<?php
/**
 * Code Snippets export — DS Services Page Style
 *
 * Snippet ID : 699
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-013
 * Modified   : 2026-09-09 02:44:40
 * Load order : 013 of 144
 */
/*
 * REMOVED in 16.1 — this told LiteSpeed the whole site was uncacheable on
 * every request. Its comment claimed it was for AJAX, but it had no AJAX
 * check of any kind. Genuinely dynamic endpoints are REST/admin-ajax URLs,
 * which LiteSpeed already never caches.
 *
 * Original line:
 *   add_filter('litespeed_is_not_cacheable', '__return_true');
 */

// Settings > General > Project Request Notification Email
add_action( 'admin_init', function () {
	register_setting( 'general', 'ds_project_request_notify_email', [
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_email',
		'default'           => get_option( 'admin_email' ),
	] );
	add_settings_field(
		'ds_project_request_notify_email',
		'Project Request Notification Email',
		function () {
			$value = get_option( 'ds_project_request_notify_email', get_option( 'admin_email' ) );
			echo '<input type="email" id="ds_project_request_notify_email" name="ds_project_request_notify_email" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="' . esc_attr( get_option( 'admin_email' ) ) . '" />';
			echo '<p class="description">Where "Start Your Project" form submissions on the Customize page are sent. Leave blank to use the site admin email.</p>';
		},
		'general'
	);
} );

/**
 * Designstager — DS Customize Page Top Section
 * Snippet Name: DS Services Page Style
 * Run: Everywhere
 * Usage: [ds_customize_top]
 */

