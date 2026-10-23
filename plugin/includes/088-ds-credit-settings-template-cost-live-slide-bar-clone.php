<?php
/**
 * Code Snippets export — DS Credit Settings [Template Cost + Live Slide Bar] [CLONE]
 *
 * Snippet ID : 769
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-083
 * Modified   : 2026-09-29 16:39:14
 * Load order : 088 of 144
 */
if ( ! defined('ABSPATH') ) exit;

/* ============================================================
 * DS Credit Settings
 * Admin-configurable pricing for AI Image models, AI Video models,
 * and AI Presentation slides.
 * Powers (via get_option, read directly by these snippets):
 *   - ds_get_model_costs() / ds_get_model_cost_each()   (Images  - Snippet 384)
 *   - ds_get_video_model_costs() / ds_get_video_cost()  (Video   - Snippet 385)
 *   - ds_ppt_credit_cost()                              (Slides  - Snippet 386)
 * Options used: ds_image_credit_settings, ds_video_credit_settings, ds_ppt_credit_settings
 * ============================================================ */

if ( ! function_exists('ds_credit_settings_image_defaults') ) {
function ds_credit_settings_image_defaults() {
    return array(
        'models' => array(
            'cloudflare'       => 2,
            'cloudflare-klein' => 3,
            'hidream-fast'     => 4,
            'flux-schnell'     => 4,
            'flux-dev'         => 7,
            'qwen-image'       => 8,
            'qwen-edit'        => 10,
            'seedream'         => 8,
            'flux-pro'         => 10,
            'nanobana'         => 13,
        ),
        'resolution_overrides' => array(
            'nanobana' => array( '4K' => 19 ),
        ),
    );
}
}

if ( ! function_exists('ds_credit_settings_video_defaults') ) {
function ds_credit_settings_video_defaults() {
    return array(
        'models' => array(
            'seedance-1.5-pro' => 15,
        ),
        'resolution_multipliers' => array(
            '480p'  => 0.6,
            '720p'  => 1.0,
            '1080p' => 1.8,
        ),
        'audio_multiplier' => 2.0,
    );
}
}

if ( ! function_exists('ds_credit_settings_ppt_defaults') ) {
function ds_credit_settings_ppt_defaults() {
    return array(
        'base_slides'      => 5,
        'base_credits'     => 5,
        'extra_slide_rate' => 1,
        'template_cost'    => 5,
    );
}
}

if ( ! function_exists('ds_credit_settings_audio_defaults') ) {
function ds_credit_settings_audio_defaults() {
return array(
'models' => array(
'ace-step-1.5' => 6,
'speech-02-hd' => 4,
),
'platform_charge' => 1,
'music_tiers' => array(1, 2, 3),
'speech_tiers' => array(2, 4, 8, 12),
);
}
}

add_action('admin_menu', function () {
    add_menu_page(
        'DS Credit Settings',
        'DS Credits',
        'manage_options',
        'ds-credit-settings',
        'ds_render_credit_settings_page',
        'dashicons-chart-line',
        61
    );
});

