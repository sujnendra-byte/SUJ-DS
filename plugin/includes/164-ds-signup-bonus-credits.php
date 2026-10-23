<?php
/**
 * Code Snippets export — DS Sign-up Bonus Credits
 *
 * Snippet ID : 894
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-credits
 * Modified   : 2026-09-29 13:33:00
 * Load order : 164 of 158
 */
/**
 * DS Sign-up Bonus Credits
 * Run: Everywhere
 *
 * Gives every new account a one-time bonus of free AI credits (default 5).
 * WP Admin > DS Credits > Sign-up Bonus: switch it on / off and set the amount.
 *  - Given once per account (user meta ds_signup_bonus_given), only to accounts
 *    created after it is switched on. Users an admin adds in wp-admin get nothing.
 *  - Credits go in through ds_add_credits() (the top-up pool), so they show in the
 *    normal balance and follow the same 1-month expiry as other top-up credits.
 * Undo: switch it off on the settings page, or deactivate this snippet.
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('ds_signup_bonus_settings') ) {

function ds_signup_bonus_settings() {
    $o = get_option('ds_signup_bonus', array());
    return array(
        'enabled' => ! empty($o['enabled']),
        'amount'  => isset($o['amount']) ? max(0, (int) $o['amount']) : 5,
    );
}

/* grant on registration (priority 20: after the credit core resets new users to 0) */
add_action('user_register', function ( $uid ) {
    $s = ds_signup_bonus_settings();
    if ( ! $s['enabled'] || $s['amount'] <= 0 ) return;
    // an admin adding a user from wp-admin is not a sign-up
    if ( is_admin() && ! wp_doing_ajax() && current_user_can('create_users') ) return;
    if ( get_user_meta($uid, 'ds_signup_bonus_given', true) ) return;
    if ( function_exists('ds_add_credits') ) {
        ds_add_credits($uid, $s['amount']);
    } else {
        $cur = (int) get_user_meta($uid, 'ds_ai_credits', true);
        update_user_meta($uid, 'ds_ai_credits', $cur + $s['amount']);
    }
    update_user_meta($uid, 'ds_signup_bonus_given', $s['amount']);
    update_user_meta($uid, 'ds_signup_bonus_date', current_time('mysql'));
}, 20);

/* settings page: DS Credits > Sign-up Bonus (falls back to Settings if that menu is missing) */
add_action('admin_menu', function () {
    global $admin_page_hooks;
    $parent = isset($admin_page_hooks['ds-credit-settings']) ? 'ds-credit-settings' : 'options-general.php';
    add_submenu_page($parent, 'Sign-up Bonus Credits', 'Sign-up Bonus', 'manage_options', 'ds-signup-bonus', 'ds_signup_bonus_page');
}, 20);

function ds_signup_bonus_page() {
    if ( ! current_user_can('manage_options') ) return;
    if ( isset($_POST['ds_sb_save']) && check_admin_referer('ds_signup_bonus_save') ) {
        update_option('ds_signup_bonus', array(
            'enabled' => ! empty($_POST['ds_sb_enabled']) ? 1 : 0,
            'amount'  => max(0, min(1000, (int) ($_POST['ds_sb_amount'] ?? 5))),
        ));
        echo '<div class="notice notice-success is-dismissible"><p>Sign-up bonus settings saved.</p></div>';
    }
    $s = ds_signup_bonus_settings();
    global $wpdb;
    $given = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'ds_signup_bonus_given'");
    ?>
    <div class="wrap">
      <h1>Sign-up Bonus Credits</h1>
      <p>Give every new account free AI credits once, when they sign up.</p>
      <form method="post">
        <?php wp_nonce_field('ds_signup_bonus_save'); ?>
        <table class="form-table" role="presentation">
          <tr>
            <th scope="row">Status</th>
            <td><label><input type="checkbox" name="ds_sb_enabled" value="1" <?php checked($s['enabled']); ?>> Give free credits to new sign-ups</label>
              <p class="description">Currently: <strong style="color:<?php echo $s['enabled'] ? '#15803d' : '#b91c1c'; ?>"><?php echo $s['enabled'] ? 'ACTIVE' : 'STOPPED'; ?></strong></p></td>
          </tr>
          <tr>
            <th scope="row"><label for="ds_sb_amount">Credits per sign-up</label></th>
            <td><input type="number" min="0" max="1000" step="1" id="ds_sb_amount" name="ds_sb_amount" value="<?php echo esc_attr($s['amount']); ?>" class="small-text">
              <p class="description">Added once to each new account (same 1-month expiry as top-up credits). Existing users are not affected.</p></td>
          </tr>
        </table>
        <p class="submit"><button type="submit" name="ds_sb_save" value="1" class="button button-primary">Save Changes</button></p>
      </form>
      <p><strong><?php echo number_format_i18n($given); ?></strong> account(s) have received the sign-up bonus so far.</p>
    </div>
    <?php
}

}
