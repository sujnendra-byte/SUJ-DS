<?php
/**
 * DS OTP Email Verification
 * Replaces the old 24-hour auto-verify bypass with a hard gate:
 * new signups must enter the emailed 6-digit code before they can log in.
 * Correct OTP = verified permanently, no time-based grace period.
 * Existing accounts (created before this went live) are NOT affected.
 */

if (!defined('DS_OTP_EXPIRY_MIN')) define('DS_OTP_EXPIRY_MIN', 15);
if (!defined('DS_OTP_MAX_ATTEMPTS')) define('DS_OTP_MAX_ATTEMPTS', 5);
if (!defined('DS_OTP_RESEND_COOLDOWN')) define('DS_OTP_RESEND_COOLDOWN', 60);

if (!function_exists('ds_otp_generate')) {
function ds_otp_generate() {
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}
}

if (!function_exists('ds_otp_send')) {
function ds_otp_send($user_id) {
    $user = get_userdata($user_id);
    if (!$user) return false;
    $otp = ds_otp_generate();
    update_user_meta($user_id, 'ds_otp_code', wp_hash_password($otp));
    update_user_meta($user_id, 'ds_otp_expires', time() + (DS_OTP_EXPIRY_MIN * 60));
    update_user_meta($user_id, 'ds_otp_attempts', 0);
    update_user_meta($user_id, 'ds_otp_last_sent', time());

    $subject = 'Your Designstager verification code';
    $body  = "Hi {$user->display_name},\n\n";
    $body .= "Your verification code is: {$otp}\n\n";
    $body .= 'This code expires in ' . DS_OTP_EXPIRY_MIN . " minutes.\n\n";
    $body .= "If you did not sign up for Designstager, you can ignore this email.\n\nTeam Designstager";
    wp_mail($user->user_email, $subject, $body);
    return true;
}
}

// 1. New registrations require OTP (existing users are untouched: this flag only gets
//    set going forward, and the login-gate below only checks accounts that have it).
add_action('user_register', function($user_id){
    update_user_meta($user_id, 'ds_otp_flow_v1', 1);
    update_user_meta($user_id, 'ds_email_verified', 0);
    ds_otp_send($user_id);
}, 20);

// New signups already set their own password on the signup form and now verify
// via the OTP email above, so WordPress's separate "set your password" account
// email is redundant (and confusing) for these accounts. Suppress it only for
// OTP-flow signups; the admin notification and any other registration path
// (e.g. accounts created manually in wp-admin) are unaffected.
add_filter('wp_new_user_notification_email', function($email, $user, $blogname){
    if ((int) get_user_meta($user->ID, 'ds_otp_flow_v1', true) === 1) {
        $email['to'] = '';
    }
    return $email;
}, 10, 3);

// If someone registered but never entered their OTP, their username/email would
// otherwise be stuck forever (blocked from logging in, but WordPress also blocks
// signing up again as "already registered"). To fix that: when a new signup
// collides with an existing but UNVERIFIED OTP-flow account, quietly delete that
// stale, never-used account first so the fresh signup can go through normally.
// Verified accounts, and accounts outside this flow, are never touched.
add_filter('registration_errors', function($errors, $sanitized_user_login, $user_email){
    $candidates = array(
        get_user_by('login', $sanitized_user_login),
        get_user_by('email', $user_email),
    );

    foreach ($candidates as $existing) {
        if (!$existing) continue;
        if ((int) get_user_meta($existing->ID, 'ds_otp_flow_v1', true) === 1
            && !get_user_meta($existing->ID, 'ds_email_verified', true)) {
            if (!function_exists('wp_delete_user')) {
                require_once ABSPATH . 'wp-admin/includes/user.php';
            }
            wp_delete_user($existing->ID);
            $errors->remove('username_exists');
            $errors->remove('email_exists');
        }
    }

    return $errors;
}, 20, 3);

// 2. If TML auto-logs an unverified user in right after registering, kick them back
//    out immediately and send them to the OTP entry screen.
add_action('wp_login', function($user_login, $user){
    if ((int) get_user_meta($user->ID, 'ds_otp_flow_v1', true) === 1
        && !get_user_meta($user->ID, 'ds_email_verified', true)) {
        wp_clear_auth_cookie();
        wp_set_current_user(0);
        $redirect = add_query_arg(array('ds_verify' => $user->ID), home_url('/login/'));
        wp_safe_redirect($redirect);
        exit;
    }
}, 5, 2);

// TML's auto-login does not reliably fire the 'wp_login' action, so this is the
// real enforcement: on every page load, if the logged-in user is an unverified
// OTP-flow account, log them out immediately and send them to the OTP screen.
add_action('init', function(){
    if (!is_user_logged_in()) return;
    if (strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) return;
    if (is_admin()) return;
    $uid = get_current_user_id();
    if ((int) get_user_meta($uid, 'ds_otp_flow_v1', true) === 1
        && !get_user_meta($uid, 'ds_email_verified', true)) {
        wp_clear_auth_cookie();
        wp_set_current_user(0);
        $redirect = add_query_arg(array('ds_verify' => $uid), home_url('/login/'));
        wp_safe_redirect($redirect);
        exit;
    }
}, 1);