add_shortcode('ds_customize_top', function() {
    if (!is_user_logged_in()) {
        ob_start(); ?>
        <div style="text-align:center;padding:60px 20px;background:#0d0f1a;border-radius:20px;margin-bottom:40px;">
            <div style="font-size:48px;margin-bottom:16px;">🔒</div>
            <h3 style="color:#fff;font-size:22px;font-weight:800;margin:0 0 10px;">Login to Submit a Request</h3>
            <p style="color:#64748b;font-size:14px;margin:0 0 24px;">Please log in or create an account to continue.</p>
            <a href="<?php echo wp_login_url(get_permalink()); ?>" style="display:inline-block;background:#E8442A;color:#fff;padding:13px 32px;border-radius:10px;font-size:15px;font-weight:700;text-decoration:none;margin-right:10px;">Login</a>
            <a href="<?php echo wp_registration_url(); ?>" style="display:inline-block;background:rgba(255,255,255,0.08);color:#fff;padding:13px 32px;border-radius:10px;font-size:15px;font-weight:700;text-decoration:none;border:1px solid rgba(255,255,255,0.15);">Sign Up Free</a>
        </div>
        <?php return ob_get_clean();
    }
    ob_start(); ?>

<div class="ds-cust-wrap">

    <!-- LEFT: TIMELINE -->
    <div class="ds-cust-left">

        <h2 class="ds-cust-section-title">Our Process</h2>

        <div class="ds-cust-timeline">

            <?php
            $steps = array(
                array('num'=>'1','icon'=>'📋','title'=>'Content Submission',
                    'desc'=>'Submit your draft (PPT, Word, PDF, or email) with project requirements and brand guidelines.'),
                array('num'=>'2','icon'=>'💰','title'=>'Pricing & Timeline',
                    'desc'=>'Project manager confirms scope, pricing, and deadlines based on complexity and requirements.'),
                array('num'=>'3','icon'=>'🎨','title'=>'Sample Slides',
                    'desc'=>'Design samples shared for review to ensure style alignment before full production.'),
                array('num'=>'4','icon'=>'🔄','title'=>'Revisions & Feedback',
                    'desc'=>'Refinement based on feedback with up to 3 revision rounds included.'),
                array('num'=>'5','icon'=>'🚀','title'=>'Final Delivery',
                    'desc'=>'Polished final files delivered in your preferred format (PPT, PDF, PNG, etc.).'),
            );
            foreach ($steps as $i => $step) : ?>
            <div class="ds-cust-step" style="animation-delay:<?php echo $i * 0.1; ?>s">
                <div class="ds-cust-step-dot">
                    <span><?php echo $step['num']; ?></span>
                </div>
                <?php if ($i < count($steps) - 1) : ?>
                <div class="ds-cust-step-line"></div>
                <?php endif; ?>
                <div class="ds-cust-step-content">
                    <div class="ds-cust-step-header">
                        <span class="ds-cust-step-icon"><?php echo $step['icon']; ?></span>
                        <h4><?php echo $step['title']; ?></h4>
                    </div>
                    <p><?php echo $step['desc']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>

        </div>

        <!-- Why Choose Us -->
        <div class="ds-cust-why">
            <h3>Why Choose Us?</h3>
            <ul>
                <li>100% satisfaction guarantee</li>
                <li>Professional team of expert designers</li>
                <li>Fast turnaround times</li>
                <li>Direct communication via email</li>
                <li>Up to 3 free revision rounds</li>
            </ul>
        </div>

    </div>

    <!-- RIGHT: MODERN FORM -->
    <div class="ds-cust-right">
        <div class="ds-cust-form-card">

            <!-- Top animated border -->
            <div class="ds-cust-form-border"></div>

            <!-- Header -->
            <div class="ds-cust-form-header">
                <h3>Start Your Project</h3>
                <p>Fill in the details and we'll get back within 24 hours</p>
            </div>

            <!-- Name + Email -->
            <div class="ds-cust-row">
                <div class="ds-cust-field">
                    <label>Name *</label>
                    <input type="text" placeholder="Your full name" class="ds-cust-input" id="ds-cust-name" value="<?php echo esc_attr(wp_get_current_user()->display_name); ?>" readonly style="opacity:0.6;cursor:not-allowed;pointer-events:none;" autocomplete="off"/>
                </div>
                <div class="ds-cust-field">
                    <label>Email *</label>
<input type="email" placeholder="your@email.com" class="ds-cust-input" id="ds-cust-email" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" readonly style="opacity:0.6;cursor:not-allowed;pointer-events:none;" autocomplete="off"/>
                </div>
            </div>

            <!-- Description -->
            <div class="ds-cust-field">
                <label>Project Description *</label>
                <textarea class="ds-cust-input ds-cust-textarea"
                    placeholder="Describe your project in detail..." rows="4"></textarea>
            </div>

            <!-- Design Type + Budget -->
            <div class="ds-cust-row">
                <div class="ds-cust-field">
                    <label>Design Type *</label>
                    <select class="ds-cust-input ds-cust-select" required>
                        <option value="">Select type...</option>
                        <option>PowerPoint / Presentation</option>
                        <option>Flyer / Poster</option>
                        <option>Social Media Design</option>
                        <option>Brochure</option>
                        <option>Infographic</option>
                        <option>Motion Graphics</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="ds-cust-field">
                    <label>Budget (USD) *</label>
                    <select class="ds-cust-input ds-cust-select" required>
                        <option value="">Select budget...</option>
                        <option>$0 - $50</option>
                        <option>$50 - $100</option>
                        <option>$100 - $250</option>
                        <option>$250 - $500</option>
                        <option>$500+</option>
                    </select>
                </div>
            </div>

            <!-- Deadline -->
            <div class="ds-cust-field">
                <label>Preferred Deadline *</label>
                <input type="date" class="ds-cust-input ds-cust-date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"/>
            </div>

            <!-- File Upload -->
            <div class="ds-cust-field">
                <label>Reference Files * <span style="color:#4A5568;font-weight:400;text-transform:none;font-size:10px;">(upload a file OR paste a Drive link below)</span></label>
                <div class="ds-cust-upload" onclick="document.getElementById('ds-file-input').click()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="rgba(255,255,255,0.3)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <p>Drop files here or <span>click to upload</span></p>
                    <small>PPT, PDF, PNG, JPG (max 100MB)</small>
                    <input type="file" id="ds-file-input" multiple style="display:none;"
                           onchange="dsShowFiles(this)"/>
                </div>
                <div id="ds-files-list" style="margin-top:8px;font-size:12px;color:#64748B;"></div>
				<div class="ds-cust-field" style="margin-top:12px;">
    <label>Google Drive Link <span style="color:#4A5568;font-weight:400;text-transform:none;font-size:10px;">(required if no file uploaded)</span></label>
    <input type="url" id="ds-cust-drive" placeholder="Paste your file link here..." class="ds-cust-input"/>
</div>
            </div>

            <!-- Submit -->
            <button class="ds-cust-submit" onclick="dsSubmitCustomForm(this)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Send My Request
            </button>

            <!-- Trust -->
            <div class="ds-cust-trust">
                <span>🔒 Secure & Private</span>
                <span>✓ Reply within 24hrs</span>
                <span>⭐ 100% Satisfaction</span>
            </div>

        </div>
    </div>

</div>

<style>
/* ===== WRAP ===== */
.ds-cust-wrap {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 48px;
    background: linear-gradient(135deg, #0d0f1a 0%, #1C1F2E 100%);
    border-radius: 20px;
    padding: 48px;
    margin: 0 0 40px;
    position: relative;
    overflow: hidden;
}
.ds-cust-wrap::before {
    content: '';
    position: absolute;
    top: -100px;
    right: -100px;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(232,68,42,0.08) 0%, transparent 70%);
    pointer-events: none;
}

/* Section title */
.ds-cust-section-title {
    font-size: 20px !important;
    font-weight: 800 !important;
    color: #ffffff !important;
    margin: 0 0 32px !important;
    display: flex;
    align-items: center;
    gap: 10px;
}
.ds-cust-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(255,255,255,0.08);
}

/* ===== TIMELINE ===== */
.ds-cust-timeline {
    position: relative;
    padding-left: 0;
}
.ds-cust-step {
    display: flex;
    gap: 16px;
    position: relative;
    opacity: 0;
    transform: translateX(-20px);
    animation: dsCustFadeIn 0.5s ease forwards;
}
@keyframes dsCustFadeIn {
    to { opacity: 1; transform: translateX(0); }
}

/* Dot column */
.ds-cust-step-dot {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
}
.ds-cust-step-dot span {
    width: 32px;
    height: 32px;
    background: #E8442A;
    color: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 800;
    box-shadow: 0 0 0 3px rgba(232,68,42,0.2), 0 0 16px rgba(232,68,42,0.4);
    flex-shrink: 0;
    position: relative;
    z-index: 2;
}
@keyframes dsSpin { to { transform: rotate(360deg); } }

/* Connecting line */
.ds-cust-step-line {
    position: absolute;
    left: 15px;
    top: 32px;
    width: 2px;
    height: calc(100% + 4px);
    background: linear-gradient(to bottom, rgba(232,68,42,0.6), rgba(232,68,42,0.1));
    z-index: 1;
}

/* Step content */
.ds-cust-step-content {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 16px;
    flex: 1;
    transition: all 0.2s;
    cursor: default;
}
.ds-cust-step-content:hover {
    background: rgba(232,68,42,0.06);
    border-color: rgba(232,68,42,0.2);
    transform: translateX(4px);
}
.ds-cust-step-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}
.ds-cust-step-icon { font-size: 16px; }
.ds-cust-step-content h4 {
    font-size: 14px !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    margin: 0 !important;
}
.ds-cust-step-content p {
    font-size: 12px !important;
    color: #64748B !important;
    margin: 0 !important;
    line-height: 1.6 !important;
}

