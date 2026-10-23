<?php
/**
 * Code Snippets export — Ds login signup fixes v3 updated
 *
 * Snippet ID : 709
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-023
 * Modified   : 2026-09-09 02:45:02
 * Load order : 023 of 144
 */
/**
 * Designstager — Login & Sign Up Page Fixes (v3 UPDATED)
 * 1. Rename Register → Sign Up everywhere
 * 2. Allow duplicate usernames, block duplicate emails with styled popup
 * 3. Unknown user on login → styled popup with Sign Up + Forgot Password buttons
 * 4. Style both pages to match Designstager brand
 * 5. Redirect to home page after successful login (NOT dashboard)
 * 6. No WordPress admin access for regular users
 */

// =============================================
// 1. RENAME "Register" TO "Sign Up" EVERYWHERE
// =============================================
add_filter( 'gettext', function( $translated, $original, $domain ) {
    $replacements = array(
        'Register'          => 'Sign Up',
        'Registration'      => 'Sign Up',
        'register'          => 'sign up',
        'Register An Account' => 'Create Your Account',
        'Registration confirmation will be emailed to you.' => 'We\'ll send your login details to your email.',
    );
    if ( isset( $replacements[ $translated ] ) ) {
        return $replacements[ $translated ];
    }
    return $translated;
}, 10, 3 );

// =============================================
// 2. ALLOW DUPLICATE USERNAMES
// =============================================
add_filter( 'registration_errors', function( $errors ) {
    if ( isset( $errors->errors['username_exists'] ) ) {
        unset( $errors->errors['username_exists'] );
        unset( $errors->error_data['username_exists'] );
    }
    return $errors;
}, 99 );

// Auto-generate unique username if duplicate
add_filter( 'pre_user_login', function( $user_login ) {
    if ( username_exists( $user_login ) ) {
        $user_login = $user_login . '_' . rand( 100, 999 );
    }
    return $user_login;
} );

// =============================================
// 3. BLOCK DUPLICATE EMAILS WITH POPUP MARKER
// =============================================
add_filter( 'registration_errors', function( $errors, $sanitized_user_login, $user_email ) {
    if ( ! empty( $user_email ) && email_exists( $user_email ) ) {
        $errors->add( 'email_exists', '__DS_EMAIL_EXISTS__' );
    }
    return $errors;
}, 10, 3 );

// =============================================
// 4. REDIRECT TO HOME PAGE AFTER LOGIN (NOT ADMIN/DASHBOARD)
// =============================================
add_filter( 'login_redirect', function( $redirect_to, $request, $user ) {
    // If user is not logged in or there's an error, return original redirect
    if ( ! isset( $user->ID ) ) {
        return $redirect_to;
    }

    // Redirect all users to home page after login. Signed in from the PowerPoint add-in's side panel:
    // come back to the add-in's start page there (?ds_office=1), so the panel keeps its add-in view.
    if ( ! empty( $_REQUEST['ds_office'] ) || false !== strpos( (string) wp_get_referer(), 'ds_office=' ) ) {
        return home_url( '/?ds_office=1' );
    }
    return home_url( '/' );
}, 10, 3 );

// =============================================
// 5. BLOCK REGULAR USERS FROM ACCESSING WP-ADMIN
// =============================================
add_action( 'admin_init', function() {
    // If not in admin, skip
    if ( ! is_admin() ) {
        return;
    }

    // Allow administrators
    if ( current_user_can( 'manage_options' ) ) {
        return;
    }

    // Block everyone else from wp-admin
    wp_safe_remote_post( home_url( '/' ) );
    wp_redirect( home_url( '/' ) );
    exit;
} );

// Also hide admin bar for non-admins
add_filter( 'show_admin_bar', function( $show ) {
    if ( is_admin() ) {
        return current_user_can( 'manage_options' );
    }
    return false;
} );

