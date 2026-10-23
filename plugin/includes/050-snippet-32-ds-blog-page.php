<?php
/**
 * Code Snippets export — Snippet 32 DS Blog Page
 *
 * Snippet ID : 736
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-050
 * Modified   : 2026-09-09 02:46:24
 * Load order : 050 of 144
 */
/**
 * Snippet 32: DS Blog Page
 * Run: Everywhere
 * NEW SNIPPET — does NOT modify any existing snippet
 *
 * Registers a [ds_blog] shortcode that renders a full blog listing page.
 * Each blog post (custom post type: ds_blog_post) can have:
 *   - A featured image
 *   - Multiple extra images (gallery)
 *   - Multiple embedded videos (YouTube / Vimeo / direct URL)
 *   - Topic / category tags
 *
 * SHORTCODES:
 *   [ds_blog]                        → Full blog listing page
 *   [ds_blog_single id="123"]        → Single post view (optional, auto-handled)
 *
 * ADMIN:
 *   → Posts → DS Blog → Add New
 *   → Supports: title, editor, featured image, custom fields (gallery, videos)
 *
 * COLORS match DesignStager brand:
 *   Orange-red: #E8481C   Dark navy: #1B2A4A   Mid navy: #243a5a
 */

if ( ! defined('ABSPATH') ) exit;

// ─── 1. Register Custom Post Type ────────────────────────────────────────────

add_action('init', function () {
    register_post_type('ds_blog_post', array(
        'label'         => 'DS Blog',
        'labels'        => array(
            'name'          => 'DS Blog Posts',
            'singular_name' => 'Blog Post',
            'add_new_item'  => 'Add New Blog Post',
            'edit_item'     => 'Edit Blog Post',
        ),
        'public'        => true,
        'show_in_menu'  => true,
        'menu_icon'     => 'dashicons-welcome-write-blog',
        'supports'      => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'has_archive'   => false,
        'rewrite'       => array('slug' => 'blog'),
    ));

    register_taxonomy('ds_blog_topic', 'ds_blog_post', array(
        'label'        => 'Topics',
        'hierarchical' => true,
        'rewrite'      => array('slug' => 'blog-topic'),
        'show_admin_column' => true,
    ));
});

// ─── 2. Meta Box: Gallery Images + Videos ───────────────────────────────────

add_action('add_meta_boxes', function () {
    add_meta_box(
        'ds_blog_media',
        '📸 Gallery Images & 🎬 Videos',
        'ds_blog_meta_box_render',
        'ds_blog_post',
        'normal',
        'high'
    );
});