// 3. Block direct future login attempts for unverified accounts.
add_filter('authenticate', function($user, $username, $password){
    if ($user instanceof WP_User
        && (int) get_user_meta($user->ID, 'ds_otp_flow_v1', true) === 1
        && !get_user_meta($user->ID, 'ds_email_verified', true)) {
        return new WP_Error('ds_not_verified', 'Please verify your email first. Check your inbox for the 6-digit code, or request a new one.');
    }
    return $user;
}, 30, 3);

// 4. REST endpoints: verify + resend
add_action('rest_api_init', function(){
    register_rest_route('ds-otp/v1', '/verify', array(
        'methods'  => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function(WP_REST_Request $req){
            $uid = (int) $req->get_param('user_id');
            $code = trim((string) $req->get_param('otp'));
            $user = get_userdata($uid);
            if (!$user) return new WP_Error('ds_bad_user','Invalid request.', array('status'=>400));

            $attempts = (int) get_user_meta($uid, 'ds_otp_attempts', true);
            if ($attempts >= DS_OTP_MAX_ATTEMPTS) {
                return new WP_Error('ds_too_many','Too many attempts. Please request a new code.', array('status'=>429));
            }

            $expires = (int) get_user_meta($uid, 'ds_otp_expires', true);
            $hash = get_user_meta($uid, 'ds_otp_code', true);
            if (!$hash || time() > $expires) {
                return new WP_Error('ds_expired','This code has expired. Please request a new one.', array('status'=>400));
            }

            if (!wp_check_password($code, $hash)) {
                update_user_meta($uid, 'ds_otp_attempts', $attempts + 1);
                return new WP_Error('ds_wrong','Incorrect code. Please try again.', array('status'=>400));
            }

            update_user_meta($uid, 'ds_email_verified', 1);
            update_user_meta($uid, 'ds_email_verified_date', current_time('mysql'));
            delete_user_meta($uid, 'ds_otp_code');
            delete_user_meta($uid, 'ds_otp_expires');
            delete_user_meta($uid, 'ds_otp_attempts');
            do_action('ds_email_verified', $uid);

            wp_clear_auth_cookie();
            wp_set_current_user($uid);
            wp_set_auth_cookie($uid, true);

            return array('success' => true, 'redirect' => home_url('/'));
        }
    ));

    register_rest_route('ds-otp/v1', '/resend', array(
        'methods'  => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function(WP_REST_Request $req){
            $uid = (int) $req->get_param('user_id');
            $user = get_userdata($uid);
            if (!$user) return new WP_Error('ds_bad_user','Invalid request.', array('status'=>400));

            $last = (int) get_user_meta($uid, 'ds_otp_last_sent', true);
            if (time() - $last < DS_OTP_RESEND_COOLDOWN) {
                $wait = max(1, DS_OTP_RESEND_COOLDOWN - (time() - $last));
                return new WP_Error('ds_wait', 'Please wait ' . $wait . ' seconds before requesting another code.', array('status'=>429, 'wait'=>$wait));
            }
            ds_otp_send($uid);
            return array('success' => true);
        }
    ));
});

