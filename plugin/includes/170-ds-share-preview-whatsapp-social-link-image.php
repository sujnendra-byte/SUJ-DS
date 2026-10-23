<?php
/**
 * Code Snippets export — DS Share Preview (WhatsApp / social link image)
 *
 * Snippet ID : 908
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, seo
 * Modified   : 2026-09-30 04:12:48
 * Load order : 170 of 162
 */
/**
 * DS Share Preview (WhatsApp / Facebook / LinkedIn / X link previews)
 * Run: Everywhere
 *
 * Fixes the blank / old-template preview when a DesignStager link is shared.
 * - Home page (and every referral link /?ref=CODE): your share image, title and description.
 * - Every other page: the default share image when the page has no image of its own
 *   (or on all pages, if you tick that option). Blog posts keep their own featured image.
 * - Adds any missing og:title / og:description / og:url / twitter:card tags.
 * WP Admin > Settings > Share Preview: upload the images and edit the text.
 * Works on top of the SEO plugin's tags (replaces only the image/description tags it needs to).
 * Does not modify any other snippet or plugin settings. Deactivate to undo.
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('dsog_settings') ) {

function dsog_defaults() {
	return array(
		'enabled'       => 1,
		'home_image'    => 0,
		'default_image' => 0,
		'all_pages'     => 1,   // use the default image on every page except blog posts
		'home_title'    => 'DesignStager — AI Presentations, Images, Videos & Designs',
		'home_desc'     => 'Create professional presentations, images, videos, voiceovers and designs — faster, with the power of AI. Everything you need to design, present and grow, in one place.',
		'default_desc'  => 'Create professional presentations, images, videos, voiceovers and designs with AI — all in one place.',
	);
}

function dsog_settings() {
	$o = get_option('dsog_settings', array());
	return wp_parse_args(is_array($o) ? $o : array(), dsog_defaults());
}

function dsog_image($id) {
	$id = (int) $id;
	if (!$id) return null;
	$src = wp_get_attachment_image_src($id, 'full');
	if (!$src) return null;
	return array(
		'url'  => $src[0],
		'w'    => (int) $src[1],
		'h'    => (int) $src[2],
		'type' => get_post_mime_type($id) ?: 'image/jpeg',
		'alt'  => trim((string) get_post_meta($id, '_wp_attachment_image_alt', true)),
	);
}

function dsog_has($head, $prop) {
	return (bool) preg_match('/<meta[^>]+(?:property|name)=["\']' . preg_quote($prop, '/') . '["\'][^>]*>/i', $head);
}

function dsog_strip($head, $props_regex) {
	return preg_replace('/[ \t]*<meta[^>]+(?:property|name)=["\'](?:' . $props_regex . ')["\'][^>]*>\s*/i', '', $head);
}

function dsog_meta($attr, $key, $value) {
	return '<meta ' . $attr . '="' . esc_attr($key) . '" content="' . esc_attr($value) . '">' . "\n";
}

/** Rewrites the finished <head> output. */
function dsog_filter_head($head) {
	$s = dsog_settings();
	if (!$s['enabled']) return $head;

	$is_home = is_front_page();
	$is_post = is_singular('post');
	$add = '';

	/* ---------- image ---------- */
	$img = null;
	$has_img = dsog_has($head, 'og:image');
	if ($is_home) {
		$img = dsog_image($s['home_image']) ?: dsog_image($s['default_image']);
	} elseif ($is_post) {
		if (!$has_img) {
			$img = has_post_thumbnail() ? dsog_image(get_post_thumbnail_id()) : null;
			if (!$img) $img = dsog_image($s['default_image']);
		}
	} elseif ($s['all_pages'] || !$has_img) {
		$img = dsog_image($s['default_image']);
	}
	if ($img) {
		$head = dsog_strip($head, 'og:image(?::[a-z_]+)?|twitter:image(?::alt)?');
		$add .= dsog_meta('property', 'og:image', $img['url']);
		if (strpos($img['url'], 'https://') === 0) $add .= dsog_meta('property', 'og:image:secure_url', $img['url']);
		$add .= dsog_meta('property', 'og:image:width', $img['w']);
		$add .= dsog_meta('property', 'og:image:height', $img['h']);
		$add .= dsog_meta('property', 'og:image:type', $img['type']);
		$alt = $img['alt'] !== '' ? $img['alt'] : get_bloginfo('name');
		$add .= dsog_meta('property', 'og:image:alt', $alt);
		$add .= dsog_meta('name', 'twitter:image', $img['url']);
		if (!dsog_has($head, 'twitter:card')) $add .= dsog_meta('name', 'twitter:card', 'summary_large_image');
	}

	/* ---------- title + description ---------- */
	if ($is_home) {
		if ($s['home_title'] !== '') {
			$head = dsog_strip($head, 'og:title|twitter:title');
			$add .= dsog_meta('property', 'og:title', $s['home_title']);
			$add .= dsog_meta('name', 'twitter:title', $s['home_title']);
		}
		if ($s['home_desc'] !== '') {
			$head = dsog_strip($head, 'og:description|twitter:description|description');
			$add .= dsog_meta('name', 'description', $s['home_desc']);
			$add .= dsog_meta('property', 'og:description', $s['home_desc']);
			$add .= dsog_meta('name', 'twitter:description', $s['home_desc']);
		}
	} else {
		if (!dsog_has($head, 'og:title')) {
			$t = wp_get_document_title();
			$add .= dsog_meta('property', 'og:title', $t);
			if (!dsog_has($head, 'twitter:title')) $add .= dsog_meta('name', 'twitter:title', $t);
		}
		if (!dsog_has($head, 'og:description')) {
			$d = '';
			if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)["\']/i', $head, $m)) $d = html_entity_decode($m[1], ENT_QUOTES);
			if ($d === '' && is_singular()) $d = wp_strip_all_tags(get_the_excerpt());
			if ($d === '') $d = $s['default_desc'];
			$add .= dsog_meta('property', 'og:description', $d);
			if (!dsog_has($head, 'twitter:description')) $add .= dsog_meta('name', 'twitter:description', $d);
		}
	}

	/* ---------- basics ---------- */
	if (!dsog_has($head, 'og:type')) $add .= dsog_meta('property', 'og:type', $is_post ? 'article' : 'website');
	if (!dsog_has($head, 'og:site_name')) $add .= dsog_meta('property', 'og:site_name', get_bloginfo('name'));
	if (!dsog_has($head, 'og:url')) {
		global $wp;
		$url = $is_home ? home_url('/') : home_url('/' . trailingslashit($wp->request));
		$add .= dsog_meta('property', 'og:url', $url);
	}
	if (!dsog_has($head, 'twitter:card') && strpos($add, 'twitter:card') === false) $add .= dsog_meta('name', 'twitter:card', 'summary_large_image');

	if ($add === '') return $head;
	return $head . "\n<!-- DS Share Preview -->\n" . $add . "<!-- /DS Share Preview -->\n";
}