// =============================================
// 6. STYLES
// =============================================
add_action( 'wp_head', function() { ?>
<style>
/* ===== FORM WRAPPER ===== */
body.page.tml-action, html body.tml-action { background: #10131c !important; }
    body.tml-action .entry-content, body.tml-action .page-content, body.tml-action .site-main, body.tml-action .ast-article-single, body.tml-action article { background: #10131c !important; }
    .tml { max-width: 460px !important; margin: 40px auto !important; }
.tml h3.tml-title,
.tml-action-links { display: none !important; }

/* ===== FORM CARD ===== */
.tml form {
    background: #191c25 !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    border-radius: 16px !important;
    padding: 36px 32px !important;
    box-shadow: 0 8px 40px rgba(0,0,0,0.4) !important;
}

/* ===== FORM HEADING (BOLD + CENTER) ===== */
.tml form::before {
    display: block;
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 20px;
    text-align: center;
}
.tml-login form::before       { content: 'Welcome back 👋'; }
.tml-register form::before    { content: 'Create your account 🚀'; }
.tml-lostpassword form::before { content: 'Reset your password 🔐'; }

/* ===== LABELS ===== */
.tml label {
    font-size: 13px !important;
    font-weight: 500 !important;
    color: #cbd5e1 !important;
    margin-bottom: 4px !important;
    display: block !important;
}
.tml .tml-message { color: #cbd5e1 !important; }

/* ===== INPUTS ===== */
.tml input[type="text"],
.tml input[type="email"],
.tml input[type="password"] {
    background: #10131c !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    border-radius: 8px !important;
    color: #e5e7eb !important;
    padding: 11px 14px !important;
    font-size: 14px !important;
    width: 100% !important;
    box-sizing: border-box !important;
    transition: border-color 0.2s !important;
    outline: none !important;
    box-shadow: none !important;
}
.tml input[type="text"]:focus,
.tml input[type="email"]:focus,
.tml input[type="password"]:focus {
    border-color: #E8442A !important;
    background: #191c25 !important;
}

/* ===== SUBMIT BUTTON ===== */
.tml input[type="submit"] {
    background: #E8442A !important;
    border: none !important;
    border-radius: 8px !important;
    color: #fff !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    padding: 13px !important;
    width: 100% !important;
    cursor: pointer !important;
    margin-top: 6px !important;
    text-transform: none !important;
    letter-spacing: 0 !important;
    transition: background 0.15s !important;
}
.tml input[type="submit"]:hover { background: #c73520 !important; }

/* ===== LINKS ===== */
.tml p a, .tml a { font-size: 13px !important; color: #E8442A !important; }

/* ===== REMEMBER ME ===== */
.tml .tml-rememberme-wrap {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin: 10px 0 !important;
    font-size: 13px !important;
    color: #cbd5e1 !important;
}

/* ===== HIDE DEFAULT ERROR BOXES (replaced by popups) ===== */
.tml .tml-errors,
.tml .tml-error { display: none !important; }
	
/* Wrong-password and other errors: dark box, white text */
.tml ul.tml-errors,
.tml .tml-errors{
  list-style:none !important;
  margin:0 0 18px !important;
  padding:12px 16px !important;
  border-radius:10px !important;
  background:rgba(232,68,42,.14) !important;
  border:1px solid rgba(232,68,42,.55) !important;
  box-shadow:none !important;
}
.tml li.tml-error,
.tml .tml-error{
  background:transparent !important;
  border:0 !important;
  padding:0 !important;
  margin:0 !important;
  color:#ffffff !important;
  -webkit-text-fill-color:#ffffff !important;
  opacity:1 !important;
  font-size:14px !important;
  line-height:1.55 !important;
}
.tml .tml-error strong{color:#ffffff !important;-webkit-text-fill-color:#ffffff !important}
.tml .tml-error a{color:#FF7A45 !important;-webkit-text-fill-color:#FF7A45 !important;font-weight:600 !important}

/* ===== BOTTOM LINKS (NO BULLETS, CENTER ALIGNED) ===== */
.ds-auth-links {
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
    color: #cbd5e1;
    list-style: none !important;
    padding: 0 !important;
}
.ds-auth-links a { 
    color: #E8442A; 
    font-weight: 600; 
    text-decoration: none;
    display: inline;
}
.ds-auth-links a:hover { text-decoration: underline; }

/* Hide bullet points from any lists */
.tml ul, .tml ol {
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
    display: flex !important;
    justify-content: center !important;
    gap: 16px !important;
    flex-wrap: wrap !important;
}

.tml li {
    list-style: none !important;
    display: inline !important;
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
}

/* ===== SHARED POPUP OVERLAY ===== */
.ds-popup-overlay-shared {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    backdrop-filter: blur(4px);
    z-index: 999999;
    align-items: center;
    justify-content: center;
}
.ds-popup-overlay-shared.open { display: flex; }

/* ===== SHARED POPUP BOX ===== */
.ds-popup-shared {
    background: #191c25;
    border-radius: 20px;
    padding: 36px 32px;
    max-width: 380px;
    width: calc(100% - 40px);
    text-align: center;
    box-shadow: 0 24px 60px rgba(0,0,0,0.5);
    animation: dsPopIn 0.25s ease;
}
@keyframes dsPopIn {
    from { opacity:0; transform: scale(0.93) translateY(10px); }
    to   { opacity:1; transform: scale(1) translateY(0); }
}
.ds-popup-shared .ds-ep-icon {
    width: 56px; height: 56px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
    font-size: 24px;
}
.ds-ep-icon--red  { background: #2a1414; }
.ds-ep-icon--blue { background: #10233d; }
.ds-popup-shared h3 {
    font-size: 18px; font-weight: 700;
    color: #fff; margin: 0 0 8px;
}
.ds-popup-shared p {
    font-size: 13px; color: #cbd5e1;
    line-height: 1.6; margin: 0 0 24px;
}
.ds-popup-shared p strong { color: #E8442A; }

/* ===== POPUP BUTTONS ===== */
.ds-ep-btn {
    display: block; width: 100%;
    padding: 12px; border-radius: 10px;
    font-size: 14px; font-weight: 600;
    text-decoration: none; margin-bottom: 10px;
    transition: opacity 0.15s;
    box-sizing: border-box;
    border: none; cursor: pointer;
}
.ds-ep-btn:hover { opacity: 0.88; }
.ds-ep-btn--primary   { background: #E8442A; color: #fff !important; }
.ds-ep-btn--secondary { background: #1f222b; color: #cbd5e1 !important; }

/* ===== REGISTRATION SUCCESS CARD ===== */
.ds-regcomplete-card {
    background: #191c25;
    border-radius: 20px;
    padding: 40px 32px;
    max-width: 420px;
    width: calc(100% - 40px);
    margin: 60px auto;
    text-align: center;
    box-shadow: 0 8px 40px rgba(0,0,0,0.4);
    border: 1px solid rgba(255,255,255,.12);
}
.ds-regcomplete-card .ds-rc-icon {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: #0f2418;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px;
    font-size: 30px;
}
.ds-regcomplete-card h3 {
    font-size: 21px; font-weight: 700;
    color: #fff; margin: 0 0 10px;
}
.ds-regcomplete-card p {
    font-size: 14px; color: #cbd5e1;
    line-height: 1.6; margin: 0 0 26px;
}
</style>
<?php }, 20 );

// =============================================
// 7. POPUP HTML + JS
// =============================================
add_action( 'wp_footer', function() {
    $login_url   = home_url( '/login' );
    $signup_url  = home_url( '/login?action=register' );
    $forgot_url  = home_url( '/login?action=lostpassword' );
    $market_url  = home_url( '/marketplace' );
    ?>

    <!-- POPUP 1: Email Already Registered (Sign Up page) -->
    <div id="ds-email-exists-overlay" class="ds-popup-overlay-shared">
        <div class="ds-popup-shared">
            <div class="ds-ep-icon ds-ep-icon--red">✉️</div>
            <h3>Email Already Registered</h3>
            <p>This email address is <strong>already registered</strong> on our website.<br>Please log in or reset your password if you have forgotten it.</p>
            <a href="<?php echo esc_url( $login_url ); ?>" class="ds-ep-btn ds-ep-btn--primary">Log In to My Account</a>
            <a href="<?php echo esc_url( $forgot_url ); ?>" class="ds-ep-btn ds-ep-btn--secondary">Forgot Password?</a>
            <button onclick="dsClosePopup('ds-email-exists-overlay')" class="ds-ep-btn ds-ep-btn--secondary" style="margin-bottom:0;">✕ Close</button>
        </div>
    </div>

    <!-- POPUP 2: Account Not Found (Login page) -->
    <div id="ds-no-account-overlay" class="ds-popup-overlay-shared">
        <div class="ds-popup-shared">
            <div class="ds-ep-icon ds-ep-icon--blue">🔍</div>
            <h3>Account Not Found</h3>
            <p>We couldn't find an account with that email or username.<br>You may not have <strong>signed up</strong> yet, or you may have used a different email address.</p>
            <a href="<?php echo esc_url( $signup_url ); ?>" class="ds-ep-btn ds-ep-btn--primary">Create a Free Account</a>
            <a href="<?php echo esc_url( $forgot_url ); ?>" class="ds-ep-btn ds-ep-btn--secondary">Forgot Password?</a>
            <button onclick="dsClosePopup('ds-no-account-overlay')" class="ds-ep-btn ds-ep-btn--secondary" style="margin-bottom:0;">✕ Try Again</button>
        </div>
    </div>

    <script>
    function dsClosePopup(id) {
        document.getElementById(id).classList.remove('open');
    }

    (function() {
        // Error keywords that mean the user doesn't exist
        var noAccountKeywords = [
            'unknown email address',
            'invalid username',
            'no account',
            'not registered',
            'is not registered',
            'could not be found'
        ];

        // Check all error elements on page
        var errors = document.querySelectorAll('.tml-error, .tml-errors, .error, .message');
        errors.forEach(function(el) {
            var text = (el.textContent || el.innerText || '').toLowerCase();

            // Email already registered — Sign Up popup
            if (text.indexOf('__ds_email_exists__') !== -1) {
                el.style.display = 'none';
                document.getElementById('ds-email-exists-overlay').classList.add('open');
                return;
            }

            // Unknown user on Login — Not Found popup
            var isNoAccount = noAccountKeywords.some(function(kw) {
                return text.indexOf(kw) !== -1;
            });
                        if (isNoAccount) {
                el.style.display = 'none';
                document.getElementById('ds-no-account-overlay').classList.add('open');
            } else if (el.matches('.tml-errors, .tml-error') && text.trim() !== '') {
                // Any other error (e.g. wrong password) — show it instead of hiding it
                el.style.setProperty('display', 'block', 'important');
            }
			// Hide default WordPress "Sign Up" link
        var signupLi = document.querySelector('li.tml-register-link');
        if (signupLi) {
            signupLi.style.display = 'none';
        }
        });

        // Close on overlay background click
        document.querySelectorAll('.ds-popup-overlay-shared').forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('open');
            });
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.ds-popup-overlay-shared').forEach(function(el) {
                    el.classList.remove('open');
                });
            }
        });

        // Add "Don't have an account?" link below login form (NO BULLETS)
        var loginForm = document.querySelector('.tml-login form');
        if (loginForm) {
            var lnk = document.createElement('div');
            lnk.className = 'ds-auth-links';
            lnk.innerHTML = 'Don\'t have an account? <a href="<?php echo esc_url( $signup_url ); ?>">Sign Up Free</a>';
            loginForm.parentNode.insertBefore(lnk, loginForm.nextSibling);
        }

        // Add "Already have an account?" + forgot password below Sign Up form (NO BULLETS)
        var registerForm = document.querySelector('.tml-register form');
        if (registerForm) {
            var lnk2 = document.createElement('div');
            lnk2.className = 'ds-auth-links';
            lnk2.innerHTML = 'Already have an account? <a href="<?php echo esc_url( $login_url ); ?>">Log In</a>';
            registerForm.parentNode.insertBefore(lnk2, registerForm.nextSibling);

            // Add "Lost your password?" with same styling as other auth links
var lnk3 = document.createElement('div');
lnk3.className = 'ds-auth-links';
lnk3.innerHTML = '<a href="<?php echo esc_url( $forgot_url ); ?>">Lost your password?</a>';
registerForm.parentNode.insertBefore(lnk3, registerForm.nextSibling);
        }
    })();
					   // Remove Sign Up link
(function() {
    function removeSignUp() {
        var signupLink = document.querySelector('.tml-register-link');
        if (signupLink) {
            signupLink.remove();
        }
        var allLis = document.querySelectorAll('.tml ul li');
        allLis.forEach(function(li) {
            if (li.textContent.indexOf('Sign Up') !== -1) {
                li.remove();
            }
        });
    }
    removeSignUp();
    setTimeout(removeSignUp, 100);
})();

// =============================================
// 8. BEAUTIFY "Registration complete" SUCCESS SCREEN
// =============================================

// Step 1: capture the email BEFORE form submits
var _dsRegEmail = '';
(function(){
  var form = document.querySelector('.tml-register-form form, form#registerform, .tml form');
  if(form){
    form.addEventListener('submit', function(){
      var emailEl = form.querySelector('input[type="email"], input[name="user_email"]');
      if(emailEl && emailEl.value) {
        _dsRegEmail = emailEl.value.trim();
        try{ sessionStorage.setItem('ds_reg_email', _dsRegEmail); }catch(e){}
      }
    });
  }
  // also restore if page reloaded after submit
  try{ _dsRegEmail = sessionStorage.getItem('ds_reg_email') || ''; }catch(e){}
})();

// Step 2: detect webmail URL from email domain
function dsGetMailboxUrl(email) {
  var domain = (email.split('@')[1] || '').toLowerCase();
  var map = {
    'gmail.com':       'https://mail.google.com',
    'googlemail.com':  'https://mail.google.com',
    'yahoo.com':       'https://mail.yahoo.com',
    'yahoo.co.in':     'https://mail.yahoo.com',
    'yahoo.in':        'https://mail.yahoo.com',
    'outlook.com':     'https://outlook.live.com',
    'hotmail.com':     'https://outlook.live.com',
    'live.com':        'https://outlook.live.com',
    'icloud.com':      'https://www.icloud.com/mail',
    'me.com':          'https://www.icloud.com/mail',
    'mac.com':         'https://www.icloud.com/mail',
    'aol.com':         'https://mail.aol.com',
    'protonmail.com':  'https://mail.proton.me',
    'proton.me':       'https://mail.proton.me',
    'zoho.com':        'https://mail.zoho.com',
  };
  return map[domain] || ('https://' + domain);
}

// Step 3: swap in the styled card
(function() {
  var nodes = document.querySelectorAll('.tml p, .tml .tml-message, .tml > div');
  for (var i = 0; i < nodes.length; i++) {
    var el = nodes[i];
    var text = (el.textContent || '').trim();
    var lower = text.toLowerCase();
    var isReg  = lower.indexOf('registration complete') !== -1;
    var isLost = lower.indexOf('confirmation link') !== -1 && lower.indexOf('login page') !== -1;
    if (!isReg && !isLost) continue;

    var email = _dsRegEmail;
    var mailUrl = email ? dsGetMailboxUrl(email) : '';
    var mailBtn = mailUrl
      ? '<a href="' + mailUrl + '" target="_blank" rel="noopener" style="display:inline-block;background:#1a73e8;color:#fff!important;text-decoration:none!important;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:700;font-family:inherit;margin-bottom:10px;width:100%;box-sizing:border-box;text-align:center;">📬 Open My Inbox' + (email ? ' (' + email + ')' : '') + '</a>'
      : '';

    var heading = isReg ? 'You\'re All Set! 🎉' : 'Check Your Email 📧';
    var body    = isReg
      ? 'We\'ve sent your login details to your email address.<br>Check your inbox, then sign in to start creating.'
      : 'We\'ve sent a password reset link to your email address.<br>Click the link in that email to set a new password.';

    var card = document.createElement('div');
    card.className = 'ds-regcomplete-card';
    card.innerHTML =
      '<div class="ds-rc-icon">' + (isReg ? '✅' : '🔑') + '</div>' +
      '<h3>' + heading + '</h3>' +
      '<p>' + body + '</p>' +
      mailBtn +
      '<a href="https://designstager.com/login" style="display:inline-block;background:#E8442A;color:#fff!important;text-decoration:none!important;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:700;font-family:inherit;width:100%;box-sizing:border-box;text-align:center;">→ Go to Login</a>';

    el.parentNode.replaceChild(card, el);
    break;
  }
})();
    </script>
    <?php
}, 20 );