if ( ! function_exists('ds_render_credit_settings_page') ) {
function ds_render_credit_settings_page() {
    if ( ! current_user_can('manage_options') ) return;

    $notice = '';

    if ( isset($_POST['ds_credit_image_save']) && check_admin_referer('ds_credit_image_save_action', 'ds_credit_image_nonce') ) {
        $defaults = ds_credit_settings_image_defaults();
        $models = array();
        foreach ( $defaults['models'] as $key => $default_cost ) {
            $posted = isset($_POST['ds_img_model_'.$key]) ? $_POST['ds_img_model_'.$key] : '';
            $models[$key] = ( $posted !== '' ) ? max(0, (int)$posted) : $default_cost;
        }
        $overrides = array();
        if ( isset($_POST['ds_img_ov_model']) && is_array($_POST['ds_img_ov_model']) ) {
            $ov_models  = $_POST['ds_img_ov_model'];
            $ov_res     = isset($_POST['ds_img_ov_res']) ? $_POST['ds_img_ov_res'] : array();
            $ov_credits = isset($_POST['ds_img_ov_credits']) ? $_POST['ds_img_ov_credits'] : array();
            foreach ( $ov_models as $i => $mk ) {
                $mk = sanitize_text_field($mk);
                $rk = isset($ov_res[$i]) ? strtoupper(sanitize_text_field($ov_res[$i])) : '';
                $ck = isset($ov_credits[$i]) ? $ov_credits[$i] : '';
                if ( $mk === '' || $rk === '' || $ck === '' ) continue;
                if ( ! isset($overrides[$mk]) ) $overrides[$mk] = array();
                $overrides[$mk][$rk] = max(0, (int)$ck);
            }
        }
        update_option('ds_image_credit_settings', array('models' => $models, 'resolution_overrides' => $overrides));
        $notice = 'Image model credit settings saved.';
    }

    if ( isset($_POST['ds_credit_video_save']) && check_admin_referer('ds_credit_video_save_action', 'ds_credit_video_nonce') ) {
        $models = array();
        if ( isset($_POST['ds_vid_model_key']) && is_array($_POST['ds_vid_model_key']) ) {
            $keys  = $_POST['ds_vid_model_key'];
            $costs = isset($_POST['ds_vid_model_cost']) ? $_POST['ds_vid_model_cost'] : array();
            foreach ( $keys as $i => $mk ) {
                $mk = sanitize_text_field($mk);
                $ck = isset($costs[$i]) ? $costs[$i] : '';
                if ( $mk === '' || $ck === '' ) continue;
                $models[$mk] = max(0, (int)$ck);
            }
        }
        if ( empty($models) ) {
            $vdef = ds_credit_settings_video_defaults();
            $models = $vdef['models'];
        }

        $res_mults = array(
            '480p'  => ( isset($_POST['ds_vid_res_480p']) && $_POST['ds_vid_res_480p'] !== '' ) ? (float)$_POST['ds_vid_res_480p'] : 0.6,
            '720p'  => ( isset($_POST['ds_vid_res_720p']) && $_POST['ds_vid_res_720p'] !== '' ) ? (float)$_POST['ds_vid_res_720p'] : 1.0,
            '1080p' => ( isset($_POST['ds_vid_res_1080p']) && $_POST['ds_vid_res_1080p'] !== '' ) ? (float)$_POST['ds_vid_res_1080p'] : 1.8,
        );
        $audio_mult = ( isset($_POST['ds_vid_audio_mult']) && $_POST['ds_vid_audio_mult'] !== '' ) ? (float)$_POST['ds_vid_audio_mult'] : 2.0;

        update_option('ds_video_credit_settings', array('models' => $models, 'resolution_multipliers' => $res_mults, 'audio_multiplier' => $audio_mult));
        $notice = 'Video model credit settings saved.';
    }

    if ( isset($_POST['ds_credit_ppt_save']) && check_admin_referer('ds_credit_ppt_save_action', 'ds_credit_ppt_nonce') ) {
        $base_slides  = ( isset($_POST['ds_ppt_base_slides']) && $_POST['ds_ppt_base_slides'] !== '' ) ? max(1, (int)$_POST['ds_ppt_base_slides']) : 5;
        $base_credits = ( isset($_POST['ds_ppt_base_credits']) && $_POST['ds_ppt_base_credits'] !== '' ) ? max(0, (int)$_POST['ds_ppt_base_credits']) : 5;
        $extra_rate   = ( isset($_POST['ds_ppt_extra_rate']) && $_POST['ds_ppt_extra_rate'] !== '' ) ? max(0, (int)$_POST['ds_ppt_extra_rate']) : 1;
        $template_cost = ( isset($_POST['ds_ppt_template_cost']) && $_POST['ds_ppt_template_cost'] !== '' ) ? max(0, (int)$_POST['ds_ppt_template_cost']) : 5;
        update_option('ds_ppt_credit_settings', array('base_slides' => $base_slides, 'base_credits' => $base_credits, 'extra_slide_rate' => $extra_rate, 'template_cost' => $template_cost, 'add_slide_cost' => ( isset($_POST['ds_ppt_add_slide_cost']) && $_POST['ds_ppt_add_slide_cost'] !== '' ) ? max(1, (int) $_POST['ds_ppt_add_slide_cost']) : 5));
        $notice = 'Presentation credit settings saved.';
    }

if ( isset($_POST['ds_credit_audio_save']) && check_admin_referer('ds_credit_audio_save_action', 'ds_credit_audio_nonce') ) {
$adef = ds_credit_settings_audio_defaults();
$amodels = $adef['models'];
$platform_charge = ( isset($_POST['ds_audio_platform_charge']) && $_POST['ds_audio_platform_charge'] !== '' ) ? max(0, (int)$_POST['ds_audio_platform_charge']) : $adef['platform_charge'];
$music_tiers = array();
for ( $i = 0; $i < 3; $i++ ) {
$posted = isset($_POST['ds_audio_music_tier_'.$i]) ? $_POST['ds_audio_music_tier_'.$i] : '';
$music_tiers[$i] = ( $posted !== '' ) ? max(0, (int)$posted) : $adef['music_tiers'][$i];
}
$speech_tiers = array();
for ( $i = 0; $i < 4; $i++ ) {
$posted = isset($_POST['ds_audio_speech_tier_'.$i]) ? $_POST['ds_audio_speech_tier_'.$i] : '';
$speech_tiers[$i] = ( $posted !== '' ) ? max(0, (int)$posted) : $adef['speech_tiers'][$i];
}
update_option('ds_audio_credit_settings', array('models' => $amodels, 'platform_charge' => $platform_charge, 'music_tiers' => $music_tiers, 'speech_tiers' => $speech_tiers));
$notice = 'Audio credit settings saved.';
}

    $img = get_option('ds_image_credit_settings');
    if ( ! is_array($img) || empty($img['models']) ) $img = ds_credit_settings_image_defaults();
    $vid = get_option('ds_video_credit_settings');
    if ( ! is_array($vid) || empty($vid['models']) ) $vid = ds_credit_settings_video_defaults();
    if ( ! isset($vid['resolution_multipliers']) || ! is_array($vid['resolution_multipliers']) ) {
        $vdef = ds_credit_settings_video_defaults();
        $vid['resolution_multipliers'] = $vdef['resolution_multipliers'];
    }
    if ( ! isset($vid['audio_multiplier']) ) $vid['audio_multiplier'] = 2.0;
    $ppt = get_option('ds_ppt_credit_settings');
    if ( ! is_array($ppt) || empty($ppt) ) $ppt = ds_credit_settings_ppt_defaults();

$audio = get_option('ds_audio_credit_settings');
if ( ! is_array($audio) || empty($audio['models']) ) $audio = ds_credit_settings_audio_defaults();
if ( ! isset($audio['platform_charge']) ) $audio['platform_charge'] = 1;
if ( ! isset($audio['music_tiers']) || ! is_array($audio['music_tiers']) || count($audio['music_tiers']) !== 3 ) $audio['music_tiers'] = array(1, 2, 3);
if ( ! isset($audio['speech_tiers']) || ! is_array($audio['speech_tiers']) || count($audio['speech_tiers']) !== 4 ) $audio['speech_tiers'] = array(2, 4, 8, 12);

    $img_labels = array(
        'cloudflare'       => 'Cloudflare FLUX-1-Schnell',
        'cloudflare-klein' => 'Cloudflare FLUX.2 Klein 9B',
        'hidream-fast'     => 'HiDream Fast',
        'flux-schnell'     => 'Flux Schnell',
        'flux-dev'         => 'Flux Dev',
        'qwen-image'       => 'Qwen Image',
        'qwen-edit'        => 'Qwen Image Edit',
        'seedream'         => 'Seedream 5',
        'flux-pro'         => 'Flux Pro',
        'nanobana'         => 'NanoBanana 2',
    );
    $audio_labels = array(
'ace-step-1.5' => 'Music / Background Music (ACE-Step 1.5)',
'speech-02-hd' => 'Text to Speech (MiniMax Speech 02 HD)',
);
?>
    <div class="wrap">
        <h1>DS Credit Settings</h1>
        <?php if ($notice): ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($notice); ?></p></div><?php endif; ?>
        <p>Set how many credits each AI model / resolution / presentation slide costs. Changes apply immediately, site-wide.</p>

        <h2>AI Image Models</h2>
        <form method="post">
            <?php wp_nonce_field('ds_credit_image_save_action', 'ds_credit_image_nonce'); ?>
            <table class="form-table">
                <tr><th>Model</th><th>Credits (base)</th></tr>
                <?php foreach ($img['models'] as $key => $cost): ?>
                <tr>
                    <th><?php echo esc_html( isset($img_labels[$key]) ? $img_labels[$key] : $key ); ?> <code><?php echo esc_html($key); ?></code></th>
                    <td><input type="number" min="0" step="1" name="ds_img_model_<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($cost); ?>" style="width:90px;"></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <h3>Per-Resolution Overrides</h3>
            <p>Set an exact credit cost for a specific model at a specific output size (e.g. NanoBanana at 4K). Leave a row blank to ignore it. Overrides win over the base cost above.</p>
            <table class="form-table">
                <tr><th>Model key</th><th>Resolution (e.g. 4K)</th><th>Credits</th></tr>
                <?php
                $ov_rows = array();
                $ov_source = ( isset($img['resolution_overrides']) && is_array($img['resolution_overrides']) ) ? $img['resolution_overrides'] : array();
                foreach ( $ov_source as $mk => $reslist ) {
                    foreach ( $reslist as $rk => $rc ) {
                        $ov_rows[] = array($mk, $rk, $rc);
                    }
                }
                while ( count($ov_rows) < 10 ) { $ov_rows[] = array('', '', ''); }
                foreach ($ov_rows as $row):
                ?>
                <tr>
                    <td><input type="text" name="ds_img_ov_model[]" value="<?php echo esc_attr($row[0]); ?>" style="width:160px;" placeholder="e.g. nanobana"></td>
                    <td><input type="text" name="ds_img_ov_res[]" value="<?php echo esc_attr($row[1]); ?>" style="width:100px;" placeholder="e.g. 4K"></td>
                    <td><input type="number" min="0" step="1" name="ds_img_ov_credits[]" value="<?php echo esc_attr($row[2]); ?>" style="width:90px;"></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button('Save Image Credit Settings', 'primary', 'ds_credit_image_save'); ?>
        </form>

        <hr>

        <h2>AI Video Models</h2>
        <form method="post">
            <?php wp_nonce_field('ds_credit_video_save_action', 'ds_credit_video_nonce'); ?>
            <table class="form-table">
                <tr><th>Model key</th><th>Base credits (@720p / 5s / audio off)</th></tr>
                <?php
                $vid_rows = array();
                foreach ($vid['models'] as $mk => $mc) { $vid_rows[] = array($mk, $mc); }
                while ( count($vid_rows) < 5 ) { $vid_rows[] = array('', ''); }
                foreach ($vid_rows as $row):
                ?>
                <tr>
                    <td><input type="text" name="ds_vid_model_key[]" value="<?php echo esc_attr($row[0]); ?>" style="width:160px;" placeholder="e.g. seedance-1.5-pro"></td>
                    <td><input type="number" min="0" step="1" name="ds_vid_model_cost[]" value="<?php echo esc_attr($row[1]); ?>" style="width:90px;"></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <h3>Resolution Multipliers</h3>
            <table class="form-table">
                <tr><th>480p</th><td><input type="number" min="0" step="0.1" name="ds_vid_res_480p" value="<?php echo esc_attr($vid['resolution_multipliers']['480p']); ?>" style="width:90px;"></td></tr>
                <tr><th>720p</th><td><input type="number" min="0" step="0.1" name="ds_vid_res_720p" value="<?php echo esc_attr($vid['resolution_multipliers']['720p']); ?>" style="width:90px;"></td></tr>
                <tr><th>1080p</th><td><input type="number" min="0" step="0.1" name="ds_vid_res_1080p" value="<?php echo esc_attr($vid['resolution_multipliers']['1080p']); ?>" style="width:90px;"></td></tr>
                <tr><th>Audio-on multiplier</th><td><input type="number" min="0" step="0.1" name="ds_vid_audio_mult" value="<?php echo esc_attr($vid['audio_multiplier']); ?>" style="width:90px;"></td></tr>
            </table>
            <p>Final cost = base credits &times; resolution multiplier &times; (duration &divide; 5s) &times; audio multiplier (if audio on), rounded up.</p>
            <?php submit_button('Save Video Credit Settings', 'primary', 'ds_credit_video_save'); ?>
        </form>

        <hr>

        <h2>AI Presentation Slides</h2>
        <form method="post">
            <?php wp_nonce_field('ds_credit_ppt_save_action', 'ds_credit_ppt_nonce'); ?>
            <table class="form-table">
                <tr><th>Flat-rate slide count</th><td><input type="number" min="1" step="1" name="ds_ppt_base_slides" value="<?php echo esc_attr($ppt['base_slides']); ?>" style="width:90px;"> slides</td></tr>
                <tr><th>Flat credits for those slides</th><td><input type="number" min="0" step="1" name="ds_ppt_base_credits" value="<?php echo esc_attr($ppt['base_credits']); ?>" style="width:90px;"> credits</td></tr>
                <tr><th>Credits per additional slide</th><td><input type="number" min="0" step="1" name="ds_ppt_extra_rate" value="<?php echo esc_attr($ppt['extra_slide_rate']); ?>" style="width:90px;"> credits/slide</td></tr>
                <tr><th>Template selection cost</th><td><input type="number" min="0" step="1" name="ds_ppt_template_cost" value="<?php echo esc_attr( isset($ppt['template_cost']) ? $ppt['template_cost'] : 5 ); ?>" style="width:90px;"> credits (charged when a user picks a ready-made design from Browse Templates)</td></tr>
                <tr><th>Credits per Add Slide</th><td><input type="number" min="1" step="1" name="ds_ppt_add_slide_cost" value="<?php echo esc_attr( isset($ppt['add_slide_cost']) ? $ppt['add_slide_cost'] : 5 ); ?>" style="width:90px;"> credits (each "Add Slide with AI" or chart slide added in the editor)</td></tr>
            </table>
            <p>Example: a deck with <?php echo (int)$ppt['base_slides'] + 3; ?> slides costs <?php echo (int)$ppt['base_credits'] + 3*(int)$ppt['extra_slide_rate']; ?> credits (first <?php echo (int)$ppt['base_slides']; ?> = <?php echo (int)$ppt['base_credits']; ?> credits flat, then 3 &times; <?php echo (int)$ppt['extra_slide_rate']; ?>).</p>
            <?php submit_button('Save Presentation Credit Settings', 'primary', 'ds_credit_ppt_save'); ?>
        </form>

<hr>

<h2>AI Audio</h2>
<form method="post">
<?php wp_nonce_field('ds_credit_audio_save_action', 'ds_credit_audio_nonce'); ?>
<table class="form-table">
<tr><th colspan="2">Music and Background Music (ACE-Step 1.5) - duration tiers</th></tr>
<tr><th>Up to 80 seconds</th><td><input type="number" min="0" step="1" name="ds_audio_music_tier_0" value="<?php echo esc_attr($audio['music_tiers'][0]); ?>" style="width:90px;"> credits</td></tr>
<tr><th>81-160 seconds</th><td><input type="number" min="0" step="1" name="ds_audio_music_tier_1" value="<?php echo esc_attr($audio['music_tiers'][1]); ?>" style="width:90px;"> credits</td></tr>
<tr><th>161-240 seconds</th><td><input type="number" min="0" step="1" name="ds_audio_music_tier_2" value="<?php echo esc_attr($audio['music_tiers'][2]); ?>" style="width:90px;"> credits</td></tr>
<tr><th>Platform charge (added on top of every music generation)</th><td><input type="number" min="0" step="1" name="ds_audio_platform_charge" value="<?php echo esc_attr($audio['platform_charge']); ?>" style="width:90px;"> credits</td></tr>
</table>
<table class="form-table">
<tr><th colspan="2">Text to Speech (MiniMax Speech 02 HD) - character-count tiers (total price - platform charge is NOT added)</th></tr>
<tr><th>1-500 characters</th><td><input type="number" min="0" step="1" name="ds_audio_speech_tier_0" value="<?php echo esc_attr($audio['speech_tiers'][0]); ?>" style="width:90px;"> credits</td></tr>
<tr><th>501-2,000 characters</th><td><input type="number" min="0" step="1" name="ds_audio_speech_tier_1" value="<?php echo esc_attr($audio['speech_tiers'][1]); ?>" style="width:90px;"> credits</td></tr>
<tr><th>2,001-5,000 characters</th><td><input type="number" min="0" step="1" name="ds_audio_speech_tier_2" value="<?php echo esc_attr($audio['speech_tiers'][2]); ?>" style="width:90px;"> credits</td></tr>
<tr><th>5,001-10,000 characters</th><td><input type="number" min="0" step="1" name="ds_audio_speech_tier_3" value="<?php echo esc_attr($audio['speech_tiers'][3]); ?>" style="width:90px;"> credits</td></tr>
</table>
<p>Music and Background Music: cost is looked up from the duration tier the slider lands on, then the platform charge is added once. Example: a 90 second track falls in the 81-160s tier.</p>
<p>Text to Speech: cost is looked up from the character-count tier the typed text lands on - that tier value is the full price; the platform charge is not added on top of it.</p>
<p>Submitting a Music or Background Music file to the Marketplace currently gives the creator 1 credit back once approved. Text to Speech files cannot be submitted to the Marketplace.</p>
<?php submit_button('Save Audio Credit Settings', 'primary', 'ds_credit_audio_save'); ?>
</form>

    </div>
    <?php
}
}

/* Price of one "Add Slide with AI" (normal or chart slide) in the presentation editor */
if ( ! function_exists('ds_ppt_add_slide_cost') ) {
function ds_ppt_add_slide_cost() {
    $o = get_option('ds_ppt_credit_settings');
    $c = ( is_array($o) && isset($o['add_slide_cost']) && $o['add_slide_cost'] !== '' ) ? (int) $o['add_slide_cost'] : 5;
    return max(1, (int) apply_filters('ds_ppt_add_slide_cost', $c));
}
}