add_action('wp_head', function () {
	if (is_admin() || is_feed() || wp_doing_ajax()) return;
	$GLOBALS['dsog_ob_level'] = ob_get_level();
	ob_start();
}, -99999);

add_action('wp_head', function () {
	if (!isset($GLOBALS['dsog_ob_level'])) return;
	// only close the buffer we opened
	if (ob_get_level() !== $GLOBALS['dsog_ob_level'] + 1) { unset($GLOBALS['dsog_ob_level']); return; }
	$head = ob_get_clean();
	unset($GLOBALS['dsog_ob_level']);
	echo dsog_filter_head($head);
}, PHP_INT_MAX);

/* =========================================================
 * ADMIN: Settings > Share Preview
 * ======================================================= */
add_action('admin_menu', function () {
	add_options_page('Share Preview', 'Share Preview', 'manage_options', 'ds-share-preview', 'dsog_admin');
});

add_action('admin_enqueue_scripts', function () {
	if (isset($_GET['page']) && $_GET['page'] === 'ds-share-preview') wp_enqueue_media();
});

function dsog_admin() {
	if (!current_user_can('manage_options')) return;
	$msg = '';
	if (isset($_POST['dsog_save']) && check_admin_referer('dsog_save')) {
		$p = wp_unslash($_POST);
		update_option('dsog_settings', array(
			'enabled'       => !empty($p['enabled']) ? 1 : 0,
			'home_image'    => absint($p['home_image'] ?? 0),
			'default_image' => absint($p['default_image'] ?? 0),
			'all_pages'     => !empty($p['all_pages']) ? 1 : 0,
			'home_title'    => sanitize_text_field($p['home_title'] ?? ''),
			'home_desc'     => sanitize_textarea_field($p['home_desc'] ?? ''),
			'default_desc'  => sanitize_textarea_field($p['default_desc'] ?? ''),
		));
		do_action('litespeed_purge_all');
		$msg = 'Saved. Share previews now use these settings.';
	}
	$s = dsog_settings();
	$home = home_url('/');
	?>
<div class="wrap">
<h1>Share Preview</h1>
<p>What people see when a DesignStager link is shared on WhatsApp, Facebook, LinkedIn, X and other apps.</p>
<?php if ($msg): ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($msg); ?></p></div><?php endif; ?>
<style>
.dsog-box{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:6px 20px 16px;margin:16px 0;max-width:980px}
.dsog-pick{display:flex;gap:24px;flex-wrap:wrap}
.dsog-slot{flex:1;min-width:280px}
.dsog-prev{border:2px dashed #cbd5e1;border-radius:12px;aspect-ratio:1200/630;display:flex;align-items:center;justify-content:center;background:#f8fafc;overflow:hidden;margin:8px 0}
.dsog-prev img{display:block;width:100%;height:100%;object-fit:cover}
.dsog-prev span{color:#94a3b8;font-size:13px;padding:20px;text-align:center}
.dsog-card{max-width:520px;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#f0f2f5;margin-top:10px}
.dsog-card img{display:block;width:100%;aspect-ratio:1200/630;object-fit:cover;background:#ddd}
.dsog-card div{padding:10px 12px;font-family:-apple-system,Segoe UI,sans-serif}
.dsog-card b{display:block;font-size:14px;color:#111}
.dsog-card small{display:block;font-size:12px;color:#555;margin-top:3px}
.dsog-card em{display:block;font-style:normal;font-size:11px;color:#888;margin-top:4px;text-transform:uppercase}
</style>
<form method="post">
<?php wp_nonce_field('dsog_save'); ?>
<div class="dsog-box">
<table class="form-table" role="presentation">
<tr><th>Status</th><td><label><input type="checkbox" name="enabled" value="1" <?php checked($s['enabled']); ?>> Use these share previews</label></td></tr>
</table>
<p class="description">Recommended image size: <strong>1200 × 630 px</strong>, JPG or PNG, under 600 KB (WhatsApp skips bigger images).</p>
<div class="dsog-pick">
<?php foreach (array('home_image' => 'Home page &amp; referral links', 'default_image' => 'All other pages') as $key => $label):
	$u = $s[$key] ? wp_get_attachment_image_url((int) $s[$key], 'large') : ''; ?>
	<div class="dsog-slot">
		<strong><?php echo $label; ?></strong>
		<div class="dsog-prev" id="<?php echo esc_attr($key); ?>_prev"><?php echo $u ? '<img src="' . esc_url($u) . '" alt="">' : '<span>No image chosen</span>'; ?></div>
		<input type="hidden" name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($s[$key]); ?>">
		<button type="button" class="button dsog-upload" data-target="<?php echo esc_attr($key); ?>">Upload / choose image</button>
		<button type="button" class="button-link dsog-remove" data-target="<?php echo esc_attr($key); ?>" style="margin-left:8px;color:#b32d2e;">Remove</button>
	</div>
<?php endforeach; ?>
</div>
<table class="form-table" role="presentation">
<tr><th>Other pages</th><td><label><input type="checkbox" name="all_pages" value="1" <?php checked($s['all_pages']); ?>> Use the "All other pages" image on every page</label>
<p class="description">Untick to use it only on pages without their own image. Blog posts always keep their featured image.</p></td></tr>
</table>
</div>

<div class="dsog-box">
<h2>Home page text</h2>
<table class="form-table" role="presentation">
<tr><th>Title</th><td><input type="text" name="home_title" value="<?php echo esc_attr($s['home_title']); ?>" class="large-text"></td></tr>
<tr><th>Description</th><td><textarea name="home_desc" rows="3" class="large-text"><?php echo esc_textarea($s['home_desc']); ?></textarea><p class="description">Also used as the home page's search description.</p></td></tr>
<tr><th>Fallback description</th><td><textarea name="default_desc" rows="2" class="large-text"><?php echo esc_textarea($s['default_desc']); ?></textarea><p class="description">Used on pages that have no description of their own.</p></td></tr>
</table>
<p><strong>Preview</strong></p>
<div class="dsog-card">
	<?php $hu = $s['home_image'] ? wp_get_attachment_image_url((int) $s['home_image'], 'large') : ''; ?>
	<?php if ($hu): ?><img src="<?php echo esc_url($hu); ?>" alt=""><?php endif; ?>
	<div><b><?php echo esc_html($s['home_title']); ?></b><small><?php echo esc_html(wp_trim_words($s['home_desc'], 22)); ?></small><em><?php echo esc_html(wp_parse_url($home, PHP_URL_HOST)); ?></em></div>
</div>
</div>

<div class="dsog-box">
<h2>After changing the image</h2>
<p>Apps remember a link's preview for a while. To refresh it: open the <a href="<?php echo esc_url('https://developers.facebook.com/tools/debug/?q=' . rawurlencode($home)); ?>" target="_blank" rel="noopener">Facebook Sharing Debugger</a> and click <em>Scrape Again</em> (this also refreshes WhatsApp and Instagram). Links already sent in old chats keep their old preview.</p>
</div>

<p class="submit"><button type="submit" name="dsog_save" value="1" class="button button-primary">Save</button></p>
</form>
</div>
<script>
jQuery(function($){
	var frames={};
	$('.dsog-upload').on('click', function(e){
		e.preventDefault();
		var t=$(this).data('target');
		if(!frames[t]){
			frames[t]=wp.media({title:'Choose share image', button:{text:'Use this image'}, library:{type:'image'}, multiple:false});
			frames[t].on('select', function(){
				var a=frames[t].state().get('selection').first().toJSON();
				var url=(a.sizes&&a.sizes.large)?a.sizes.large.url:a.url;
				$('#'+t).val(a.id);
				$('#'+t+'_prev').html('<img src="'+url+'" alt="">');
			});
		}
		frames[t].open();
	});
	$('.dsog-remove').on('click', function(e){
		e.preventDefault();
		var t=$(this).data('target');
		$('#'+t).val('0');
		$('#'+t+'_prev').html('<span>No image chosen</span>');
	});
});
</script>
	<?php
}

} // function_exists guard