/* Why Choose Us */
.ds-cust-why {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    padding: 28px;
    margin-top: 32px;
}
.ds-cust-why h3 {
    color: #ffffff !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    margin: 0 0 16px !important;
}
.ds-cust-why ul li {
    color: #94A3B8 !important;
}
    border-radius: 14px;
    padding: 22px 24px;
    margin-top: 8px;
    position: relative;
    overflow: hidden;
}
.ds-cust-why::before {
    content: '';
    position: absolute;
    top: -20px; right: -20px;
    width: 80px; height: 80px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}
.ds-cust-why h3 {
    font-size: 16px !important;
    font-weight: 800 !important;
    color: #ffffff !important;
    margin: 0 0 14px !important;
}
.ds-cust-why ul {
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
}
.ds-cust-why ul li {
    font-size: 13px !important;
    color: rgba(255,255,255,0.9) !important;
    padding: 5px 0 !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}
.ds-cust-why ul li::before {
    content: '✓' !important;
    width: 18px !important;
    height: 18px !important;
    background: rgba(255,255,255,0.2) !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 10px !important;
    font-weight: 800 !important;
    flex-shrink: 0 !important;
}

/* ===== FORM CARD ===== */
.ds-cust-right { position: sticky; top: 100px; }
.ds-cust-form-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 32px;
    position: relative;
    overflow: hidden;
}