if ( ! function_exists('ds_blog_meta_box_render') ) {
function ds_blog_meta_box_render( $post ) {
    wp_nonce_field('ds_blog_media_nonce', 'ds_blog_nonce');
    $images = get_post_meta($post->ID, '_ds_blog_gallery', true) ?: array();
    $videos = get_post_meta($post->ID, '_ds_blog_videos', true) ?: array();
    if ( ! is_array($images) ) $images = array();
    if ( ! is_array($videos) ) $videos = array();
    ?>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap');
    .ds-meta-section { margin-bottom: 20px; }
    .ds-meta-section h4 { margin: 0 0 8px; color: #1B2A4A; font-size: 13px; font-weight: 700; }
    .ds-meta-row { display: flex; gap: 8px; align-items: center; margin-bottom: 6px; }
    .ds-meta-row input[type=text] { flex: 1; }
    .ds-meta-row input[type=url] { flex: 1; }
    .ds-meta-btn { background: #E8481C; color: #fff; border: none; border-radius: 4px; padding: 4px 10px; cursor: pointer; font-size: 12px; }
    .ds-meta-rm { background: #dc2626; color: #fff; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 12px; }
    .ds-meta-add { background: #1B2A4A; color: #fff; border: none; border-radius: 5px; padding: 6px 14px; cursor: pointer; font-size: 12px; margin-top: 4px; }
    .ds-meta-thumb { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
    </style>
    <div class="ds-meta-section">
        <h4>📸 Extra Gallery Images (in addition to Featured Image)</h4>
        <p style="color:#555;font-size:11px;margin:0 0 8px;">Enter image URLs or use the media library. You can add unlimited images.</p>
        <div id="ds-gallery-rows">
        <?php foreach ($images as $i => $url): ?>
            <div class="ds-meta-row">
                <?php if ($url): ?><img src="<?php echo esc_url($url); ?>" class="ds-meta-thumb"><?php endif; ?>
                <input type="url" name="ds_blog_gallery[]" value="<?php echo esc_url($url); ?>" placeholder="https://..." style="flex:1;">
                <button type="button" class="ds-meta-btn ds-blog-media-btn" data-target="ds_blog_gallery[]" data-index="<?php echo $i; ?>">Choose</button>
                <button type="button" class="ds-meta-rm" onclick="this.closest('.ds-meta-row').remove()">✕</button>
            </div>
        <?php endforeach; ?>
        </div>
        <button type="button" class="ds-meta-add" id="ds-add-image">+ Add Image</button>
    </div>
    <div class="ds-meta-section">
        <h4>🎬 Videos (YouTube, Vimeo, or direct .mp4 URL)</h4>
        <p style="color:#555;font-size:11px;margin:0 0 8px;">Paste any video URL. YouTube and Vimeo links are auto-embedded. You can add multiple videos.</p>
        <div id="ds-video-rows">
        <?php foreach ($videos as $v): ?>
            <div class="ds-meta-row">
                <input type="url" name="ds_blog_videos[]" value="<?php echo esc_url($v); ?>" placeholder="https://youtube.com/watch?v=...">
                <button type="button" class="ds-meta-rm" onclick="this.closest('.ds-meta-row').remove()">✕</button>
            </div>
        <?php endforeach; ?>
        </div>
        <button type="button" class="ds-meta-add" id="ds-add-video">+ Add Video</button>
    </div>
    <script>
    document.getElementById('ds-add-image').onclick = function() {
        var row = document.createElement('div');
        row.className = 'ds-meta-row';
        row.innerHTML = '<input type="url" name="ds_blog_gallery[]" placeholder="https://..." style="flex:1;"><button type="button" class="ds-meta-btn ds-blog-media-btn">Choose</button><button type="button" class="ds-meta-rm" onclick="this.closest(\'.ds-meta-row\').remove()">✕</button>';
        document.getElementById('ds-gallery-rows').appendChild(row);
        bindMediaBtns();
    };
    document.getElementById('ds-add-video').onclick = function() {
        var row = document.createElement('div');
        row.className = 'ds-meta-row';
        row.innerHTML = '<input type="url" name="ds_blog_videos[]" placeholder="https://youtube.com/watch?v=..." style="flex:1;"><button type="button" class="ds-meta-rm" onclick="this.closest(\'.ds-meta-row\').remove()">✕</button>';
        document.getElementById('ds-video-rows').appendChild(row);
    };
    function bindMediaBtns() {
        document.querySelectorAll('.ds-blog-media-btn').forEach(function(btn) {
            if (btn._bound) return;
            btn._bound = true;
            btn.onclick = function() {
                var inp = btn.previousElementSibling && btn.previousElementSibling.tagName === 'INPUT'
                    ? btn.previousElementSibling
                    : btn.closest('.ds-meta-row').querySelector('input[type=url]');
                var frame = wp.media({ title: 'Select Image', button: { text: 'Use this image' }, multiple: false });
                frame.on('select', function() {
                    var att = frame.state().get('selection').first().toJSON();
                    inp.value = att.url;
                    // Show thumb
                    var ex = btn.closest('.ds-meta-row').querySelector('.ds-meta-thumb');
                    if (ex) ex.src = att.url;
                    else {
                        var img = document.createElement('img');
                        img.className = 'ds-meta-thumb';
                        img.src = att.url;
                        btn.closest('.ds-meta-row').insertBefore(img, inp);
                    }
                });
                frame.open();
            };
        });
    }
    bindMediaBtns();
    </script>
    <?php
}
}

// ─── 3. Save meta ────────────────────────────────────────────────────────────

add_action('save_post_ds_blog_post', function ($post_id) {
    if ( ! isset($_POST['ds_blog_nonce']) || ! wp_verify_nonce($_POST['ds_blog_nonce'], 'ds_blog_media_nonce') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

    $images = array_filter(array_map('esc_url_raw', (array)($_POST['ds_blog_gallery'] ?? array())));
    $videos = array_filter(array_map('esc_url_raw', (array)($_POST['ds_blog_videos']  ?? array())));

    update_post_meta($post_id, '_ds_blog_gallery', array_values($images));
    update_post_meta($post_id, '_ds_blog_videos',  array_values($videos));
});

// ─── 4. Helper: embed video URL → iframe/video HTML ─────────────────────────

if ( ! function_exists('ds_blog_embed_video') ) {
function ds_blog_embed_video( $url ) {
    $url = esc_url($url);
    // YouTube
    if ( preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]{11})/', $url, $m) ) {
        return '<div class="ds-blog-video-wrap"><iframe src="https://www.youtube.com/embed/' . $m[1] . '" allowfullscreen loading="lazy" frameborder="0"></iframe></div>';
    }
    // Vimeo
    if ( preg_match('/vimeo\.com\/(\d+)/', $url, $m) ) {
        return '<div class="ds-blog-video-wrap"><iframe src="https://player.vimeo.com/video/' . $m[1] . '" allowfullscreen loading="lazy" frameborder="0"></iframe></div>';
    }
    // Direct mp4
    if ( preg_match('/\.(mp4|webm|ogg)(\?|$)/i', $url) ) {
        return '<div class="ds-blog-video-wrap"><video controls preload="none"><source src="' . $url . '"></video></div>';
    }
    // Generic oEmbed fallback
    return '<div class="ds-blog-video-wrap"><a href="' . $url . '" target="_blank" rel="noopener">' . $url . '</a></div>';
}
}

// ─── 5. [ds_blog] shortcode — listing page ──────────────────────────────────

add_shortcode('ds_blog', function ($atts) {
    $atts = shortcode_atts(array(
        'posts_per_page' => 9,
        'topic'          => '',
    ), $atts);

    $topic_filter = sanitize_text_field($atts['topic']);
    $current_topic = isset($_GET['ds_topic']) ? sanitize_text_field($_GET['ds_topic']) : $topic_filter;
    $paged = max(1, intval($_GET['ds_paged'] ?? 1));

    $tax_query = array();
    if ( $current_topic ) {
        $tax_query[] = array('taxonomy' => 'ds_blog_topic', 'field' => 'slug', 'terms' => $current_topic);
    }

    $q = new WP_Query(array(
        'post_type'      => 'ds_blog_post',
        'posts_per_page' => intval($atts['posts_per_page']),
        'paged'          => $paged,
        'tax_query'      => $tax_query ?: null,
    ));

    $topics = get_terms(array('taxonomy' => 'ds_blog_topic', 'hide_empty' => true));
    $current_url = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

    ob_start();
    ?>
    <style>
    :root {
        --ds-red:    #E8481C;
        --ds-navy:   #1B2A4A;
        --ds-mid:    #243a5a;
        --ds-light:  #C8D8F0;
        --ds-text:   #1a1a2e;
        --ds-bg:     #10131c;
        --ds-card:   #ffffff;
        --ds-radius: 14px;
    }
    .ds-blog-wrap { font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--ds-bg); min-height: 60vh; padding: 0 0 60px; width: 100vw; max-width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); box-sizing: border-box; }
    body.page-id-10048, html body.page-id-10048, .site-main, .ast-container, html, body { background: #10131c !important; }
    body.page-id-10048 { overflow-x: hidden; }
    .ds-blog-hero { background: linear-gradient(135deg, #10131c 0%, #191c25 60%, #1f222b 100%); color: #fff; padding: 52px 24px 44px; text-align: center; }
    .ds-blog-hero h1 { margin: 0 0 10px; font-size: clamp(28px, 5vw, 48px); font-weight: 900; letter-spacing: -1px; color: #fff; }
    .ds-blog-hero h1 span { color: var(--ds-red); }
    .ds-blog-hero p { margin: 0; color: var(--ds-light); font-size: 15px; }
    .ds-blog-topics { display: flex; gap: 8px; flex-wrap: wrap; padding: 20px 24px 0; max-width: 1100px; margin: 0 auto; }
    .ds-blog-topic-btn { display: inline-block; padding: 6px 16px; border-radius: 999px; font-size: 12px; font-weight: 700; text-decoration: none; border: 2px solid transparent; transition: all .15s; background: #fff; color: var(--ds-navy); border-color: var(--ds-light); }
    .ds-blog-topic-btn:hover, .ds-blog-topic-btn.active { background: var(--ds-red); color: #fff; border-color: var(--ds-red); }
    .ds-blog-topic-btn.all { background: #10131c; color: #fff; border-color: #10131c; }
    .ds-blog-topic-btn.all:hover { background: var(--ds-red); border-color: var(--ds-red); }
    .ds-blog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 28px; padding: 36px 32px 0; max-width: 1200px; margin: 0 auto; }
    .ds-blog-card { background: #191c25; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(16,19,28,.18); transition: transform .18s, box-shadow .18s; display: flex; flex-direction: column; }
    .ds-blog-card:hover { transform: translateY(-5px); box-shadow: 0 12px 40px rgba(16,19,28,.35); }
    .ds-blog-card-img { position: relative; aspect-ratio: 3/2; overflow: hidden; background: #10131c; min-height: 220px; }
    .ds-blog-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .3s; }
    .ds-blog-card:hover .ds-blog-card-img img { transform: scale(1.04); }
    .ds-blog-card-badge { position: absolute; top: 10px; left: 10px; background: var(--ds-red); color: #fff; font-size: 10px; font-weight: 800; padding: 3px 9px; border-radius: 999px; letter-spacing: .5px; text-transform: uppercase; }
    .ds-blog-card-media-count { position: absolute; bottom: 10px; right: 10px; background: rgba(0,0,0,.65); color: #fff; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 6px; display: flex; gap: 6px; align-items: center; }
    .ds-blog-card-body { padding: 22px 22px 24px; flex: 1; display: flex; flex-direction: column; }
    .ds-blog-card-date { font-size: 11px; color: #C8D8F0; font-weight: 600; letter-spacing: .5px; text-transform: uppercase; margin-bottom: 8px; opacity: .7; }
    .ds-blog-card-title { font-size: 18px; font-weight: 800; color: #FFFFFF; margin: 0 0 8px; line-height: 1.35; }
    .ds-blog-card-title a { text-decoration: none; color: inherit; }
    .ds-blog-card-title a:hover { color: var(--ds-red); }
    .ds-blog-card-excerpt { font-size: 13px; color: #C8D8F0; line-height: 1.6; flex: 1; margin: 0 0 14px; opacity: .85; }
    .ds-blog-card-topics { display: flex; gap: 5px; flex-wrap: wrap; margin-bottom: 14px; }
    .ds-blog-card-topic { font-size: 10px; font-weight: 700; color: #C8D8F0; background: rgba(255,255,255,.12); padding: 2px 8px; border-radius: 999px; }
    .ds-blog-card-link { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 800; color: #E8481C; text-decoration: none; letter-spacing: .3px; }
    .ds-blog-card-link::after { content: '→'; }
    .ds-blog-card-link:hover { color: #fff; }
    .ds-blog-pagination { display: flex; gap: 8px; justify-content: center; padding: 40px 24px 0; }
    .ds-blog-pagination a, .ds-blog-pagination span { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none; border: 2px solid var(--ds-light); color: #e5e7eb; transition: all .15s; }
    .ds-blog-pagination a:hover { background: #191c25; color: #fff; border-color: #191c25; }
    .ds-blog-pagination .current { background: var(--ds-red); color: #fff; border-color: var(--ds-red); }
    .ds-blog-empty { text-align: center; padding: 60px 24px; color: #8b97af; font-size: 15px; }

    /* ── Single post styles ── */
    .ds-blog-single { max-width: 820px; margin: 0 auto; padding: 32px 24px 60px; }
    .ds-blog-single-back { display: inline-flex; align-items: center; gap: 5px; color: var(--ds-red); font-size: 13px; font-weight: 700; text-decoration: none; margin-bottom: 24px; }
    .ds-blog-single-back::before { content: '←'; }
    .ds-blog-single-back:hover { color: var(--ds-navy); }
    .ds-blog-single-hero { border-radius: var(--ds-radius); overflow: hidden; margin-bottom: 28px; aspect-ratio: 16/9; background: var(--ds-navy); }
    .ds-blog-single-hero img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ds-blog-single-title { font-size: clamp(22px, 4vw, 36px); font-weight: 900; color: var(--ds-navy); margin: 0 0 10px; line-height: 1.2; }
    .ds-blog-single-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid var(--ds-light); }
    .ds-blog-single-date { font-size: 12px; color: #8b97af; font-weight: 600; }
    .ds-blog-single-body { font-size: 16px; color: #1a1a2e; line-height: 1.75; }
    .ds-blog-single-body h2 { color: var(--ds-navy); font-weight: 800; margin: 32px 0 12px; }
    .ds-blog-single-body h3 { color: var(--ds-mid); font-weight: 700; margin: 24px 0 8px; }
    .ds-blog-single-body p { margin: 0 0 18px; }
    .ds-blog-single-body a { color: var(--ds-red); }
    .ds-blog-section-label { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 800; color: var(--ds-navy); letter-spacing: .5px; text-transform: uppercase; margin: 32px 0 14px; }
    .ds-blog-section-label::after { content: ''; flex: 1; height: 2px; background: var(--ds-light); }
    .ds-blog-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-bottom: 10px; }
    .ds-blog-gallery-item { border-radius: 10px; overflow: hidden; aspect-ratio: 4/3; background: var(--ds-navy); cursor: pointer; }
    .ds-blog-gallery-item img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .25s; }
    .ds-blog-gallery-item:hover img { transform: scale(1.06); }
    .ds-blog-video-wrap { position: relative; border-radius: 10px; overflow: hidden; aspect-ratio: 16/9; background: #000; margin-bottom: 12px; }
    .ds-blog-video-wrap iframe, .ds-blog-video-wrap video { position: absolute; inset: 0; width: 100%; height: 100%; border: none; }
    /* Lightbox */
    .ds-lightbox { position: fixed; inset: 0; background: rgba(0,0,0,.92); z-index: 999999; display: none; align-items: center; justify-content: center; }
    .ds-lightbox.open { display: flex; }
    .ds-lightbox img { max-width: 92vw; max-height: 90vh; border-radius: 10px; object-fit: contain; }
    .ds-lightbox-close { position: absolute; top: 18px; right: 22px; color: #fff; font-size: 28px; cursor: pointer; background: none; border: none; font-weight: 900; line-height: 1; }
    .ds-lightbox-prev, .ds-lightbox-next { position: absolute; top: 50%; transform: translateY(-50%); color: #fff; font-size: 36px; cursor: pointer; background: rgba(255,255,255,.12); border: none; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; }
    .ds-lightbox-prev { left: 12px; }
    .ds-lightbox-next { right: 12px; }
    @media(max-width:640px) {
        .ds-blog-grid { grid-template-columns: 1fr; padding: 20px 14px 0; }
        .ds-blog-gallery { grid-template-columns: repeat(2, 1fr); }
    }
    </style>

    <?php
    // Redirect single post to its real WordPress URL
$single_id = intval($_GET['ds_post_id'] ?? 0);
if ( $single_id ) {
    $sp = get_post($single_id);
    if ( $sp && $sp->post_type === 'ds_blog_post' && $sp->post_status === 'publish' ) {
        wp_redirect( get_permalink($single_id), 301 );
        exit;
    }
}
    ?>

    <div class="ds-blog-wrap">
        <div class="ds-blog-hero">
            <h1><span style="color:#fff;">Our</span> <span style="color:#E8481C;">Blog</span></h1>
            <p>Insights, tutorials, and design inspiration — updated regularly.</p>
        </div>

        <!-- Topic filter pills -->
        <?php if ( ! is_wp_error($topics) && ! empty($topics) ) : ?>
        <div class="ds-blog-topics">
            <a href="<?php echo esc_url($current_url); ?>" class="ds-blog-topic-btn all <?php echo !$current_topic ? 'active' : ''; ?>">All</a>
            <?php foreach ($topics as $t): ?>
            <a href="<?php echo esc_url(add_query_arg('ds_topic', $t->slug, $current_url)); ?>"
               class="ds-blog-topic-btn <?php echo $current_topic === $t->slug ? 'active' : ''; ?>">
                <?php echo esc_html($t->name); ?> <span style="opacity:.6">(<?php echo $t->count; ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Post grid -->
        <?php if ( $q->have_posts() ) : ?>
        <div class="ds-blog-grid">
        <?php while ( $q->have_posts() ) : $q->the_post();
            $pid    = get_the_ID();
            $thumb  = get_the_post_thumbnail_url($pid, 'large');
            $extra  = get_post_meta($pid, '_ds_blog_gallery', true) ?: array();
            $videos = get_post_meta($pid, '_ds_blog_videos', true) ?: array();
            $ptopics = get_the_terms($pid, 'ds_blog_topic');
            $post_url = get_permalink($pid);
        ?>
        <article class="ds-blog-card">
            <div class="ds-blog-card-img">
                <?php if ($thumb): ?>
                    <img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                <?php else: ?>
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#191c25,#10131c);font-size:50px;">✍️</div>
                <?php endif; ?>
                <?php if ($ptopics && ! is_wp_error($ptopics)): ?>
                    <span class="ds-blog-card-badge"><?php echo esc_html($ptopics[0]->name); ?></span>
                <?php endif; ?>
                <?php if ($extra || $videos): ?>
                    <div class="ds-blog-card-media-count">
                        <?php if ($extra): ?>📸 <?php echo count($extra); ?><?php endif; ?>
                        <?php if ($videos): ?> 🎬 <?php echo count($videos); ?><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="ds-blog-card-body">
                <div class="ds-blog-card-date"><?php echo get_the_date('M j, Y'); ?></div>
                <h2 class="ds-blog-card-title"><a href="<?php echo esc_url($post_url); ?>"><?php the_title(); ?></a></h2>
                <p class="ds-blog-card-excerpt"><?php echo wp_trim_words(get_the_excerpt() ?: get_the_content(), 20, '…'); ?></p>
                <?php if ($ptopics && !is_wp_error($ptopics)): ?>
                <div class="ds-blog-card-topics">
                    <?php foreach ($ptopics as $pt): ?>
                    <span class="ds-blog-card-topic"><?php echo esc_html($pt->name); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <a href="<?php echo esc_url($post_url); ?>" class="ds-blog-card-link">Read more</a>
            </div>
        </article>
        <?php endwhile; wp_reset_postdata(); ?>
        </div>

        <!-- Pagination -->
        <?php if ( $q->max_num_pages > 1 ) : ?>
        <div class="ds-blog-pagination">
            <?php for ($i = 1; $i <= $q->max_num_pages; $i++): ?>
                <?php if ($i == $paged): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="<?php echo esc_url(add_query_arg(array('ds_paged' => $i, 'ds_topic' => $current_topic ?: null), $current_url)); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="ds-blog-empty">
            <p style="font-size:40px;margin:0 0 12px">✍️</p>
            <p>No blog posts found<?php echo $current_topic ? ' in this topic' : ''; ?>.</p>
            <?php if ($current_topic): ?><a href="<?php echo esc_url($current_url); ?>" class="ds-blog-card-link" style="justify-content:center">← View all posts</a><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Lightbox -->
    <div class="ds-lightbox" id="ds-lightbox">
        <button class="ds-lightbox-close" onclick="document.getElementById('ds-lightbox').classList.remove('open')">✕</button>
        <button class="ds-lightbox-prev" id="ds-lb-prev">‹</button>
        <img id="ds-lb-img" src="" alt="">
        <button class="ds-lightbox-next" id="ds-lb-next">›</button>
    </div>
    <script>
    (function(){
    var _imgs = [], _idx = 0;
    window._dsLbOpen = function(imgs, i) {
        _imgs = imgs; _idx = i;
        document.getElementById('ds-lb-img').src = _imgs[_idx];
        document.getElementById('ds-lightbox').classList.add('open');
    };
    document.getElementById('ds-lb-prev').onclick = function() { _idx = (_idx - 1 + _imgs.length) % _imgs.length; document.getElementById('ds-lb-img').src = _imgs[_idx]; };
    document.getElementById('ds-lb-next').onclick = function() { _idx = (_idx + 1) % _imgs.length; document.getElementById('ds-lb-img').src = _imgs[_idx]; };
    document.addEventListener('keydown', function(e) {
        var lb = document.getElementById('ds-lightbox');
        if (!lb.classList.contains('open')) return;
        if (e.key === 'Escape') lb.classList.remove('open');
        if (e.key === 'ArrowLeft') document.getElementById('ds-lb-prev').click();
        if (e.key === 'ArrowRight') document.getElementById('ds-lb-next').click();
    });
    document.getElementById('ds-lightbox').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
    })();
    </script>

    <?php
    return ob_get_clean();
});

// ─── 6. Single post renderer ─────────────────────────────────────────────────

if ( ! function_exists('ds_blog_render_single') ) {
function ds_blog_render_single( $post ) {
    $pid    = $post->ID;
    $thumb  = get_the_post_thumbnail_url($pid, 'full');
    $extra  = get_post_meta($pid, '_ds_blog_gallery', true) ?: array();
    $videos = get_post_meta($pid, '_ds_blog_videos', true) ?: array();
    $topics = get_the_terms($pid, 'ds_blog_topic');
    $back   = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
    $all_imgs = array_filter(array_merge($thumb ? array($thumb) : array(), $extra));

    ob_start(); ?>
    <div class="ds-blog-wrap">
    <div class="ds-blog-single">
        <a href="<?php echo esc_url($back); ?>" class="ds-blog-single-back">Back to Blog</a>

        <?php if ($thumb): ?>
        <div class="ds-blog-single-hero"><img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($post->post_title); ?>"></div>
        <?php endif; ?>

        <h1 class="ds-blog-single-title"><?php echo esc_html($post->post_title); ?></h1>

        <div class="ds-blog-single-meta">
            <span class="ds-blog-single-date"><?php echo get_the_date('F j, Y', $post); ?></span>
            <?php if ($topics && !is_wp_error($topics)): ?>
                <?php foreach ($topics as $t): ?>
                    <span class="ds-blog-card-topic"><?php echo esc_html($t->name); ?></span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="ds-blog-single-body">
            <?php echo apply_filters('the_content', $post->post_content); ?>
        </div>

        <!-- Gallery -->
        <?php if ( ! empty($extra) ) : ?>
        <div class="ds-blog-section-label">📸 Gallery</div>
        <div class="ds-blog-gallery">
            <?php foreach ($extra as $gi => $img_url): ?>
            <div class="ds-blog-gallery-item" onclick="window._dsLbOpen(<?php echo json_encode(array_values($all_imgs)); ?>, <?php echo $thumb ? $gi + 1 : $gi; ?>)">
                <img src="<?php echo esc_url($img_url); ?>" alt="Gallery image <?php echo $gi + 1; ?>" loading="lazy">
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Videos -->
        <?php if ( ! empty($videos) ) : ?>
        <div class="ds-blog-section-label">🎬 Videos</div>
        <?php foreach ($videos as $v): ?>
            <?php echo ds_blog_embed_video($v); ?>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    </div>
    <?php
    return ob_get_clean();
}
}