// 5. Front-end OTP entry screen, shown on the login page when redirected with ?ds_verify=<uid>
add_action('wp_footer', function(){
    if (empty($_GET['ds_verify'])) return;
    $uid = (int) $_GET['ds_verify'];
    if (!$uid) return;
    ?>
    <style>
    #dsOtpOv{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:2147483600;display:flex;align-items:center;justify-content:center;}
    #dsOtpBox{background:#1c1c22;color:#fff;max-width:380px;width:92%;border-radius:12px;padding:28px 24px;font-family:sans-serif;text-align:center;}
    #dsOtpBox h2{margin:0 0 8px;font-size:20px;color:#ffffff !important;font-weight:700 !important;opacity:1 !important;}
#dsOtpBox p{color:#c8c8d0 !important;font-size:14px;margin:0 0 18px;opacity:1 !important;}
    #dsOtpInput{width:100%;font-size:22px;letter-spacing:6px;text-align:center;padding:12px;border-radius:8px;border:1px solid #444;background:#111;color:#fff;margin-bottom:14px;box-sizing:border-box;}
    #dsOtpGo{width:100%;padding:12px;border:0;border-radius:8px;background:#6c5ce7;color:#fff;font-size:15px;cursor:pointer;margin-bottom:10px;}
    #dsOtpResend{background:none;border:0;color:#9c9cf0;cursor:pointer;font-size:13px;}
    #dsOtpResend:disabled{color:#7a7a8c;cursor:default;opacity:.85;}
    #dsOtpMsg{font-size:13px;margin-bottom:10px;min-height:16px;}
    .dsOtpErr{color:#ff6b6b;} .dsOtpOk{color:#6bffb0;}
    </style>
    <div id="dsOtpOv">
      <div id="dsOtpBox">
        <h2>Verify your email</h2>
        <p>We emailed a 6-digit code to your inbox. Enter it below to finish signing in.</p>
        <div id="dsOtpMsg"></div>
        <input id="dsOtpInput" maxlength="6" inputmode="numeric" placeholder="______" />
        <button id="dsOtpGo">Verify</button>
        <button id="dsOtpResend">Resend code</button>
      </div>
    </div>
    <script>
    (function(){
      var uid = <?php echo (int) $uid; ?>;
      var msg = document.getElementById('dsOtpMsg');
      /* resend timer: the button stays off (with a countdown) until a new code may be sent */
      var rsBtn = document.getElementById('dsOtpResend'), rsT = null;
      function rsTimer(sec){
        clearInterval(rsT); sec = Math.max(0, Math.ceil(sec || 0));
        function tick(){
          if (sec <= 0) { clearInterval(rsT); rsBtn.disabled = false; rsBtn.textContent = 'Resend code'; return; }
          rsBtn.disabled = true; rsBtn.textContent = 'Resend code in ' + Math.floor(sec / 60) + ':' + ('0' + (sec % 60)).slice(-2); sec--;
        }
        tick(); rsT = setInterval(tick, 1000);
      }
      rsTimer(<?php $dsLast = (int) get_user_meta($uid, 'ds_otp_last_sent', true); echo $dsLast ? max(0, DS_OTP_RESEND_COOLDOWN - (time() - $dsLast)) : 0; ?>);
      document.getElementById('dsOtpGo').addEventListener('click', function(){
        var otp = document.getElementById('dsOtpInput').value.trim();
        msg.className=''; msg.textContent='Checking...';
        var ctrl = new AbortController();
        var to = setTimeout(function(){ ctrl.abort(); }, 15000);
        fetch('/wp-json/ds-otp/v1/verify', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({user_id:uid,otp:otp}),signal:ctrl.signal})
          .then(function(r){return r.json().then(function(d){return {ok:r.ok,d:d};});})
          .then(function(res){
            clearTimeout(to);
            if(res.ok && res.d.success){
              msg.className='dsOtpOk'; msg.textContent='Verified! Redirecting...';
              window.location.href = res.d.redirect || '/';
            } else {
              msg.className='dsOtpErr'; msg.textContent = (res.d && res.d.message) || 'Something went wrong.';
            }
          })
          .catch(function(err){
            clearTimeout(to);
            msg.className='dsOtpErr';
            msg.textContent = (err && err.name === 'AbortError') ? 'This is taking too long. Please try again.' : 'Network error. Please try again.';
          });
      });
      document.getElementById('dsOtpResend').addEventListener('click', function(){
        if (rsBtn.disabled) return;
        rsBtn.disabled = true;
        msg.className=''; msg.textContent='Sending...';
        var ctrl2 = new AbortController();
        var to2 = setTimeout(function(){ ctrl2.abort(); }, 15000);
        fetch('/wp-json/ds-otp/v1/resend', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({user_id:uid}),signal:ctrl2.signal})
          .then(function(r){return r.json().then(function(d){return {ok:r.ok,d:d};});})
          .then(function(res){
            clearTimeout(to2);
            msg.className = res.ok ? 'dsOtpOk' : 'dsOtpErr';
            msg.textContent = res.ok ? 'A new code has been sent.' : ((res.d && res.d.message) || 'Please wait before requesting another.');
            rsTimer(res.ok ? <?php echo (int) DS_OTP_RESEND_COOLDOWN; ?> : ((res.d && res.d.data && res.d.data.wait) || 0));
          })
          .catch(function(err){
            clearTimeout(to2);
            msg.className='dsOtpErr';
            msg.textContent = (err && err.name === 'AbortError') ? 'This is taking too long. Please try again in a moment.' : 'Network error. Please try again.';
            rsTimer(0);
          });
      });
    })();
    </script>
    <?php
}, 100);

// Admin: show an at-a-glance verification badge on the Users list page for
// accounts on the OTP flow. Pre-existing accounts (created before this went
// live) don't have the ds_otp_flow_v1 flag, so they show no badge at all.
add_filter('manage_users_columns', function($columns){
    $columns['ds_email_status'] = 'Email Status';
    return $columns;
});

add_filter('manage_users_custom_column', function($output, $column_name, $user_id){
    if ($column_name !== 'ds_email_status') return $output;
    if ((int) get_user_meta($user_id, 'ds_otp_flow_v1', true) !== 1) return $output;
    if (get_user_meta($user_id, 'ds_email_verified', true)) {
        return '<span style="color:#0a7d2c;font-weight:600;">&#9989; Verified</span>';
    }
    return '<span style="color:#b45309;font-weight:600;">&#9203; Unverified</span>';
}, 10, 3);