/* Animated top border */
.ds-cust-form-border {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #E8442A, #ff6b4a, #E8442A);
    background-size: 200% 100%;
    animation: dsBorderAnim 3s linear infinite;
}
@keyframes dsBorderAnim {
    0% { background-position: 0% 0%; }
    100% { background-position: 200% 0%; }
}

/* Form header */
.ds-cust-form-header { margin-bottom: 24px; }
.ds-cust-form-header h3 {
    font-size: 20px !important;
    font-weight: 800 !important;
    color: #ffffff !important;
    margin: 0 0 4px !important;
}
.ds-cust-form-header p {
    font-size: 13px !important;
    color: #64748B !important;
    margin: 0 !important;
}

/* Fields */
.ds-cust-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 14px;
}
.ds-cust-field { margin-bottom: 14px; }
.ds-cust-field label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-bottom: 6px;
}
.ds-cust-input {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    color: #ffffff !important;
    caret-color: #ffffff;
    padding: 11px 14px;
    font-size: 14px;
    box-sizing: border-box;
    outline: none;
    transition: all 0.2s;
    font-family: inherit;
    -webkit-text-fill-color: #ffffff;
}
.ds-cust-input:focus {
    border-color: #E8442A;
    background: rgba(232,68,42,0.05);
    box-shadow: 0 0 0 3px rgba(232,68,42,0.1);
}
.ds-cust-input::placeholder { color: #4A5568; }
input[type="url"].ds-cust-input::placeholder {
    color: #4A5568 !important;
    -webkit-text-fill-color: #4A5568 !important;
}
.ds-cust-input:-webkit-autofill,
.ds-cust-input:-webkit-autofill:hover,
.ds-cust-input:-webkit-autofill:focus,
.ds-cust-input[readonly] {
    -webkit-box-shadow: 0 0 0px 1000px #191c25 inset !important;
    -webkit-text-fill-color: #ffffff !important;
    background-color: #191c25 !important;
    caret-color: #ffffff;
}
input[type="url"].ds-cust-input {
    -webkit-text-fill-color: #94A3B8 !important;
    color: #94A3B8 !important;
    background: rgba(255,255,255,0.05) !important;
    -webkit-box-shadow: 0 0 0px 1000px #191c25 inset !important;
}
input[type="url"].ds-cust-input::placeholder {
    -webkit-text-fill-color: #4A5568 !important;
    color: #4A5568 !important;
}
input[type="url"].ds-cust-input:-webkit-autofill,
input[type="url"].ds-cust-input:-webkit-autofill:hover,
input[type="url"].ds-cust-input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px #191c25 inset !important;
    -webkit-text-fill-color: #94A3B8 !important;
    background: #191c25 !important;
}
.ds-cust-textarea { resize: vertical; min-height: 100px; }
.ds-cust-select {
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none'%3E%3Cpath d='M6 9l6 6 6-6' stroke='%2394A3B8' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 36px;
    cursor: pointer;
}
.ds-cust-select option { background: #1C1F2E; color: #ffffff; }
.ds-cust-date { color-scheme: dark; }

/* Upload */
.ds-cust-upload {
    background: rgba(255,255,255,0.03);
    border: 1.5px dashed rgba(255,255,255,0.12);
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}
.ds-cust-upload:hover {
    border-color: rgba(232,68,42,0.4);
    background: rgba(232,68,42,0.03);
}
.ds-cust-upload p {
    font-size: 13px !important;
    color: #64748B !important;
    margin: 8px 0 4px !important;
}
.ds-cust-upload p span { color: #E8442A; text-decoration: underline; }
.ds-cust-upload small { font-size: 11px; color: #4A5568; }

/* Submit */
.ds-cust-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    background: #E8442A;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 14px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s;
    box-shadow: 0 8px 24px rgba(232,68,42,0.3);
    margin-bottom: 16px;
    font-family: inherit;
}
.ds-cust-submit:hover {
    background: #c73520;
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(232,68,42,0.45);
}

/* Trust */
.ds-cust-trust {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
}
.ds-cust-trust span {
    font-size: 11px;
    color: #4A5568;
    font-weight: 500;
}

/* Mobile */
@media (max-width: 768px) {
    .ds-cust-wrap {
        grid-template-columns: 1fr !important;
        padding: 24px !important;
        gap: 32px !important;
    }
    .ds-cust-right { position: relative !important; top: 0 !important; }
    .ds-cust-row { grid-template-columns: 1fr !important; }
}

#ds-cust-name:-webkit-autofill,
#ds-cust-name:-webkit-autofill:hover,
#ds-cust-name:-webkit-autofill:focus,
#ds-cust-email:-webkit-autofill,
#ds-cust-email:-webkit-autofill:hover,
#ds-cust-email:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px #191c25 inset !important;
    -webkit-text-fill-color: #ffffff !important;
    background-color: #191c25 !important;
    caret-color: #ffffff;
}

#ds-cust-name[readonly],
#ds-cust-email[readonly] {
    -webkit-box-shadow: 0 0 0px 1000px #191c25 inset !important;
    -webkit-text-fill-color: #ffffff !important;
    background: rgba(255,255,255,0.03) !important;
    pointer-events: none;
    user-select: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('/wp-json/ds/v1/my-profile', {credentials:'include'})
    .then(function(r){ return r.json(); })
    .then(function(u){
        if (!u || u.code) return;
        var nameEl  = document.getElementById('ds-cust-name');
        var emailEl = document.getElementById('ds-cust-email');
        if (nameEl && u.name) {
            nameEl.value = u.name;
            nameEl.setAttribute('readonly', 'readonly');
            nameEl.setAttribute('autocomplete', 'off');
            nameEl.style.cssText += 'opacity:0.6;cursor:not-allowed;pointer-events:none;-webkit-text-fill-color:#ffffff !important;';
        }
        if (emailEl && u.email) {
            emailEl.value = u.email;
            emailEl.setAttribute('readonly', 'readonly');
            emailEl.setAttribute('autocomplete', 'off');
            emailEl.style.cssText += 'opacity:0.6;cursor:not-allowed;pointer-events:none;-webkit-text-fill-color:#ffffff !important;';
        }
    })
    .catch(function(err){ console.log('Profile fetch error:', err); });
});

