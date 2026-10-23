<?php
/**
 * Code Snippets export — DS Saved Items Cap Settings
 *
 * Snippet ID : 754
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-068
 * Modified   : 2026-09-09 02:41:42
 * Load order : 068 of 144
 */
/**
 * Snippet Name: DS Saved Items Cap Settings
 * Run: Everywhere
 * Purpose: Admin-editable per-plan limits on how many items (images/videos/presentations,
 * combined) a user may permanently save. Provides ds_get_user_saved_cap($uid), which the
 * save-gate checks (images/videos, PPT builder, PPT drafts) and the hub's cap display call
 * instead of the old flat DS_SAVED_ITEMS_CAP constant.
 */

if ( ! function_exists('ds_saved_cap_defaults') ) {
    function ds_saved_cap_defaults() {
        return array(
            'free'     => 1,
            'premium'  => 25,
            'pro'      => 50,
            'business' => 100,
        );
    }
}

if ( ! function_exists('ds_get_saved_caps') ) {
    function ds_get_saved_caps() {
        $defaults = ds_saved_cap_defaults();
        $saved    = get_option( 'ds_saved_item_caps', array() );
        if ( ! is_array( $saved ) ) $saved = array();
        $out = array();
        foreach ( $defaults as $key => $def ) {
            $out[ $key ] = isset( $saved[ $key ] ) ? max( 0, (int) $saved[ $key ] ) : $def;
        }
        return $out;
    }
}

if ( ! function_exists('ds_get_user_saved_cap') ) {
    function ds_get_user_saved_cap( $uid ) {
        $caps = ds_get_saved_caps();
        $plan = function_exists('ds_access_plan') ? ds_access_plan( $uid ) : ( function_exists('ds_get_user_plan') ? ds_get_user_plan( $uid ) : 'free' );
        if ( ! isset( $caps[ $plan ] ) ) $plan = 'free';
        return (int) $caps[ $plan ];
    }
}

add_action('admin_menu', function() {
    add_menu_page(
        'DS Saved Items Limits',
        'DS Save Limits',
        'manage_options',
        'ds-save-limits-settings',
        function() {
            if ( ! current_user_can('manage_options') ) {
                return;
            }

            $notice   = '';
            $defaults = ds_saved_cap_defaults();

            if ( isset($_POST['ds_save_limits_save']) && check_admin_referer('ds_save_limits_save_action', 'ds_save_limits_nonce') ) {
                $new = array();
                foreach ( $defaults as $key => $def ) {
                    $new[ $key ] = isset($_POST['ds_cap'][$key]) ? max(0, intval($_POST['ds_cap'][$key])) : $def;
                }
                update_option( 'ds_saved_item_caps', $new );
                $notice = 'Saved-item limits updated.';
            }

            $current = ds_get_saved_caps();
            ?>
            <div class="wrap">
                <h1>Saved Items Limits</h1>
                <?php if ( $notice ): ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($notice); ?></p></div><?php endif; ?>
                <p>Set how many items (images, videos, and presentation drafts — combined, one shared pool) each plan may permanently save at once. Changes apply immediately, site-wide.</p>
                <form method="post">
                    <?php wp_nonce_field('ds_save_limits_save_action', 'ds_save_limits_nonce'); ?>
                    <table class="form-table">
                        <tr><th scope="row">Free</th><td><input type="number" min="0" name="ds_cap[free]" value="<?php echo esc_attr($current['free']); ?>" style="width:100px;"/></td></tr>
                        <tr><th scope="row">Premium</th><td><input type="number" min="0" name="ds_cap[premium]" value="<?php echo esc_attr($current['premium']); ?>" style="width:100px;"/></td></tr>
                        <tr><th scope="row">Pro</th><td><input type="number" min="0" name="ds_cap[pro]" value="<?php echo esc_attr($current['pro']); ?>" style="width:100px;"/></td></tr>
                        <tr><th scope="row">Business</th><td><input type="number" min="0" name="ds_cap[business]" value="<?php echo esc_attr($current['business']); ?>" style="width:100px;"/></td></tr>
                    </table>
                    <?php submit_button('Save Limits', 'primary', 'ds_save_limits_save'); ?>
                </form>
            </div>
            <?php
        },
        'dashicons-star-filled',
        59
    );
});