function dsShowFiles(input) {
    var list = document.getElementById('ds-files-list');
    if (input.files.length > 0) {
        var names = Array.from(input.files).map(function(f) {
            return '📎 ' + f.name;
        }).join('<br>');
        list.innerHTML = names;
    }
}

function dsSubmitCustomForm(btn) {
    var name  = document.querySelector('.ds-cust-wrap input[type="text"]').value.trim();
    var email = document.querySelector('.ds-cust-wrap input[type="email"]').value.trim();
    var desc  = document.querySelector('.ds-cust-wrap textarea').value.trim();
    var selects = document.querySelectorAll('.ds-cust-wrap .ds-cust-select');
    var designType = selects[0] ? selects[0].value : '';
    var budget = selects[1] ? selects[1].value : '';
    var deadline = document.querySelector('.ds-cust-wrap .ds-cust-date').value;
    var driveLink = document.getElementById('ds-cust-drive').value.trim();
    var fileInput = document.getElementById('ds-file-input');
    var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
    if (!name || !email || !desc || !designType || !budget || !deadline) {
        alert('Please fill in all required fields.');
        return;
    }
    var oversized = false;
    if (hasFile) {
        Array.from(fileInput.files).forEach(function(f){ if (f.size > 100 * 1024 * 1024) oversized = true; });
    }
    if (oversized) {
        alert('Each file must be 100MB or smaller.');
        return;
    }
    if (!hasFile && !driveLink) {
        alert('Please upload a reference file or paste a Google Drive link.');
        return;
    }
    dsSubmitActualForm();
}
function dsSubmitActualForm() {
    var btn = document.querySelector('.ds-cust-submit');
    btn.disabled = true;
    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="animation:dsSpin 0.8s linear infinite"><circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="2"/><path d="M12 2a10 10 0 0110 10" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg> Sending...';

    var name  = document.querySelector('.ds-cust-wrap input[type="text"]').value.trim();
    var email = document.querySelector('.ds-cust-wrap input[type="email"]').value.trim();
    var desc  = document.querySelector('.ds-cust-wrap textarea').value.trim();

    var formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('description', desc);

    var selects = document.querySelectorAll('.ds-cust-wrap .ds-cust-select');
    formData.append('design_type', selects[0] ? selects[0].value : '');
    formData.append('budget',      selects[1] ? selects[1].value : '');
    var deadlineVal = document.querySelector('.ds-cust-wrap .ds-cust-date');
    formData.append('deadline', deadlineVal ? deadlineVal.value : '');
	var driveLink = document.getElementById('ds-cust-drive');
formData.append('drive_link', driveLink ? driveLink.value : '');

    var fileInput = document.getElementById('ds-file-input');
    if (fileInput && fileInput.files.length > 0) {
        Array.from(fileInput.files).forEach(function(file) {
            formData.append('files[]', file);
        });
    }

    fetch('/wp-json/ds/v1/submit-design-request', { method:'POST', body:formData, credentials:'include' })
    .then(function(r){ return r.json(); })
    .then(function(data){
        if (data.success) {
            var toast = document.createElement('div');
            toast.innerHTML = '🎉 Thank you for submitting your request!<br><span style="font-size:13px;font-weight:400;opacity:0.85;">We will contact you soon.</span>';
            toast.style.cssText = 'position:fixed;top:32px;left:50%;transform:translateX(-50%) translateY(-20px);background:#10B981;color:#fff;padding:18px 32px;border-radius:14px;font-size:16px;font-weight:700;text-align:center;z-index:99999;box-shadow:0 8px 32px rgba(16,185,129,0.35);opacity:0;transition:all 0.4s ease;line-height:1.6;';
            document.body.appendChild(toast);
            setTimeout(function(){ toast.style.opacity='1'; toast.style.transform='translateX(-50%) translateY(0)'; }, 10);
            setTimeout(function(){ toast.style.opacity='0'; toast.style.transform='translateX(-50%) translateY(-20px)'; setTimeout(function(){ toast.remove(); }, 400); }, 3500);
            btn.disabled = false;
            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Send My Request';
            btn.style.background = '#E8442A';
            document.querySelector('.ds-cust-wrap input[type="text"]').value = '';
            document.querySelector('.ds-cust-wrap input[type="email"]').value = '';
            document.querySelector('.ds-cust-wrap textarea').value = '';
            document.querySelector('.ds-cust-wrap input[type="date"]').value = '';
            document.getElementById('ds-file-input').value = '';
            document.getElementById('ds-files-list').innerHTML = '';
            document.querySelectorAll('.ds-cust-select').forEach(function(s){ s.selectedIndex = 0; });
        } else {
            btn.disabled = false;
            btn.innerHTML = '⚠️ Failed. Try again.';
            setTimeout(function(){
                btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Send My Request';
                btn.style.background = '#E8442A';
                btn.disabled = false;
            }, 2000);
        }
    })
    .catch(function(){
        btn.disabled = false;
        btn.innerHTML = 'Send My Request';
    });
}

</script>

<?php return ob_get_clean();
});

						
// =============================================
// AJAX HANDLER — SEND EMAIL
// =============================================
add_action('rest_api_init', function() {
    register_rest_route('ds/v1', '/submit-design-request', array(
        'methods'             => 'POST',
        'permission_callback' => '__return_true',
        'callback'            => function(WP_REST_Request $req) {
            $name        = sanitize_text_field($req->get_param('name') ?: '');
$email       = sanitize_email($req->get_param('email') ?: '');
            $desc        = sanitize_textarea_field($req->get_param('description') ?: '');
            $design_type = sanitize_text_field($req->get_param('design_type') ?: '');
            $budget      = sanitize_text_field($req->get_param('budget') ?: '');
            $drive_link  = esc_url_raw($req->get_param('drive_link') ?: '');
            $deadline    = sanitize_text_field($req->get_param('deadline') ?: '');

            if (!$name || !$email || !$desc || !$design_type || !$budget || !$deadline) {
                return rest_ensure_response(array('success'=>false,'data'=>'Please fill in all required fields.'));
            }
            if (!is_email($email)) {
                return rest_ensure_response(array('success'=>false,'data'=>'Please enter a valid email address.'));
            }

            $has_uploaded_file = !empty($_FILES['files']['name'][0]);
            if (!$has_uploaded_file && !$drive_link) {
                return rest_ensure_response(array('success'=>false,'data'=>'Please upload a reference file or provide a Google Drive link.'));
            }

            if ( $has_uploaded_file ) {
                foreach ( $_FILES['files']['size'] as $fsize ) {
                    if ( $fsize > 100 * 1024 * 1024 ) {
                        return rest_ensure_response(array('success'=>false,'data'=>'File is too large. Max size per file is 100MB.'));
                    }
                }
            }

            $to = get_option('ds_project_request_notify_email');
            if (!$to || !is_email($to)) {
                $to = get_option('admin_email');
            }
            $subject = 'New Custom Design Request from ' . $name;
            $message = "
==============================================
  NEW CUSTOM DESIGN REQUEST — Designstager
==============================================

Name        : {$name}
Email       : {$email}
Design Type : {$design_type}
Budget      : {$budget}
Deadline    : {$deadline}
Drive Link  : {$drive_link}

----------------------------------------------
Project Description:
{$desc}
----------------------------------------------

Submitted on: " . date('d M Y, h:i A') . "
==============================================
";
            $headers = array(
                'Content-Type: text/plain; charset=UTF-8',
                'Reply-To: ' . $name . ' <' . $email . '>',
            );

            $file_urls = array();
if ( ! empty( $_FILES['files']['name'][0] ) ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    $upload_overrides = array( 'test_form' => false );
    foreach ( $_FILES['files']['tmp_name'] as $i => $tmp ) {
        if ( empty( $tmp ) || ! is_uploaded_file( $tmp ) ) {
            continue;
        }
        $single_file = array(
            'name'     => $_FILES['files']['name'][ $i ],
            'type'     => $_FILES['files']['type'][ $i ],
            'tmp_name' => $tmp,
            'error'    => $_FILES['files']['error'][ $i ],
            'size'     => $_FILES['files']['size'][ $i ],
        );
        $moved = wp_handle_upload( $single_file, $upload_overrides );
        if ( ! empty( $moved['url'] ) ) {
            $file_urls[] = $moved['url'];
        }
    }
}
if ( ! empty( $file_urls ) ) {
    $message .= "\nUploaded File(s):\n" . implode( "\n", $file_urls ) . "\n";
}

$sent = wp_mail($to, $subject, $message, $headers);

if ($sent) return rest_ensure_response(array('success'=>true));
            return rest_ensure_response(array('success'=>false,'data'=>'Mail failed'));
        },
    ));
	register_rest_route('ds/v1', '/my-profile', array(
    'methods'             => 'GET',
    'permission_callback' => function() { return is_user_logged_in(); },
    'callback'            => function() {
        $user = wp_get_current_user();
        return rest_ensure_response(array(
            'name'  => $user->display_name,
            'email' => $user->user_email,
        ));
    },
));
});
						
