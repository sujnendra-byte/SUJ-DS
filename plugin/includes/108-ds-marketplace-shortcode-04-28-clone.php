<?php
/**
 * Code Snippets export — ds-marketplace-shortcode 04/28 [CLONE]
 *
 * Snippet ID : 789
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-108
 * Modified   : 2026-09-18 05:20:37
 * Load order : 108 of 147
 */
/**
 * Designstager - DS Marketplace
 * Snippet Name: DS Marketplace
 * Run: Everywhere
 * Shortcode: [ds_marketplace]
 */

// OG meta tags for product sharing (only on marketplace page with ?product=ID)
add_action('wp_head', function(){
    if (!isset($_GET['product']) || !is_numeric($_GET['product'])) return;
    $pid  = (int)$_GET['product'];
    $post = get_post($pid);
    if (!$post || $post->post_type !== 'dlm_download') return;
    $thumb = '';
    $og_thumb_id = get_post_thumbnail_id($pid);
    if ($og_thumb_id && function_exists('ds_mkt_classify_media')) {
        $og_m = ds_mkt_classify_media($og_thumb_id);
        if ($og_m && $og_m['type'] === 'image') $thumb = $og_m['preview'];
    }
    if (!$thumb) $thumb = get_the_post_thumbnail_url($pid, 'large') ?: get_the_post_thumbnail_url($pid, 'full');
    $title = esc_attr($post->post_title);
    $url   = esc_url(home_url('/marketplace/?product=' . $pid));
    if (!$thumb) {
        $att = get_posts(array('post_type'=>'attachment','post_mime_type'=>'image','post_parent'=>$pid,'posts_per_page'=>1));
        if ($att) $thumb = wp_get_attachment_url($att[0]->ID);
    }
    if ($thumb) {
        echo '<meta property="og:type" content="website"/>' . "\n";
        echo '<meta property="og:title" content="' . $title . ' - Designstager Marketplace"/>' . "\n";
        echo '<meta property="og:description" content="Free and Pro design templates on Designstager Marketplace"/>' . "\n";
        echo '<meta property="og:image" content="' . esc_url($thumb) . '"/>' . "\n";
        echo '<meta property="og:url" content="' . $url . '"/>' . "\n";
        echo '<meta name="twitter:card" content="summary_large_image"/>' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url($thumb) . '"/>' . "\n";
    }
}, 1);

if (!function_exists('ds_render_cat_tree')) {
function ds_render_cat_tree($cats, $parent_id, $current_cat, $depth = 0, $type = 'visual') {
    $children = array_filter($cats, function($c) use ($parent_id) { return $c->parent == $parent_id; });
    if (empty($children)) return '';
    $out = '';
    foreach ($children as $cat) {
        $has_children = !empty(array_filter($cats, function($c) use ($cat) { return $c->parent == $cat->term_id; }));
        if ($cat->count == 0 && !$has_children) continue;
        $is_active = ($current_cat === $cat->slug);
        $px   = $depth * 14;
        $fsz  = $depth === 0 ? '13' : ($depth === 1 ? '12' : '11');
        $col  = $depth === 0 ? '#e2e8f0' : ($depth === 1 ? '#cbd5e1' : '#94a3b8');
        $out .= '<li>';
        $out .= '<a href="?type=' . esc_attr($type) . '&cat=' . esc_attr($cat->slug) . '" style="padding-left:' . (10 + $px) . 'px;" class="' . ($is_active ? 'active' : '') . '">';
        $out .= '<span style="font-size:' . $fsz . 'px;color:' . $col . ';">' . esc_html($cat->name) . '</span>';
        $out .= '</a>';
        $sub = ds_render_cat_tree($cats, $cat->term_id, $current_cat, $depth + 1, $type);
        if ($sub) $out .= '<ul style="list-style:none;padding:0;margin:0;">' . $sub . '</ul>';
        $out .= '</li>';
    }
    return $out;
}
}

if (!function_exists('ds_marketplace_get_user_tier')) {
function ds_marketplace_get_user_tier() {
    if (!is_user_logged_in()) return 'guest';
    $uid   = get_current_user_id();
    $roles = (array)wp_get_current_user()->roles;
    $rs    = implode(',', $roles);
    if (in_array('administrator', $roles) || in_array('editor', $roles)) return 'business';
    $plan = '';
    if (function_exists('ds_access_plan')) $plan = ds_access_plan($uid);            /* live plan, or the last paid plan while credits last */
    elseif (function_exists('ds_get_user_plan')) $plan = ds_get_user_plan($uid);
    if (!$plan) $plan = strtolower(get_user_meta($uid, 'ds_subscription_plan', true) ?: '');
    if ($plan === 'business' || in_array('business_member', $roles) || strpos($rs, 'business') !== false) return 'business';
    if ($plan === 'pro'      || in_array('pro_member', $roles)      || strpos($rs, '_pro') !== false)    return 'pro';
    if ($plan === 'premium'  || in_array('premium_member', $roles)  || strpos($rs, 'premium') !== false) return 'premium';
    return 'free';
}
}

add_shortcode('ds_marketplace', function($atts) {
    $atts        = shortcode_atts(array('number' => -1), $atts);
    $current_cat = isset($_GET['cat']) ? sanitize_text_field($_GET['cat']) : '';
    $current_type = (isset($_GET['type']) && sanitize_key($_GET['type']) === 'audio') ? 'audio' : 'visual';
    $user_tier   = ds_marketplace_get_user_tier();
    $dl_nonce    = wp_create_nonce('wp_rest');
    $rest_url    = trailingslashit(rest_url('ds/v1'));
    $site_url    = home_url();
    $pricing_url = home_url('/pricing');
    $login_url   = home_url('/login');
    $logged_in   = is_user_logged_in();

    $dl_limits = array(
        'guest'    => array('period' => 'day',   'free' => 0,    'pro' => 0),
        'free'     => array('period' => 'day',   'free' => 5,    'pro' => 0),
        'premium'  => array('period' => 'month', 'free' => 500,  'pro' => 50),
        'pro'      => array('period' => 'month', 'free' => 1000, 'pro' => 200),
        'business' => array('period' => 'month', 'free' => -1,   'pro' => 600),
    );
    $user_limits = isset($dl_limits[$user_tier]) ? $dl_limits[$user_tier] : $dl_limits['free'];
    $can_pro     = ($user_limits['pro'] !== 0);

    /*
     * 16.1 — was a hard-coded 100 (audio) / 200 (everything else). Rendering 200
     * product cards server-side is what produced ~1.1 MB of HTML per request.
     * The prev/next pagination below already existed and handles the rest, so
     * this only changes how many cards appear per page. Tune without editing
     * code via:  add_filter( 'ds_marketplace_per_page', fn() => 48 );
     */
    $dl_per_page = (int) apply_filters(
        'ds_marketplace_per_page',
        ( $current_type === 'audio' ) ? 60 : 60,
        $current_type
    );
    if ( $dl_per_page < 1 ) { $dl_per_page = 60; }
    $dl_page     = isset($_GET['dlpage']) ? max(1, intval($_GET['dlpage'])) : 1;
	$dl_sort  = isset($_GET['sort']) ? sanitize_key($_GET['sort']) : 'latest';
    $sort_map = array(
        'latest' => array('date',  'DESC'),
        'oldest' => array('date',  'ASC'),
        'name'   => array('title', 'ASC'),
        'name_z' => array('title', 'DESC'),
    );
    if (!isset($sort_map[$dl_sort])) $dl_sort = 'latest';
	$dl_q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
	$dl_scope     = isset($_GET['scope']) ? sanitize_key($_GET['scope']) : 'db';
	if (!in_array($dl_scope, array('db','cat','page'), true)) $dl_scope = 'db';
$dl_q_norm = trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $dl_q));
$dl_is_fallback = false;

    $args = array(
    'post_type'      => 'dlm_download',
    'posts_per_page' => $dl_per_page,
    'paged'          => $dl_page,
    'post_status'    => 'publish',
    'orderby'        => $sort_map[$dl_sort][0],
    'order'          => $sort_map[$dl_sort][1],
);

    // Type-aware scoping when no specific category is selected: Audio tab only
    // shows the Audio tree; Visual tab excludes it so audio products don't
    // leak into the image/video grid.
    $type_tax_query = ($current_type === 'audio')
        ? array('taxonomy' => 'dlm_download_category', 'field' => 'slug', 'terms' => array('audio'), 'include_children' => true)
        : array('taxonomy' => 'dlm_download_category', 'field' => 'slug', 'terms' => array('audio'), 'operator' => 'NOT IN', 'include_children' => true);

    if ($dl_q !== '') {
        // A search spans the whole marketplace and ignores the selected category.
        $ids_text = get_posts(array(
            'post_type' => 'dlm_download', 'post_status' => 'publish',
            'posts_per_page' => -1, 'fields' => 'ids', 'suppress_filters' => true,
            's' => ($dl_q_norm !== '' ? $dl_q_norm : $dl_q),
        ));
        $tag_ids = get_terms(array(
            'taxonomy' => 'dlm_download_tag', 'hide_empty' => false,
            'fields' => 'ids', 'name__like' => $dl_q,
        ));
        $ids_tag = array();
        if ($tag_ids && !is_wp_error($tag_ids)) {
            $ids_tag = get_posts(array(
                'post_type' => 'dlm_download', 'post_status' => 'publish',
                'posts_per_page' => -1, 'fields' => 'ids', 'suppress_filters' => true,
                'tax_query' => array(array(
                    'taxonomy' => 'dlm_download_tag', 'field' => 'term_id', 'terms' => $tag_ids,
                )),
            ));
        }
        $found = array_values(array_unique(array_merge((array) $ids_text, (array) $ids_tag)));
if (empty($found)) {
    $dl_words = array_filter(preg_split('/[^\p{L}\p{N}]+/u', $dl_q), function($w){ return mb_strlen($w) >= 2; });
    $dl_words = array_slice(array_values(array_unique($dl_words)), 0, 6);
    if ($dl_words) {
        $ids_related = array();
        foreach ($dl_words as $w) {
            $wp_ids = get_posts(array(
                'post_type' => 'dlm_download', 'post_status' => 'publish',
                'posts_per_page' => -1, 'fields' => 'ids', 'suppress_filters' => true,
                's' => $w,
            ));
            $ids_related = array_merge($ids_related, (array) $wp_ids);
            $wt_ids = get_terms(array(
                'taxonomy' => 'dlm_download_tag', 'hide_empty' => false,
                'fields' => 'ids', 'name__like' => $w,
            ));
            if ($wt_ids && !is_wp_error($wt_ids)) {
                $wt_posts = get_posts(array(
                    'post_type' => 'dlm_download', 'post_status' => 'publish',
                    'posts_per_page' => -1, 'fields' => 'ids', 'suppress_filters' => true,
                    'tax_query' => array(array(
                        'taxonomy' => 'dlm_download_tag', 'field' => 'term_id', 'terms' => $wt_ids,
                    )),
                ));
                $ids_related = array_merge($ids_related, (array) $wt_posts);
            }
        }
        $found = array_values(array_unique($ids_related));
        $dl_is_fallback = !empty($found);
    }
}
        $args['post__in'] = $found ? $found : array(0);
        if (!empty($current_cat)) {
            $args['tax_query'] = array(array('taxonomy'=>'dlm_download_category','field'=>'slug','terms'=>$current_cat));
        } elseif ($type_tax_query) {
            $args['tax_query'] = array($type_tax_query);
        }
    } elseif (!empty($current_cat)) {
        $args['tax_query'] = array(array('taxonomy'=>'dlm_download_category','field'=>'slug','terms'=>$current_cat));
    } elseif ($type_tax_query) {
        $args['tax_query'] = array($type_tax_query);
    }
    $dl_query       = new WP_Query($args);
	if (current_user_can('manage_options') && isset($_GET['dbg'])) {
        echo '<pre style="background:#fff;color:#000;padding:12px;font:12px monospace;white-space:pre-wrap;">'
           . esc_html(print_r(array(
               'GET_sort' => isset($_GET['sort']) ? $_GET['sort'] : '(none)',
               'dl_sort'  => $dl_sort,
               'orderby'  => $args['orderby'],
               'order'    => $args['order'],
               'sql'      => $dl_query->request,
             ), true))
           . '</pre>';
    }
    $downloads      = $dl_query->posts;
    $dl_total_pages = max(1, $dl_query->max_num_pages);

    $dl_prev_args = array('dlpage' => $dl_page - 1);
    $dl_next_args = array('dlpage' => $dl_page + 1);
    if (!empty($current_cat)) {
        $dl_prev_args['cat'] = $current_cat;
        $dl_next_args['cat'] = $current_cat;
    }
	if ($dl_sort !== 'latest') {
        $dl_prev_args['sort'] = $dl_sort;
        $dl_next_args['sort'] = $dl_sort;
    }
	if ($dl_q !== '') {
        $dl_prev_args['q'] = $dl_q;
        $dl_next_args['q'] = $dl_q;
    }
    $dl_prev_url = esc_url(add_query_arg($dl_prev_args));
    $dl_next_url = esc_url(add_query_arg($dl_next_args));
    $all_cats  = get_terms(array('taxonomy'=>'dlm_download_category','hide_empty'=>false,'orderby'=>'name','order'=>'ASC'));
    if ( is_wp_error( $all_cats ) || ! is_array( $all_cats ) ) { $all_cats = array(); }

    // Build product data
    $products = array();
    foreach ($downloads as $dl) {
        $tags       = get_the_terms($dl->ID, 'dlm_download_tag') ?: array();
        $tag_slugs  = array_map(function($t){ return strtolower($t->slug); }, $tags);
        $cats_terms = get_the_terms($dl->ID, 'dlm_download_category');
        $cat_name   = $cats_terms ? $cats_terms[0]->name : '';

        if (in_array('pro', $tag_slugs)) {
            $tier='pro'; $tier_label='Pro'; $tier_cls='ds-type-pro'; $badge_cls='pro-badge';
        } else {
            $tier='free'; $tier_label='Free'; $tier_cls='ds-type-free'; $badge_cls='free-badge';
        }

        $can_dl = $logged_in && ($tier === 'free' || $can_pro);
        $dl_url = '#';
        if (function_exists('download_monitor') && method_exists(download_monitor(), 'service')) {
            $obj = download_monitor()->service('download_repository')->retrieve_single($dl->ID);
            if ($obj) $dl_url = $obj->get_the_download_link();
        }
        if (!$dl_url || $dl_url === '#') $dl_url = get_permalink($dl->ID);

        $action_url = $dl_url;
        if (!$logged_in) $action_url = $login_url . '?redirect_to=' . urlencode(get_permalink($dl->ID));
        elseif (!$can_dl) $action_url = $pricing_url;

        // File format
        $file_format = 'PowerPoint / PDF';
        $is_video = false;
        $is_audio = false;
        $audio_preview_url = '';
        $download_resolution_url = '';
        if (function_exists('download_monitor') && method_exists(download_monitor(), 'service')) {
            $obj2 = download_monitor()->service('download_repository')->retrieve_single($dl->ID);
            if ($obj2) {
                $ver = $obj2->get_version();
                if ($ver) {
                    $fp  = $ver->get_filename() ?: $ver->get_url();
                    $ext = strtolower(pathinfo(parse_url($fp, PHP_URL_PATH), PATHINFO_EXTENSION));
                    $fmap = array('pptx'=>'PowerPoint (PPTX)','ppt'=>'PowerPoint (PPT)','pdf'=>'PDF','mp4'=>'Video (MP4)',
                                  'png'=>'Image (PNG)','jpg'=>'Image (JPG)','jpeg'=>'Image (JPG)','svg'=>'Vector (SVG)',
                                  'zip'=>'ZIP Archive','psd'=>'Photoshop (PSD)','ai'=>'Adobe Illustrator','docx'=>'Word (DOCX)',
                                  'mp3'=>'Audio (MP3)','wav'=>'Audio (WAV)','ogg'=>'Audio (OGG)','m4a'=>'Audio (M4A)','flac'=>'Audio (FLAC)');
                    if (isset($fmap[$ext])) $file_format = $fmap[$ext];
                    $is_video = in_array($ext, array('mp4','mov','avi','webm'));
                    $is_audio = in_array($ext, array('mp3','wav','ogg','m4a','flac','aac'));
                    if (in_array($ext, array('png','jpg','jpeg','webp'))) {
                        $download_resolution_url = $ver->get_url();
                    }
                    if ($is_audio) {
                        $audio_preview_url = $ver->get_url();
                    }
                }
            }
        }

        $thumb_id = get_post_thumbnail_id($dl->ID);
        $media = array();
        $media_ids = array();
        if ($thumb_id) {
            $m = ds_mkt_classify_media($thumb_id);
            if ($m) { $media[] = $m; $media_ids[] = $thumb_id; }
        }
        $attached = get_posts(array(
            'post_type'      => 'attachment',
            'post_mime_type' => array('image/jpeg','image/png','image/webp','image/gif','video/mp4','video/webm','video/quicktime'),
            'post_parent'    => $dl->ID,
            'posts_per_page' => 10,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ));
        foreach ($attached as $att) {
            if ($thumb_id && (int)$att->ID === (int)$thumb_id) continue;
            $m = ds_mkt_classify_media($att->ID);
            if ($m) { $media[] = $m; $media_ids[] = $att->ID; }
        }

        $designer = get_post_meta($dl->ID, 'designer_name', true)
            ?: get_post_meta($dl->ID, 'designer', true)
            ?: get_post_meta($dl->ID, 'designed_by', true)
            ?: '';
        if (!$designer) {
            $ds_mkt_creator_id = (int) get_post_meta($dl->ID, 'ds_creator_id', true);
            if (!$ds_mkt_creator_id) $ds_mkt_creator_id = (int) $dl->post_author;
            if ($ds_mkt_creator_id) {
                $ds_mkt_creator_user = get_user_by('id', $ds_mkt_creator_id);
                if ($ds_mkt_creator_user) $designer = $ds_mkt_creator_user->display_name;
            }
        }

        $desc = '';
        if (!empty($dl->post_excerpt)) $desc = wp_trim_words(strip_tags($dl->post_excerpt), 25, '...');
        if (!$desc && !empty($dl->post_content)) $desc = wp_trim_words(strip_tags($dl->post_content), 25, '...');
        if (!$desc) $desc = 'A professional design template.';

        $share_thumb = (!empty($media[0]) && $media[0]['type'] !== 'video') ? $media[0]['preview'] : '';

        $ds_mkt_resolution = '';
        if ($download_resolution_url && function_exists('ds_mkt_get_resolution_label_from_url')) {
            $ds_mkt_resolution = ds_mkt_get_resolution_label_from_url($download_resolution_url);
        }
        if (!$ds_mkt_resolution && function_exists('ds_mkt_get_resolution_label')) {
            $ds_mkt_resolution = ds_mkt_get_resolution_label(!empty($media_ids[0]) ? $media_ids[0] : $thumb_id);
        }

        $products[] = array(
            'id'         => $dl->ID,
            'title'      => $dl->post_title,
            'cat'        => $cat_name,
            'tier'       => $tier,
            'tier_label' => $tier_label,
            'tier_cls'   => $tier_cls,
            'badge_cls'  => $badge_cls,
            'can_dl'     => $can_dl,
            'action_url' => $action_url,
			'tags'      => implode( ' ', $tag_slugs ),
            'dl_url'     => $dl_url,
            'designer'   => $designer,
            'desc'       => $desc,
            'format'     => $file_format,
            'fmt_tag'    => $is_video ? 'VID' : ( $is_audio ? 'AUD' : ( preg_match('/PowerPoint/i', $file_format) ? 'PPT' : ( preg_match('/PDF/i', $file_format) ? 'PDF' : ( preg_match('/Image|Vector|Photoshop|Illustrator/i', $file_format) ? 'IMG' : '' ) ) ) ),
            'is_video'   => $is_video,
            'is_audio'   => $is_audio,
            'audio_url'  => $audio_preview_url,
            'media'      => array_values($media),
            'thumb_type' => !empty($media[0]['type']) ? $media[0]['type'] : '',
            'thumb_grid' => !empty($media[0]['grid']) ? $media[0]['grid'] : '',
            'thumb_poster' => ( !empty($media_ids[0]) && !empty($media[0]['type']) && $media[0]['type'] === 'video' && function_exists('ds_mkt_poster_url') ) ? ds_mkt_poster_url($media_ids[0]) : '',
            'resolution' => function_exists('ds_mkt_quality_label') ? ds_mkt_quality_label($ds_mkt_resolution) : $ds_mkt_resolution,
            'share_thumb'=> $share_thumb,
            'permalink'  => get_permalink($dl->ID),
        );
    }

    ob_start();
    ?>
<style>
:root{--ds-mkt-header-h:90px;}
.ds-mkt{display:flex;gap:0;background:#080a10;height:calc(100vh - var(--ds-mkt-header-h));overflow:hidden;}
body.page-id-4041 .site-main{background:#080a10 !important;}
body.page-id-4041 .entry-content{background:#080a10 !important;}
.ds-mkt-sb{width:220px;flex-shrink:0;background:#10131c;border-right:1px solid #1e2a40;padding:16px 10px;height:100%;overflow-y:auto;box-sizing:border-box;}
.ds-mkt-sb h3{font-size:12px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;padding:0 6px 10px;border-bottom:1px solid #1e2a40;margin:0 0 8px;}
.ds-mkt-sb ul{list-style:none;padding:0;margin:0;}
.ds-mkt-sb a{display:flex;align-items:center;justify-content:space-between;padding:7px 10px;border-radius:8px;font-size:13px;color:#cbd5e1;text-decoration:none;transition:all .15s;margin:1px 0;}
.ds-mkt-sb a:hover,.ds-mkt-sb a.active{background:#191c25;color:#60a5fa;}
.ds-mkt-count{background:#191c25;color:#60a5fa;font-size:10px;padding:1px 6px;border-radius:10px;}
.ds-mkt-main{flex:1;padding:16px;display:flex;flex-direction:column;height:100%;overflow:hidden;box-sizing:border-box;}
.ds-mkt-toolbar{display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap;flex-shrink:0;}
.ds-mkt-search{display:flex;align-items:center;gap:8px;background:#191c25;border:1px solid #1f222b;border-radius:4px;padding:0 12px;height:36px;box-sizing:border-box;flex:1;max-width:360px;}
.ds-mkt-sort{background:#191c25;border:1px solid #1f222b;border-radius:4px;color:#cbd5e1;font-size:13px;font-weight:500;padding:0 10px;height:36px;box-sizing:border-box;outline:none;cursor:pointer;font-family:inherit;}
.ds-mkt-sort:focus{border-color:#4a6080;}
.ds-mkt-search svg{flex-shrink:0;}
.ds-mkt-search input{border:none;outline:none;font-size:13px;color:#f1f5f9;width:100%;background:transparent;font-weight:500;}
.ds-mkt-search input::placeholder{color:#4a6080;}
.ds-mkt-search input:focus,
.ds-mkt-search input:focus-visible{background:transparent !important;color:#f1f5f9 !important;
  border:none !important;outline:none !important;box-shadow:none !important;}
.ds-mkt-search input:-webkit-autofill,
.ds-mkt-search input:-webkit-autofill:hover,
.ds-mkt-search input:-webkit-autofill:focus{
  -webkit-text-fill-color:#f1f5f9 !important;
  -webkit-box-shadow:0 0 0 1000px #10131c inset !important;
  transition:background-color 9999s ease-in-out 0s;}
.ds-mkt-scroll{flex:1;overflow-y:auto;padding-right:6px;}
.ds-mkt-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;}
.dsaaCard{grid-column:1/-1;background:#0a0a0a;border:1px solid #1f222b;border-radius:12px;padding:14px}
.dsaaCardRow{display:flex;gap:12px;align-items:flex-start}
.dsaaCardThumb{width:88px!important;height:88px!important;min-width:88px!important;min-height:88px!important;max-width:88px!important;max-height:88px!important;border-radius:50%!important;object-fit:cover!important;flex:none;background:#161616;border:1px solid #1f222b;display:block!important;margin:0!important;padding:0!important;animation:dsaaDiscSpin 9s linear infinite;animation-play-state:paused}
.dsaaCardThumb.dsaaSpinning{animation-play-state:running}
@keyframes dsaaDiscSpin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
.dsaaThumbWrap{position:relative;flex:none;width:88px;height:88px}
.dsaaThumbPlay{padding:0!important;box-sizing:border-box!important;margin:0!important;font-size:0!important;position:absolute!important;top:50%!important;left:50%!important;transform:translate(-50%,-50%)!important;width:38px!important;height:38px!important;min-width:38px!important;min-height:38px!important;max-width:38px!important;max-height:38px!important;border-radius:50%!important;background:rgba(0,0,0,.7)!important;border:2px solid #fff!important;color:#fff!important;display:flex!important;align-items:center!important;justify-content:center!important;cursor:pointer;line-height:1!important;box-shadow:none!important}
.dsaaThumbPlay svg{margin-left:1px}
.dsaaCardBody{flex:1;min-width:0}
.dsaaCardTop{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px}
.dsaaCardMeta{min-width:0;display:flex;align-items:center;gap:8px}
.dsaaCardKind{display:inline-flex;align-items:center;gap:5px;font-size:10.5px;font-weight:800;letter-spacing:.03em;text-transform:uppercase;color:#2563eb;background:rgba(37,99,235,.12);padding:3px 8px;border-radius:999px;margin:0;flex:none}
.dsaaCardLabel{font-size:13.5px;font-weight:700;color:#e5e7eb;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:420px;min-width:0}
.dsaaCardDate{font-size:11px;color:#6b7280;text-align:right;margin-top:6px}
.dsaaBadge{font-size:10px;font-weight:700;padding:3px 8px;border-radius:999px;white-space:nowrap}
.dsaaBadge.temp{background:rgba(234,179,8,.15);color:#eab308}
.dsaaBadge.saved{background:rgba(34,197,94,.15);color:#22c55e}
.dsaaCardActions{display:flex;gap:6px;flex:none}
.dsaaIconBtn{width:30px!important;height:30px!important;min-width:30px!important;min-height:30px!important;max-width:30px!important;max-height:30px!important;padding:0!important;border-radius:8px!important;background:#161616!important;border:1px solid #1f222b!important;color:#94a3b8!important;display:flex!important;align-items:center!important;justify-content:center!important;cursor:pointer;box-shadow:none!important;margin:0!important}
.dsaaIconBtn svg{width:15px!important;height:15px!important;min-width:15px!important;min-height:15px!important;flex:none!important;display:block!important}
.dsaaIconBtn:hover{color:#e5e7eb;border-color:#2563eb}
.dsaaVolWrap{display:flex;align-items:center;gap:5px;flex:none}
.dsaaVolIcon{color:#94a3b8;display:flex;flex:none;cursor:pointer}
.dsaaVolIcon:hover{color:#e5e7eb}
.dsaaVolIcon svg{width:14px!important;height:14px!important;min-width:14px!important;min-height:14px!important;flex:none!important;display:block!important}
.dsaaVolSlider{-webkit-appearance:none!important;appearance:none!important;width:54px!important;height:4px!important;background:#2a2e38!important;border-radius:999px!important;outline:none!important;border:none!important;box-shadow:none!important;cursor:pointer}
.dsaaVolSlider::-webkit-slider-runnable-track{height:4px!important;background:#2a2e38!important;border-radius:999px!important;box-shadow:none!important;border:none!important}
.dsaaVolSlider::-webkit-slider-thumb{-webkit-appearance:none!important;appearance:none!important;width:11px!important;height:11px!important;border-radius:50%!important;background:#e5e7eb!important;cursor:pointer;margin-top:-3.5px!important;border:none!important;box-shadow:none!important}
.dsaaVolSlider::-moz-range-track{height:4px!important;background:#2a2e38!important;border-radius:999px!important;box-shadow:none!important;border:none!important}
.dsaaVolSlider::-moz-range-thumb{width:11px!important;height:11px!important;border-radius:50%!important;background:#e5e7eb!important;border:none!important;cursor:pointer;box-shadow:none!important}
.dsaaPlayer{display:flex;align-items:center;gap:10px}
.dsaaWave{flex:1;display:flex;align-items:center;justify-content:space-between;height:34px;cursor:pointer;overflow:visible;position:relative}
.dsaaWaveCursor{position:absolute;top:-7px;bottom:-7px;width:4px;border-radius:2px;background:#fff;pointer-events:none;left:0%;z-index:2;box-shadow:0 0 6px rgba(255,255,255,.8)}
.dsaaWave b{display:block;width:2px;min-width:2px;border-radius:2px;background:#2a2e38;transition:background .1s}
.dsaaWave b.fill{background:#2563eb}
.dsaaTime{font-size:11px;color:#6b7280;min-width:38px;text-align:right;flex:none}
@media (max-width:640px){.dsaaCardThumb,.dsaaThumbWrap{width:56px!important;height:56px!important;min-width:56px!important;min-height:56px!important;max-width:56px!important;max-height:56px!important}.dsaaCardRow{flex-wrap:wrap}}
.ds-mkt-load-more{display:flex;justify-content:center;padding:26px 0 10px;}
.ds-mkt-load-more button{background:#191c25;color:#60a5fa;border:1px solid #1f222b;padding:11px 30px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s;}
.ds-mkt-load-more button:hover{background:#27395c;}
@media(max-width:1100px){.ds-mkt-grid{grid-template-columns:repeat(4,1fr);}}
@media(max-width:800px){
.ds-mkt-grid{grid-template-columns:repeat(3,1fr);}
.ds-mkt{flex-direction:column;height:auto;overflow:visible;}
.ds-mkt-sb{width:100%;border-right:none;border-bottom:1px solid #1e2a40;height:auto;overflow:visible;position:sticky;top:0;z-index:5;}
.ds-mkt-main{height:auto;overflow:visible;}
.ds-mkt-scroll{overflow:visible;}
}
@media(max-width:500px){.ds-mkt-grid{grid-template-columns:repeat(2,1fr);}}
.ds-mkt-card{background:#191c25;border-radius:12px;overflow:hidden;cursor:pointer;box-shadow:0 2px 12px rgba(0,0,0,.45);transition:all .2s;}
.ds-mkt-card:hover{box-shadow:0 6px 24px rgba(0,0,0,.65);transform:translateY(-2px);}
.ds-mkt-thumb{position:relative;aspect-ratio:16/9;overflow:hidden;background:#10131c;}
.ds-mkt-thumb img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .3s;}
.ds-mkt-pagination{flex-wrap:wrap}
.ds-mkt-pagenums{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.ds-mkt-pg{display:inline-flex;align-items:center;justify-content:center;min-width:40px;height:40px;padding:0 10px;background:#191c25;color:#cbd5e1!important;border:1px solid #1f222b;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none!important;font-family:inherit;transition:.2s}
.ds-mkt-pg:hover{border-color:#E8442A;color:#fff!important;background:rgba(232,68,42,.15)}
.ds-mkt-pg-dots{color:#64748b;font-weight:700;padding:0 2px}
@media(max-width:640px){.ds-mkt-pg-far{display:none}}
.ds-mkt-thumb{position:relative}
.ds-mkt-fmt{position:absolute;top:8px;left:8px;z-index:3;padding:3px 7px;border-radius:6px;font:700 10px/1.2 Poppins,sans-serif;letter-spacing:.06em;color:#fff!important;background:rgba(8,10,18,.72);border:1px solid rgba(255,255,255,.2);pointer-events:none;text-transform:uppercase;box-shadow:0 2px 8px rgba(0,0,0,.35)}
.ds-mkt-fmt-vid{background:rgba(124,58,237,.9)}.ds-mkt-fmt-img{background:rgba(37,99,235,.9)}.ds-mkt-fmt-ppt{background:rgba(232,68,42,.92)}.ds-mkt-fmt-pdf{background:rgba(220,38,38,.9)}.ds-mkt-fmt-aud{background:rgba(16,185,129,.9)}
.ds-mkt-thumb video.ds-mkt-card-video{width:100%;height:100%;object-fit:cover;display:block;transition:transform .3s;background:#080a10;}
.ds-mkt-card:hover .ds-mkt-thumb img,.ds-mkt-card:hover .ds-mkt-card-video{transform:scale(1.04);}
.ds-mkt-no-thumb{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:#1f222b;}
.ds-mkt-audio-thumb{position:relative;width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1b2436,#0f1420);overflow:hidden;}
.ds-mkt-audio-art{width:78%;height:78%;border-radius:50%;object-fit:cover;box-shadow:0 4px 18px rgba(0,0,0,.5);animation:dsMktDiscSpin 9s linear infinite;animation-play-state:paused;}
.ds-mkt-audio-art.dsMktSpinning{animation-play-state:running;}
@keyframes dsMktDiscSpin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
.ds-mkt-audio-play{position:absolute;width:44px;height:44px;border-radius:50%;background:rgba(234,76,43,.92);border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .15s,background .15s;}
.ds-mkt-audio-play:hover{transform:scale(1.08);background:#ea4c2b;}
.ds-mkt-body{padding:12px 14px 14px;}
.ds-mkt-body h4{font-size:13px;font-weight:600;color:#f1f5f9;margin:0 0 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ds-mkt-designer{display:flex;align-items:center;gap:5px;font-size:11px;color:#64748b;min-width:0;flex-shrink:1;}
.ds-mkt-designer-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:92px;}
.ds-mkt-meta-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;}
.ds-mkt-res{font-size:10.5px;color:#64748b;font-weight:600;white-space:nowrap;flex-shrink:0;margin-left:8px;}
.ds-mkt-bottom{display:flex;align-items:center;justify-content:space-between;gap:8px;}
.ds-mkt-tag{font-size:11px !important;font-weight:600 !important;background:transparent !important;background-color:transparent !important;border:none !important;padding:0 !important;border-radius:0 !important;box-shadow:none !important;display:inline !important;}
.ds-mkt-tag.free{color:#94a3b8 !important;background:none !important;}
.ds-mkt-tag.pro{color:#f59e0b !important;font-weight:700 !important;background:none !important;}
.ds-mkt-pro-crown{position:absolute;top:8px;left:8px;background:rgba(0,0,0,.55);border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:15px;line-height:1;backdrop-filter:blur(4px);z-index:2;}
.ds-mkt-btns{display:flex;gap:4px;align-items:center;}
.ds-mkt-dl-btn{width:28px !important;height:28px !important;border-radius:6px !important;background:transparent !important;display:flex;align-items:center;justify-content:center;border:none !important;box-shadow:none !important;cursor:pointer;transition:all .15s;color:#94a3b8;padding:0 !important;}
.ds-mkt-dl-btn:hover{background:rgba(148,163,184,.12) !important;color:#f1f5f9;}
.ds-mkt-sh-btn{width:28px !important;height:28px !important;border-radius:6px !important;background:transparent !important;display:flex;align-items:center;justify-content:center;border:none !important;box-shadow:none !important;outline:none !important;cursor:pointer;transition:all .15s;color:#94a3b8;padding:0 !important;}
.ds-mkt-sh-btn:hover{background:rgba(148,163,184,.12) !important;color:#f1f5f9;}
.ds-mkt-lock{width:28px;height:28px;border-radius:6px;background:transparent;display:flex;align-items:center;justify-content:center;color:#94a3b8;}
.ds-mkt-lock:hover{background:rgba(148,163,184,.12);}
/* Popup */
.ds-mkt-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:99999;align-items:center;justify-content:center;padding:20px;box-sizing:border-box;}
.ds-mkt-overlay.open{display:flex;}
.ds-mkt-popup{background:#191c25;border-radius:16px;max-width:1600px;width:96vw;height:92vh;max-height:92vh;overflow:hidden;box-shadow:0 20px 80px rgba(0,0,0,.6);display:flex;flex-direction:column;border:1px solid #2d3a55;}
.ds-mkt-pop-inner{display:flex;flex:1;overflow:hidden;}
.ds-mkt-pop-left{flex:0 0 75%;background:#10131c;display:flex;flex-direction:column;padding:18px;position:relative;}
.ds-mkt-pop-img{flex:1;border-radius:12px;overflow:hidden;background:#080a10;display:flex;align-items:center;justify-content:center;min-height:220px;border:1px solid #1e2a40;position:relative;}
.ds-mkt-pop-img img{width:100%;height:100%;object-fit:contain;display:block;max-height:none;}
.ds-mkt-pop-thumbs{display:flex;gap:6px;margin-top:10px;overflow-x:auto;padding-bottom:4px;}
.ds-mkt-pop-prodnav{display:flex;justify-content:space-between;margin-top:8px;}
.ds-mkt-pop-prodnav-btn{background:transparent;border:none;color:#64748b;cursor:pointer;padding:6px;display:flex;align-items:center;justify-content:center;transition:color .15s;}
.ds-mkt-pop-prodnav-btn:hover:not(:disabled){color:#f1f5f9;}
.ds-mkt-pop-prodnav-btn:disabled{opacity:.25;cursor:not-allowed;}
.ds-mkt-pop-thumb{width:56px;height:42px;border-radius:6px;overflow:hidden;cursor:pointer;border:2px solid transparent;flex-shrink:0;}
.ds-mkt-pop-thumb.active,.ds-mkt-pop-thumb:hover{border-color:#3b82f6;}
.ds-mkt-pop-thumb img{width:100%;height:100%;object-fit:cover;}
.ds-mkt-pop-thumb video{width:100%;height:100%;object-fit:cover;}
.ds-mkt-pop-right{flex:0 0 25%;padding:24px;overflow-y:auto;display:flex;flex-direction:column;border-left:1px solid #1e2a40;}
.ds-mkt-pop-cat{font-size:11px;font-weight:600;color:#60a5fa;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;}
.ds-mkt-pop-title{font-size:20px;font-weight:800;color:#f1f5f9;margin:0 0 6px;}
.ds-mkt-pop-by{font-size:12px;color:#64748b;margin:0 0 12px;}
.ds-mkt-pop-desc{font-size:13px;color:#94a3b8;line-height:1.7;margin:0 0 16px;flex:1;}
.ds-mkt-pop-meta{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:20px;padding:14px;background:#10131c;border-radius:10px;border:1px solid #1e2a40;}
.ds-meta-lbl{display:block;font-size:10px;font-weight:700;color:#4a6080;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;}
.ds-meta-val{font-size:13px;font-weight:600;color:#94a3b8;}
.ds-mkt-pop-dl{display:block;width:100%;padding:13px;border-radius:10px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;text-align:center;font-size:14px;font-weight:700;text-decoration:none;border:none;cursor:pointer;transition:all .15s;margin-bottom:8px;box-sizing:border-box;font-family:inherit;}
.ds-mkt-pop-dl:hover{background:linear-gradient(135deg,#1d4ed8,#1e40af);}
.ds-mkt-pop-dl.locked{background:#191c25;color:#64748b;}
.ds-mkt-pop-share{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:12px;border-radius:10px;background:#7c3aed;color:#fff;text-align:center;font-size:13px;font-weight:700;border:none;cursor:pointer;transition:all .15s;margin-bottom:8px;box-sizing:border-box;font-family:inherit;}
.ds-mkt-pop-share:hover{background:#6d28d9;}
.ds-mkt-pop-close{display:flex;align-items:center;justify-content:center;gap:6px;width:100%;padding:10px;border-radius:0;border:none;background:transparent;color:#64748b;font-size:13px;font-weight:600;cursor:pointer;box-sizing:border-box;font-family:inherit;}.ds-mkt-pop-close:hover{color:#f1f5f9;}

/* Nav arrows on popup */
.ds-mkt-pop-nav{position:absolute;top:50%;transform:translateY(-50%);width:auto !important;height:auto !important;background:transparent !important;color:#fff;border:none !important;box-shadow:none !important;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:10;padding:8px !important;border-radius:0 !important;opacity:.7;transition:opacity .15s;}
.ds-mkt-pop-nav:hover{opacity:1;background:transparent !important;}
.ds-mkt-pop-nav.prev{left:14px;}
.ds-mkt-pop-nav.next{right:14px;}
.ds-mkt-pop-nav:disabled{opacity:.2;cursor:not-allowed;}
/* Share modal */
.ds-mkt-share-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:999999;align-items:center;justify-content:center;}
.ds-mkt-share-overlay.open{display:flex;}
.ds-mkt-share-box{background:#191c25;border-radius:18px;padding:24px;max-width:360px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.5);border:1px solid #2d3a55;}
.ds-mkt-share-box h3{font-size:15px;font-weight:800;color:#f1f5f9;margin:0 0 4px;}
.ds-mkt-share-box p{font-size:12px;color:#64748b;margin:0 0 14px;}
.ds-mkt-share-preview-wrap{position:relative;margin-bottom:14px;}
.ds-mkt-share-preview-wrap img{width:100%;border-radius:10px;max-height:180px;object-fit:cover;background:#10131c;display:block;}
.ds-mkt-share-overlay-text{position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,.75));padding:10px 12px;border-radius:0 0 10px 10px;}
.ds-mkt-share-overlay-title{font-size:12px;color:#fff;font-weight:600;}
.ds-mkt-share-card{background:linear-gradient(135deg,#0f1f3d,#1e1040);border-radius:10px;padding:12px 14px;margin-bottom:14px;}
.ds-mkt-share-card-title{font-size:14px;font-weight:800;color:#fff;margin-bottom:3px;}
.ds-mkt-share-card-sub{font-size:11px;color:rgba(255,255,255,.4);}
.ds-mkt-share-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;}
.ds-mkt-share-btn{padding:9px;border-radius:8px;font-size:12px;font-weight:700;border:none;cursor:pointer;font-family:inherit;color:#fff;}
.ds-mkt-mains{display:flex;flex-direction:column;gap:8px;margin:0 0 18px;}
.ds-mkt-main-btn{display:flex;align-items:center;justify-content:center;
  width:100%;box-sizing:border-box;min-height:42px;padding:0 10px;
  border-radius:4px;border:1px solid #1f222b;background:transparent;color:#cbd5e1;
  font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;
  text-align:center;line-height:1.2;text-decoration:none;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
  transition:background .15s,border-color .15s,color .15s;}
.ds-mkt-main-btn:hover{border-color:#4a6080;color:#fff;}
.ds-mkt-main-btn.active{background:#ea4c2b;border-color:#ea4c2b;color:#fff;}
.ds-mkt-type-toggle{display:flex;gap:6px;margin-bottom:12px;}
.ds-mkt-type-btn{flex:1;text-align:center;padding:8px 6px;border-radius:8px;border:1px solid #22304a;background:#151a26;color:#94a3b8;font-size:12px;font-weight:600;text-decoration:none;transition:background .15s,border-color .15s,color .15s;}
.ds-mkt-type-btn:hover{border-color:#4a6080;color:#fff;}
.ds-mkt-type-btn.active{background:#ea4c2b;border-color:#ea4c2b;color:#fff;}
.ds-mkt-subs{list-style:none;padding:0;margin:0;border-top:1px solid #22304a;padding-top:12px;}
 </style>

<div class="ds-mkt">
  <!-- SIDEBAR -->
  <div class="ds-mkt-sb">
    <div class="ds-mkt-type-toggle">
      <a href="?type=visual" class="ds-mkt-type-btn <?php echo ($current_type !== 'audio') ? 'active' : ''; ?>">Visual</a>
      <a href="?type=audio" class="ds-mkt-type-btn <?php echo ($current_type === 'audio') ? 'active' : ''; ?>">Audio</a>
    </div>
    <h3>Categories</h3>
    <?php
    // Marketplace root term (type-aware: Visual -> 'marketplace', Audio -> 'audio')
    $mkt_root_slug = ($current_type === 'audio') ? 'audio' : 'marketplace';
    $mkt_cat = null;
    foreach ($all_cats as $cat) {
      if ($cat->slug === $mkt_root_slug && $cat->parent == 0) { $mkt_cat = $cat; break; }
    }
    $mkt_id = $mkt_cat ? $mkt_cat->term_id : 0;

    // Main categories = direct children of Marketplace (Images / Presentations / Videos)
    $main_cats = array_values(array_filter($all_cats, function($c) use ($mkt_id) {
      return $c->parent == $mkt_id;
    }));

    // Walk up from the current selection to find which main category it belongs to
    $by_slug = array(); $by_id = array();
    foreach ($all_cats as $c) { $by_slug[$c->slug] = $c; $by_id[$c->term_id] = $c; }
    $active_main = null;
    if ($current_cat && isset($by_slug[$current_cat])) {
      $node = $by_slug[$current_cat];
      while ($node && $node->parent != $mkt_id && $node->parent != 0) {
        $node = isset($by_id[$node->parent]) ? $by_id[$node->parent] : null;
      }
      if ($node && $node->parent == $mkt_id) $active_main = $node;
    }
    ?>

    <div class="ds-mkt-mains">
      <a href="?type=<?php echo esc_attr($current_type); ?>" class="ds-mkt-main-btn <?php echo !$current_cat ? 'active' : ''; ?>">All</a>
      <?php foreach ($main_cats as $mc): ?>
        <a href="?type=<?php echo esc_attr($current_type); ?>&cat=<?php echo esc_attr($mc->slug); ?>"
           class="ds-mkt-main-btn <?php echo ($active_main && $active_main->term_id === $mc->term_id) ? 'active' : ''; ?>">
          <?php echo esc_html($mc->name); ?>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if ($active_main): ?>
      <ul class="ds-mkt-subs">
        <?php echo ds_render_cat_tree($all_cats, $active_main->term_id, $current_cat, 0, $current_type); ?>
      </ul>
    <?php endif; ?>
  </div>

  <!-- MAIN -->
  <div class="ds-mkt-main">
		<div class="ds-mkt-toolbar">
      <div class="ds-mkt-search">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="#4a6080" stroke-width="2"/><path d="M16.5 16.5L21 21" stroke="#4a6080" stroke-width="2" stroke-linecap="round"/></svg>
        <input type="text" id="ds-mkt-search" autocomplete="off" placeholder="Search designs… press Enter" value="<?php echo esc_attr($dl_q); ?>" onkeydown="if(event.key==='Enter'){var sc=(document.getElementById('ds-mkt-scope')||{}).value||'db';if(sc==='page'){dsMktApplyVisibility();}else{(function(v){var p=location.search.substring(1).split('&').filter(function(x){return x&&x.indexOf('q=')!==0&&x.indexOf('dlpage=')!==0&&x.indexOf('scope=')!==0&&(sc==='cat'||x.indexOf('cat=')!==0)});if(v)p.push('q='+encodeURIComponent(v));p.push('scope='+sc);location.href=location.pathname+(p.length?'?'+p.join('&'):'');})(this.value.trim())}}"/>
      </div>
      <select class="ds-mkt-sort" id="ds-mkt-scope" title="Search scope">
        <option value="db" <?php selected($dl_scope,'db'); ?>>Search: Whole Database</option>
        <option value="cat" <?php selected($dl_scope,'cat'); ?>>Search: This Category</option>
        <option value="page" <?php selected($dl_scope,'page'); ?>>Search: This Page</option>
      </select>
      <select class="ds-mkt-sort" onchange="(function(v){var p=location.search.substring(1).split('&').filter(function(x){return x&&x.indexOf('sort=')!==0&&x.indexOf('dlpage=')!==0;});p.push('sort='+encodeURIComponent(v));location.href=location.pathname+'?'+p.join('&');})(this.value)">
        <option value="latest" <?php selected($dl_sort,'latest'); ?>>Latest first</option>
        <option value="oldest" <?php selected($dl_sort,'oldest'); ?>>Oldest first</option>
        <option value="name"   <?php selected($dl_sort,'name');   ?>>Name (A–Z)</option>
        <option value="name_z" <?php selected($dl_sort,'name_z'); ?>>Name (Z–A)</option>
      </select>
    </div>

    <div class="ds-mkt-scroll" id="ds-mkt-scroll">
<?php if (!empty($dl_is_fallback)): ?>
<div style="padding:10px 14px;margin:0 0 12px;background:#191c25;border:1px solid #2d3a55;border-radius:8px;color:#94a3b8;font-size:13px;">No exact match for "<strong style="color:#f1f5f9;"><?php echo esc_html($dl_q); ?></strong>" — showing related results.</div>
<?php endif; ?>
<?php if ($dl_q !== '' && empty($products)): ?>
<div style="padding:60px 20px;text-align:center;color:#64748b;font-size:14px;">No results found for "<strong style="color:#94a3b8;"><?php echo esc_html($dl_q); ?></strong>". Try a different search term.</div>
<?php endif; ?>
    <div class="ds-mkt-grid" id="ds-mkt-grid">
      <?php foreach ($products as $prod):
        $popup_json = esc_attr(json_encode(array(
            'id'        => $prod['id'],
            'title'     => $prod['title'],
            'cat'       => $prod['cat'],
            'tier'      => $prod['tier'],
            'tier_label'=> $prod['tier_label'],
            'designer'  => $prod['designer'],
            'media'     => $prod['media'],
            'permalink' => $prod['permalink'],
            'desc'      => $prod['desc'],
            'format'    => $prod['format'],
            'is_video'  => $prod['is_video'],
            'is_audio'  => $prod['is_audio'],
            'audio_url' => $prod['audio_url'],
            'can_dl'    => $prod['can_dl'],
            'action_url'=> $prod['action_url'],
            'dl_url'    => $prod['dl_url'],
            'resolution' => $prod['resolution'],
        )));
      ?>

      <?php if ($prod['is_audio']): ?>
      <div class="dsaaCard" data-tags="<?php echo esc_attr( $prod['tags'] ); ?>">
        <div class="dsaaCardRow">
          <div class="dsaaCardBody">
            <div class="dsaaCardTop">
              <div class="dsaaCardMeta">
                <span class="dsaaCardKind">Audio</span>
                <div class="dsaaCardLabel" title="<?php echo esc_attr($prod['title']); ?>"><?php echo esc_html($prod['title']); ?></div>
              </div>
              <div style="display:flex;align-items:center;gap:8px">
                <?php if ($prod['tier'] === 'free'): ?><span class="dsaaBadge temp">Free</span><?php else: ?><span class="dsaaBadge saved">👑 Pro</span><?php endif; ?>
                <div class="dsaaCardActions">
                  <?php if ($prod['can_dl']): ?>
                  <a href="<?php echo esc_url($prod['action_url']); ?>" class="dsaaIconBtn" title="Download" onclick="event.stopPropagation();event.preventDefault();dsMktDoDownload(this,<?php echo intval($prod['id']); ?>,'<?php echo esc_js($prod['tier']); ?>','<?php echo esc_js($prod['action_url']); ?>');" style="text-decoration:none;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                  </a>
                  <?php else: ?>
                  <a href="<?php echo esc_url($pricing_url); ?>" class="dsaaIconBtn" title="Upgrade" onclick="event.stopPropagation()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2l3 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="#7c3aed" stroke-width="2"/></svg>
                  </a>
                  <?php endif; ?>
                  <button class="dsaaIconBtn" onclick="event.stopPropagation();dsMktCardShare('<?php echo esc_js($prod['share_thumb']); ?>','<?php echo esc_js($prod['title']); ?>',<?php echo (int)$prod['id']; ?>)" title="Share">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="2"/><circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="2"/><circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="2"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="currentColor" stroke-width="2"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="currentColor" stroke-width="2"/></svg>
                  </button>
                </div>
              </div>
            </div>
            <div class="dsaaPlayer">
              <div class="dsaaThumbWrap">
                <img class="dsaaCardThumb" src="/wp-content/uploads/2026/08/image-1.png" alt="">
                <button class="dsaaThumbPlay" data-act="thumbplay" type="button"><svg width="13" height="13" viewBox="0 0 24 24" fill="#fff"><path d="M8 5v14l11-7z"/></svg></button>
              </div>
              <div class="dsaaWave" data-act="seek">
                <div class="dsaaWaveCursor"></div>
              </div>
              <span class="dsaaTime">--:--</span>
              <div class="dsaaVolWrap">
                <span class="dsaaVolIcon" data-act="mute" title="Mute"><svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M4 9v6h4l5 5V4L8 9H4z" fill="currentColor"/><path d="M16.5 8.5a5 5 0 010 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M19 6a9 9 0 010 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                <input type="range" class="dsaaVolSlider" min="0" max="1" step="0.01" value="1" data-act="volume">
              </div>
            </div>
            <div class="dsaaCardDate"><?php if ($prod['designer']): ?>by <?php echo esc_html($prod['designer']); ?><?php endif; ?></div>
          </div>
        </div>
        <audio preload="metadata" src="<?php echo esc_url($prod['audio_url']); ?>"></audio>
      </div>
      <?php endif; ?>
      <?php if (!$prod['is_audio']): ?>
      <div class="ds-mkt-card" data-title="<?php echo esc_attr(strtolower($prod['title'])); ?>" data-popup="<?php echo $popup_json; ?>" data-tags="<?php echo esc_attr( $prod['tags'] ); ?>" onclick="dsMktOpenPopup(this)">
        <div class="ds-mkt-thumb">
            <?php if (!empty($prod['fmt_tag'])): ?><span class="ds-mkt-fmt ds-mkt-fmt-<?php echo esc_attr(strtolower($prod['fmt_tag'])); ?>"><?php echo esc_html($prod['fmt_tag']); ?></span><?php endif; ?>
          <?php if ($prod['thumb_type'] === 'video'): ?>
          <video class="ds-mkt-card-video" data-src="<?php echo esc_url($prod['thumb_grid']); ?>"<?php if (!empty($prod['thumb_poster'])) echo ' poster="' . esc_url($prod['thumb_poster']) . '"'; ?> muted loop playsinline preload="none"></video>
          <?php elseif ($prod['is_audio']): ?>
          <div class="ds-mkt-audio-thumb">
            <img class="ds-mkt-audio-art" src="/wp-content/uploads/2026/08/image-1.png" alt="" loading="lazy">
            <button type="button" class="ds-mkt-audio-play" data-audio="<?php echo esc_url($prod['audio_url']); ?>" onclick="dsMktAudioToggle(event,this)" aria-label="Play preview">
              <svg class="ico-play" width="20" height="20" viewBox="0 0 24 24" fill="#fff"><path d="M8 5v14l11-7z"/></svg>
              <svg class="ico-pause" width="20" height="20" viewBox="0 0 24 24" fill="#fff" style="display:none;"><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>
            </button>
          </div>
          <?php elseif ($prod['thumb_grid']): ?>
          <img src="<?php echo esc_url($prod['thumb_grid']); ?>" alt="<?php echo esc_attr($prod['title']); ?>" loading="lazy"/>
          <?php else: ?>
          <div class="ds-mkt-no-thumb"><?php echo strtoupper(substr($prod['title'],0,2)); ?></div>
          <?php endif; ?>
        </div>
        <div class="ds-mkt-body">
          <h4><?php echo esc_html($prod['title']); ?></h4>
          <div class="ds-mkt-meta-row">
            <?php if ($prod['tier'] === 'free'): ?><span class="ds-mkt-tag free">Free</span><?php else: ?><span class="ds-mkt-tag pro">👑 Pro</span><?php endif; ?>
            <?php if (!empty($prod['resolution'])): ?><span class="ds-mkt-res"><?php echo esc_html($prod['resolution']); ?></span><?php endif; ?>
          </div>
          <div class="ds-mkt-bottom">
            <?php if ($prod['designer']): ?><div class="ds-mkt-designer"><svg width="10" height="10" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="#64748b" stroke-width="1.8"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#64748b" stroke-width="1.8" stroke-linecap="round"/></svg><span class="ds-mkt-designer-name"><?php echo esc_html($prod['designer']); ?></span></div><?php else: ?><span></span><?php endif; ?>
            <div class="ds-mkt-btns">
              <?php if ($prod['can_dl']): ?>
              <a href="<?php echo esc_url($prod['action_url']); ?>" class="ds-mkt-dl-btn" title="Download" onclick="event.stopPropagation();event.preventDefault();dsMktDoDownload(this,<?php echo intval($prod['id']); ?>,'<?php echo esc_js($prod['tier']); ?>','<?php echo esc_js($prod['action_url']); ?>');" style="text-decoration:none;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 3v13M7 12l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 20h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              </a>
              <?php elseif (!$logged_in): ?>
              <a href="<?php echo esc_url($prod['action_url']); ?>" class="ds-mkt-lock" title="Login" onclick="event.stopPropagation()">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" stroke="#64748b" stroke-width="2"/><path d="M7 11V7a5 5 0 0110 0v4" stroke="#64748b" stroke-width="2"/></svg>
              </a>
              <?php else: ?>
              <a href="<?php echo esc_url($pricing_url); ?>" class="ds-mkt-lock" title="Upgrade" onclick="event.stopPropagation()">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M12 2l3 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="#7c3aed" stroke-width="2"/></svg>
              </a>
              <?php endif; ?>
              <button class="ds-mkt-sh-btn" onclick="event.stopPropagation();dsMktCardShare('<?php echo esc_js($prod['share_thumb']); ?>','<?php echo esc_js($prod['title']); ?>',<?php echo (int)$prod['id']; ?>)" title="Share">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="2"/><circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="2"/><circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="2"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="currentColor" stroke-width="2"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="currentColor" stroke-width="2"/></svg>
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <div class="ds-mkt-pagination" style="display:flex;align-items:center;justify-content:center;gap:14px;padding:26px 0 10px;">
        <?php
        // Numbered pages (2026-09-23): 1 … [6 before] ← Prev | Page X of N | Next → [6 after] … last
        $ds_pg_args = $dl_next_args;
        $ds_pg_url  = function ( $n ) use ( $ds_pg_args ) { $a = $ds_pg_args; $a['dlpage'] = (int) $n; return esc_url( add_query_arg( $a ) ); };
        $ds_pg_from = max( 1, $dl_page - 6 );
        $ds_pg_to   = min( $dl_total_pages, $dl_page + 6 );
        $ds_pg_cls  = function ( $n ) use ( $dl_page ) { return 'ds-mkt-pg' . ( abs( $n - $dl_page ) > 2 ? ' ds-mkt-pg-far' : '' ); };
        ?>
        <?php if ( $dl_total_pages > 1 ) : ?>
        <div class="ds-mkt-pagenums">
            <?php if ( $ds_pg_from > 1 ) : ?>
                <a class="ds-mkt-pg" href="<?php echo $ds_pg_url( 1 ); ?>">1</a>
                <?php if ( $ds_pg_from > 2 ) : ?><span class="ds-mkt-pg-dots">&hellip;</span><?php endif; ?>
            <?php endif; ?>
            <?php for ( $ds_n = $ds_pg_from; $ds_n < $dl_page; $ds_n++ ) : ?>
                <a class="<?php echo esc_attr( $ds_pg_cls( $ds_n ) ); ?>" href="<?php echo $ds_pg_url( $ds_n ); ?>"><?php echo (int) $ds_n; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php if ($dl_page > 1): ?>
        <a href="<?php echo $dl_prev_url; ?>" style="background:#191c25;color:#60a5fa;border:1px solid #1f222b;padding:11px 22px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;font-family:inherit;">← Previous</a>
        <?php else: ?>
        <span style="background:#11182b;color:#475569;border:1px solid #1e2a40;padding:11px 22px;border-radius:10px;font-size:13px;font-weight:700;font-family:inherit;">← Previous</span>
        <?php endif; ?>

        <span style="color:#64748b;font-size:12.5px;font-weight:600;">Page <?php echo intval($dl_page); ?> of <?php echo intval($dl_total_pages); ?></span>

        <?php if ($dl_page < $dl_total_pages): ?>
        <a href="<?php echo $dl_next_url; ?>" style="background:#191c25;color:#60a5fa;border:1px solid #1f222b;padding:11px 22px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;font-family:inherit;">Next →</a>
        <?php else: ?>
        <span style="background:#11182b;color:#475569;border:1px solid #1e2a40;padding:11px 22px;border-radius:10px;font-size:13px;font-weight:700;font-family:inherit;">Next →</span>
        <?php endif; ?>
        <?php if ( $dl_total_pages > 1 ) : ?>
        <div class="ds-mkt-pagenums">
            <?php for ( $ds_n = $dl_page + 1; $ds_n <= $ds_pg_to; $ds_n++ ) : ?>
                <a class="<?php echo esc_attr( $ds_pg_cls( $ds_n ) ); ?>" href="<?php echo $ds_pg_url( $ds_n ); ?>"><?php echo (int) $ds_n; ?></a>
            <?php endfor; ?>
            <?php if ( $ds_pg_to < $dl_total_pages ) : ?>
                <?php if ( $ds_pg_to < $dl_total_pages - 1 ) : ?><span class="ds-mkt-pg-dots">&hellip;</span><?php endif; ?>
                <a class="ds-mkt-pg" href="<?php echo $ds_pg_url( $dl_total_pages ); ?>"><?php echo (int) $dl_total_pages; ?></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    </div><!-- /.ds-mkt-scroll -->
  </div>
</div>

<!-- POPUP -->
<div class="ds-mkt-overlay" id="ds-mkt-overlay">
  <div class="ds-mkt-popup">
    <div class="ds-mkt-pop-inner">
      <div class="ds-mkt-pop-left">
        <button class="ds-mkt-pop-nav prev" id="ds-mkt-pop-prev" onclick="dsMktNavImage(-1)" title="Previous image"><svg width="36" height="36" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
        <button class="ds-mkt-pop-nav next" id="ds-mkt-pop-next" onclick="dsMktNavImage(1)" title="Next image"><svg width="36" height="36" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
        <div class="ds-mkt-pop-img" id="ds-mkt-pop-img-wrap">
          <img id="ds-mkt-pop-main-img" src="" alt="Preview"/>
          <video id="ds-mkt-pop-video" controls style="display:none;width:100%;height:100%;object-fit:contain;border-radius:12px;background:#000;"><source id="ds-mkt-pop-vsrc" src="" type="video/mp4"/></video>
        </div>
        <div class="ds-mkt-pop-thumbs" id="ds-mkt-pop-thumbs"></div>
      </div>
      <div class="ds-mkt-pop-right">
        <div class="ds-mkt-pop-cat" id="ds-mkt-pop-cat"></div>
        <h2 class="ds-mkt-pop-title" id="ds-mkt-pop-title"></h2>
        <p class="ds-mkt-pop-by" id="ds-mkt-pop-by"></p>
        <div id="ds-mkt-pop-badge" style="margin-bottom:12px;"></div>
        <p class="ds-mkt-pop-desc" id="ds-mkt-pop-desc"></p>
        <div class="ds-mkt-pop-meta">
          <div><span class="ds-meta-lbl">Category</span><span class="ds-meta-val" id="ds-mkt-pop-meta-cat"></span></div>
          <div><span class="ds-meta-lbl">Format</span><span class="ds-meta-val" id="ds-mkt-pop-meta-fmt"></span></div>
          <div id="ds-mkt-pop-designer-row" style="display:none;"><span class="ds-meta-lbl">Designer</span><span class="ds-meta-val" id="ds-mkt-pop-meta-designer"></span></div>
          <div id="ds-mkt-pop-dims-row" style="display:none;"><span class="ds-meta-lbl">Dimensions</span><span class="ds-meta-val" id="ds-mkt-pop-meta-dims"></span></div>
        </div>
        <button class="ds-mkt-pop-dl" id="ds-mkt-pop-dl-btn">Download Now</button>
        <button class="ds-mkt-pop-share" onclick="dsMktPopShare()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="3" stroke="#fff" stroke-width="2"/><circle cx="6" cy="12" r="3" stroke="#fff" stroke-width="2"/><circle cx="18" cy="19" r="3" stroke="#fff" stroke-width="2"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="#fff" stroke-width="2"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="#fff" stroke-width="2"/></svg>
          Share this Design
        </button>
        <button class="ds-mkt-pop-close" onclick="dsMktClosePopup()">
          <svg width="12" height="12" viewBox="0 0 16 16" fill="none"><path d="M1 1l14 14M15 1L1 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>Close
        </button>
        <div class="ds-mkt-pop-prodnav">
          <button id="ds-mkt-prodnav-prev" class="ds-mkt-pop-prodnav-btn" onclick="dsMktNavProduct(-1)" title="Previous product"><svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
          <button id="ds-mkt-prodnav-next" class="ds-mkt-pop-prodnav-btn" onclick="dsMktNavProduct(1)" title="Next product"><svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- SHARE MODAL -->
<div class="ds-mkt-share-overlay" id="ds-mkt-share-overlay">
  <div class="ds-mkt-share-box">
    <h3>Share this Design</h3>
    <p>Found on Designstager Marketplace</p>
    <div class="ds-mkt-share-preview-wrap">
      <img id="ds-mkt-share-img" src="" alt=""/>
      <div class="ds-mkt-share-overlay-text"><div class="ds-mkt-share-overlay-title" id="ds-mkt-share-title-overlay"></div></div>
    </div>
    <div class="ds-mkt-share-card"><div class="ds-mkt-share-card-title">Designstager Marketplace</div><div class="ds-mkt-share-card-sub">Free and Pro design templates - designstager.com</div></div>
    <div class="ds-mkt-share-grid">
      <button class="ds-mkt-share-btn" style="background:#25D366;" onclick="dsMktShareVia('whatsapp')">WhatsApp</button>
      <button class="ds-mkt-share-btn" id="ds-mkt-copy-btn" style="background:#6366f1;" onclick="dsMktShareVia('copy')">Copy Link</button>
      <button class="ds-mkt-share-btn" style="background:#1da1f2;" onclick="dsMktShareVia('twitter')">Twitter / X</button>
      <button class="ds-mkt-share-btn" style="background:#1877f2;" onclick="dsMktShareVia('facebook')">Facebook</button>
    </div>
    <button onclick="dsMktShareClose()" style="width:100%;padding:9px;border-radius:8px;border:1.5px solid #2d3a55;background:transparent;color:#64748b;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;">Close</button>
  </div>
</div>

<script>
(function(){
var dsMktCurAudio=null, dsMktCurBtn=null;
function dsMktSetPlayIcon(btn, playing){ if(!btn) return; var p=btn.querySelector('.ico-play'), pa=btn.querySelector('.ico-pause'); if(p) p.style.display = playing?'none':''; if(pa) pa.style.display = playing?'':'none'; var thumb=btn.closest('.ds-mkt-audio-thumb'); var art=thumb?thumb.querySelector('.ds-mkt-audio-art'):null; if(art) art.classList.toggle('dsMktSpinning', !!playing); }
window.dsMktAudioToggle = function(e, btn){
  e.stopPropagation();
  var src = btn.getAttribute('data-audio');
  if (!src) return;
  if (dsMktCurBtn === btn && dsMktCurAudio) {
    if (dsMktCurAudio.paused) { dsMktCurAudio.play(); dsMktSetPlayIcon(btn, true); }
    else { dsMktCurAudio.pause(); dsMktSetPlayIcon(btn, false); }
    return;
  }
  if (dsMktCurAudio) { dsMktCurAudio.pause(); dsMktSetPlayIcon(dsMktCurBtn, false); }
  dsMktCurAudio = new Audio(src);
  dsMktCurBtn = btn;
  dsMktCurAudio.play();
  dsMktSetPlayIcon(btn, true);
  dsMktCurAudio.addEventListener('ended', function(){ dsMktSetPlayIcon(btn, false); });
};
var SITE='<?php echo esc_js($site_url); ?>';
var PRICING='<?php echo esc_js($pricing_url); ?>';
var LOGIN='<?php echo esc_js($login_url); ?>';
var NONCE='<?php echo esc_js($dl_nonce); ?>';
var REST='<?php echo esc_js($rest_url); ?>';
var LOGGED=<?php echo $logged_in ? 'true' : 'false'; ?>;
var CAN_PRO=<?php echo $can_pro ? 'true' : 'false'; ?>;
var USER_TIER='<?php echo esc_js($user_tier); ?>';
var DL_LIMIT_FREE=<?php echo intval($user_limits['free']); ?>;
var DL_LIMIT_PRO=<?php echo intval($user_limits['pro']); ?>;
var DS_REST_URL='<?php echo esc_js(rest_url('ds/v1/dl-check')); ?>';
var DS_NONCE='<?php echo esc_js($dl_nonce); ?>';
var currentData=null;
var currentIndex=-1;
var allCards=[];
var shareUrl='',shareTitle='',shareImg='';

function q(id){return document.getElementById(id);}

function dsMktCollectCards(){
    allCards=Array.prototype.slice.call(document.querySelectorAll('.ds-mkt-card'));
}

function dsMktShowMedia(i){
    var item=currentImages[i];if(!item)return;
    var mainImg=q('ds-mkt-pop-main-img'),video=q('ds-mkt-pop-video');
    if(item.type==='video'){
        mainImg.style.display='none';video.style.display='block';
        q('ds-mkt-pop-vsrc').src=item.preview;video.load();
        var p=video.play();if(p&&p.catch)p.catch(function(){});
    } else {
        if(video.pause)video.pause();
        video.style.display='none';
        mainImg.style.display='block';mainImg.src=item.preview;
    }
}

// POPUP
window.dsMktOpenPopup=function(card){
    if(!allCards.length)dsMktCollectCards();
    currentIndex=allCards.indexOf(card);
    var data=JSON.parse(card.getAttribute('data-popup'));
    currentData=data;
    var media=data.media||[];
    currentImages=media.slice();
    currentImageIndex=0;
    dsMktShowMedia(0);
    var thumbs=q('ds-mkt-pop-thumbs');thumbs.innerHTML='';
    if(media.length>1){
        media.forEach(function(item,i){
            var t=document.createElement('div');t.className='ds-mkt-pop-thumb'+(i===0?' active':'');
            if(item.type==='video'){
                t.innerHTML='<video src="'+item.grid+'" muted loop autoplay playsinline></video>';
            } else {
                t.innerHTML='<img src="'+item.grid+'" alt=""/>';
            }
            t.onclick=function(e){e.stopPropagation();currentImageIndex=i;dsMktShowMedia(i);document.querySelectorAll('.ds-mkt-pop-thumb').forEach(function(el){el.classList.remove('active');});t.classList.add('active');dsMktUpdateNav();};
            thumbs.appendChild(t);
        });
    }
    q('ds-mkt-pop-title').textContent=data.title;
    q('ds-mkt-pop-cat').textContent=data.cat;
    q('ds-mkt-pop-meta-cat').textContent=data.cat;
    q('ds-mkt-pop-meta-fmt').textContent=data.format||'PowerPoint / PDF';
    var byEl=q('ds-mkt-pop-by');if(data.designer){byEl.textContent='Designed by '+data.designer;byEl.style.display='';}else{byEl.style.display='none';}
    var drEl=q('ds-mkt-pop-designer-row');var dv=q('ds-mkt-pop-meta-designer');
    if(data.designer){drEl.style.display='';if(dv)dv.textContent=data.designer;}else{drEl.style.display='none';}
    var dimsEl=q('ds-mkt-pop-dims-row');var dimsVal=q('ds-mkt-pop-meta-dims');
    if(data.resolution){dimsEl.style.display='';if(dimsVal)dimsVal.textContent=data.resolution;}else{dimsEl.style.display='none';}
    q('ds-mkt-pop-desc').textContent=data.desc;
    var badgeMap={free:'<span class="ds-mkt-tag free">Free</span>',pro:'<span class="ds-mkt-tag pro">👑 Pro</span>'};
    q('ds-mkt-pop-badge').innerHTML=badgeMap[data.tier]||'';
    var dlBtn=q('ds-mkt-pop-dl-btn');
    if(!LOGGED){dlBtn.textContent='Login to Download';dlBtn.className='ds-mkt-pop-dl';dlBtn.onclick=function(){window.location.href=LOGIN;};}
    else if(data.tier==='pro'&&!CAN_PRO){dlBtn.textContent='Upgrade to Pro';dlBtn.className='ds-mkt-pop-dl locked';dlBtn.onclick=function(){window.location.href=PRICING;};}
    else{dlBtn.textContent='Download Now';dlBtn.className='ds-mkt-pop-dl';dlBtn.onclick=function(){dsMktDoDownload(dlBtn,data.id,data.tier,data.action_url);};}
    dsMktUpdateNav();
    q('ds-mkt-overlay').classList.add('open');document.body.style.overflow='hidden';
};

var currentImageIndex=0;
var currentImages=[];

function dsMktUpdateNav(){
    // Image arrows: hide entirely if only 1 image
    var prev=q('ds-mkt-pop-prev'),next=q('ds-mkt-pop-next');
    if(prev&&next){
        var multi=(currentImages.length>1);
        prev.style.display=multi?'flex':'none';
        next.style.display=multi?'flex':'none';
        if(multi){
            prev.disabled=(currentImageIndex<=0);
            next.disabled=(currentImageIndex>=currentImages.length-1);
        }
    }
    // Product nav: at bottom of right panel
    var pp=q('ds-mkt-prodnav-prev'),pn=q('ds-mkt-prodnav-next');
    if(pp&&pn){
        pp.disabled=(currentIndex<=0);
        pn.disabled=(currentIndex>=allCards.length-1);
    }
}

window.dsMktNavImage=function(dir){
    if(!currentImages.length)return;
    var ni=currentImageIndex+dir;
    if(ni<0||ni>=currentImages.length)return;
    currentImageIndex=ni;
    dsMktShowMedia(ni);
    var thumbsWrap=q('ds-mkt-pop-thumbs');
    if(thumbsWrap){
        var thumbEls=thumbsWrap.querySelectorAll('.ds-mkt-pop-thumb');
        thumbEls.forEach(function(el,i){el.classList.toggle('active',i===ni);});
        if(thumbEls[ni]&&thumbEls[ni].scrollIntoView)thumbEls[ni].scrollIntoView({behavior:'smooth',block:'nearest',inline:'center'});
    }
    dsMktUpdateNav();
};

window.dsMktNavProduct=function(dir){
    if(!allCards.length)return;
    var ni=currentIndex+dir;
    if(ni<0||ni>=allCards.length)return;
    var card=allCards[ni];
    if(card)dsMktOpenPopup(card);
};

window.dsMktClosePopup=function(){
    var v=q('ds-mkt-pop-video');if(v){v.pause();v.currentTime=0;}
    q('ds-mkt-overlay').classList.remove('open');document.body.style.overflow='';
};

// SHARE from popup
window.dsMktPopShare=function(){
    if(!currentData)return;
    var pid=currentData.id||0;
    var first=(currentData.media&&currentData.media[0])?currentData.media[0]:null;
    var img=(first&&first.type!=='video')?first.preview:'';
    shareUrl=pid?(window.location.origin+window.location.pathname+'?product='+pid):window.location.href;
    shareTitle=currentData.title||'';shareImg=img;
    q('ds-mkt-share-img').src=img;
    var tov=q('ds-mkt-share-title-overlay');if(tov)tov.textContent=shareTitle;
    dsMktClosePopup();q('ds-mkt-share-overlay').classList.add('open');
};

// SHARE from card button
window.dsMktCardShare=function(img,title,pid){
    shareUrl=pid?(window.location.origin+window.location.pathname+'?product='+pid):window.location.href;
    shareTitle=title||'';shareImg=img||'';
    q('ds-mkt-share-img').src=shareImg;
    var tov=q('ds-mkt-share-title-overlay');if(tov)tov.textContent=shareTitle;
    q('ds-mkt-share-overlay').classList.add('open');
};
window.dsMktShareClose=function(){q('ds-mkt-share-overlay').classList.remove('open');};
window.dsMktShareVia=function(ch){
    var msg='*'+shareTitle+'*\nDownload this design on Designstager Marketplace!\n'+shareUrl+'\n\nFree & Pro design templates at designstager.com';
    if(ch==='whatsapp')window.open('https://wa.me/?text='+encodeURIComponent(msg),'_blank');
    else if(ch==='twitter')window.open('https://twitter.com/intent/tweet?text='+encodeURIComponent(shareTitle+' - Designstager Marketplace '+shareUrl),'_blank');
    else if(ch==='facebook')window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(shareUrl),'_blank');
    else if(ch==='copy'){
        navigator.clipboard.writeText(shareTitle+'\n'+shareUrl).then(function(){
            var btn=q('ds-mkt-copy-btn');if(btn){btn.textContent='Copied!';setTimeout(function(){btn.textContent='Copy Link';},2000);}
        }).catch(function(){});
    }
};
	
// SEARCH (filters within the current page — pagination is server-side now)
function dsMktApplyVisibility(){
	var qv = q('ds-mkt-search').value.toLowerCase();
	var cards = document.querySelectorAll('.ds-mkt-card');
	cards.forEach(function(c){
		var titleMatch = c.getAttribute('data-title').includes(qv);
		var tagsAttr = c.getAttribute('data-tags')||'';
		var tagsMatch = tagsAttr.toLowerCase().includes(qv);
		var matches = !qv || titleMatch || tagsMatch;
		c.style.display = matches ? '' : 'none';
	});
}
q('ds-mkt-search').addEventListener('input', function(){var sc=(document.getElementById('ds-mkt-scope')||{}).value||'db';if(sc==='page')dsMktApplyVisibility();});
if(((document.getElementById('ds-mkt-scope')||{}).value||'db')==='page')dsMktApplyVisibility();

// GRID CARD VIDEO: show poster image; load + play the video only on mouse hover (2026-09-23)
var dsMktCanHover = !!(window.matchMedia && window.matchMedia('(hover: hover)').matches);
function dsMktLoadVid(v){
 if(!v.getAttribute('src') && v.dataset.src){ v.src = v.dataset.src + (v.getAttribute('poster') ? '' : '#t=0.1'); }
}
function dsMktTryPlay(v){
 dsMktLoadVid(v);
 v.muted = true;
 var p = v.play();
 if(p && p.catch){ p.catch(function(){}); }
}
function dsMktStopVid(v){
 try { v.pause(); if(v.readyState > 0){ v.currentTime = 0; } } catch(e){}
}
var dsMktVidObserver = ('IntersectionObserver' in window) ? new IntersectionObserver(function(entries){
 entries.forEach(function(en){
 var v = en.target;
 v._dsInView = en.isIntersecting;
 if(!en.isIntersecting){ dsMktStopVid(v); }
 });
},{rootMargin:'200px'}) : null;

function dsMktInitCardVideos(){
 document.querySelectorAll('.ds-mkt-card-video').forEach(function(v){
 if(v._dsObserved) return;
 v._dsObserved = true;
 var card = v.closest('.ds-mkt-card') || v;
 if(dsMktCanHover){
 card.addEventListener('mouseenter', function(){ dsMktTryPlay(v); });
 card.addEventListener('mouseleave', function(){ dsMktStopVid(v); });
 }
 if(dsMktVidObserver){ dsMktVidObserver.observe(v); }
 });
}
dsMktInitCardVideos();

// Close overlays
q('ds-mkt-overlay').addEventListener('click',function(e){if(e.target===this)dsMktClosePopup();});
q('ds-mkt-share-overlay').addEventListener('click',function(e){if(e.target===this)dsMktShareClose();});
document.addEventListener('keydown',function(e){
    if(e.key==='Escape'){dsMktClosePopup();dsMktShareClose();}
    if(q('ds-mkt-overlay').classList.contains('open')){
        if(e.key==='ArrowLeft')dsMktNavImage(-1);
        if(e.key==='ArrowRight')dsMktNavImage(1);
    }
});
})();

// Expose vars for dsMktDoDownload
window.LOGGED = <?php echo $logged_in ? 'true' : 'false'; ?>;
window.USER_TIER = '<?php echo esc_js($user_tier); ?>';
window.DL_LIMIT_FREE = <?php echo intval($user_limits['free']); ?>;
window.DL_LIMIT_PRO = <?php echo intval($user_limits['pro']); ?>;
window.DS_REST_URL = '<?php echo esc_js(rest_url('ds/v1/dl-check')); ?>';
window.DS_NONCE = '<?php echo esc_js($dl_nonce); ?>';
window.PRICING = '<?php echo esc_js($pricing_url); ?>';
window.LOGIN = '<?php echo esc_js($login_url); ?>';

				  function dsShowLimitModal(message) {
    var overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;';
    var card = document.createElement('div');
    card.style.cssText = 'background:#fff;border-radius:20px;padding:40px 32px;max-width:400px;width:100%;text-align:center;box-shadow:0 8px 40px rgba(0,0,0,0.2);';
    card.innerHTML =
        '<div style="width:64px;height:64px;border-radius:50%;background:#FFF3E0;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:30px;">⏳</div>' +
        '<h3 style="font-size:21px;font-weight:700;color:#1a1a1a;margin:0 0 10px;">Download Limit Reached</h3>' +
        '<p style="font-size:14px;color:#666;line-height:1.6;margin:0 0 26px;">' + message + '</p>' +
        '<a href="' + window.PRICING + '" style="display:inline-block;background:#E8442A;color:#fff!important;text-decoration:none!important;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:700;font-family:inherit;margin-bottom:10px;width:100%;box-sizing:border-box;text-align:center;">🚀 Upgrade Now</a>' +
        '<a href="#" id="dsLimitClose" style="display:inline-block;background:#f5f5f5;color:#444!important;text-decoration:none!important;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:700;font-family:inherit;width:100%;box-sizing:border-box;text-align:center;">Maybe Later</a>';
    overlay.appendChild(card);
    document.body.appendChild(overlay);
    function close(e){ if(e) e.preventDefault(); overlay.remove(); }
    overlay.addEventListener('click', function(e){ if (e.target === overlay) close(); });
    card.querySelector('#dsLimitClose').addEventListener('click', close);
}
window.dsMktDoDownload = function(btn, postId, itemTier, actionUrl) {
    if (!window.LOGGED) { window.location.href = window.LOGIN; return; }
    if (window.USER_TIER === 'business') { window.location.href = actionUrl; return; }

    var limit = (itemTier === 'pro') ? window.DL_LIMIT_PRO : window.DL_LIMIT_FREE;
    if (limit === 0) { window.location.href = window.PRICING; return; }

    if (btn) { btn.style.opacity = '0.5'; btn.style.pointerEvents = 'none'; }

    fetch(window.DS_REST_URL, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.DS_NONCE },
        body: JSON.stringify({ post_id: postId, tier: itemTier })
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (res.ok) {
            if (btn) { btn.style.opacity = ''; btn.style.pointerEvents = ''; }
            window.location.href = actionUrl;
        } else {
            if (btn) { btn.style.opacity = ''; btn.style.pointerEvents = ''; }
            if (res.reason === 'limit_reached') {
                if (res.period === 'month') {
    dsShowLimitModal('You\'ve reached your monthly download limit (' + res.limit + '). It resets at the start of next month — or upgrade now for more downloads.');
} else {
    dsShowLimitModal('You\'ve reached your daily download limit (' + res.limit + '). It resets at midnight UTC — or upgrade now for more downloads.');
}
            } else if (res.reason === 'upgrade_required') {
                window.location.href = window.PRICING;
            }
        }
    })
    .catch(function(err) {
        console.log('dl-check network error:', err);
        if (btn) { btn.style.opacity = ''; btn.style.pointerEvents = ''; }
        // Don't blindly resend a mutating call — the first request may have
        // already succeeded server-side even though we failed to read the
        // response. Just let the user click Download again; dl-check itself
        // will report limit_reached if it truly already went through enough
        // times to matter.
        alert('Something went wrong starting your download. Please click Download again.');
    });
};

(function(){
function dsMktFmtTime(sec){ sec=Math.max(0,Math.floor(sec||0)); var m=Math.floor(sec/60), s=sec%60; return (m<10?'0':'')+m+':'+(s<10?'0':'')+s; }
function dsMktWaveformBars(seedStr){
  var seed=0; for (var i=0;i<seedStr.length;i++) seed=(seed*31+seedStr.charCodeAt(i))>>>0;
  function rnd(){ seed=(seed*1103515245+12345)>>>0; return (seed%1000)/1000; }
  var html=''; for (var b=0;b<90;b++){ html+='<b style="height:'+Math.round(6+rnd()*28)+'px"></b>'; }
  return html;
}
function dsMktPlayIcon(){ return '<svg width="13" height="13" viewBox="0 0 24 24" fill="#fff"><path d="M8 5v14l11-7z"/></svg>'; }
function dsMktPauseIcon(){ return '<svg width="13" height="13" viewBox="0 0 24 24" fill="#fff"><rect x="6" y="5" width="4" height="14"/><rect x="14" y="5" width="4" height="14"/></svg>'; }
function dsMktVolIcon(){ return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M4 9v6h4l5 5V4L8 9H4z" fill="currentColor"/><path d="M16.5 8.5a5 5 0 010 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M19 6a9 9 0 010 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>'; }
function dsMktVolMuteIcon(){ return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M4 9v6h4l5 5V4L8 9H4z" fill="currentColor"/><line x1="16" y1="9" x2="21" y2="15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="21" y1="9" x2="16" y2="15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>'; }
function dsMktPauseAllAudioRows(){
  document.querySelectorAll('.dsaaCard audio').forEach(function(a){ a.pause(); });
  document.querySelectorAll('.dsaaCard .dsaaThumbPlay').forEach(function(b){ b.innerHTML=dsMktPlayIcon(); });
  document.querySelectorAll('.dsaaCard .dsaaCardThumb').forEach(function(t){ t.classList.remove('dsaaSpinning'); });
}
function dsMktInitAudioRow(el){
  var audio = el.querySelector('audio');
  var thumbPlayBtn = el.querySelector('.dsaaThumbPlay');
  var thumbImg = el.querySelector('.dsaaCardThumb');
  var wave = el.querySelector('.dsaaWave');
  var waveCursor = el.querySelector('.dsaaWaveCursor');
  var timeEl = el.querySelector('.dsaaTime');
  var label = el.querySelector('.dsaaCardLabel');
  if (!audio || !thumbPlayBtn || !wave) return;
  wave.insertAdjacentHTML('afterbegin', dsMktWaveformBars(label ? label.textContent : String(Math.random())));
  var bars = wave.querySelectorAll('b');
  audio.addEventListener('loadedmetadata', function(){ if (!audio.currentTime) timeEl.textContent = dsMktFmtTime(audio.duration); });
  function setFill(pct){ var n=Math.round(pct*bars.length); bars.forEach(function(b,i){ b.classList.toggle('fill', i<n); }); }
  function dsMktRowToggle(){
    if (audio.paused) {
      dsMktPauseAllAudioRows();
      audio.play().catch(function(){});
      thumbPlayBtn.innerHTML = dsMktPauseIcon();
      if (thumbImg) thumbImg.classList.add('dsaaSpinning');
    } else {
      audio.pause();
      thumbPlayBtn.innerHTML = dsMktPlayIcon();
      if (thumbImg) thumbImg.classList.remove('dsaaSpinning');
    }
  }
  thumbPlayBtn.addEventListener('click', function(e){ e.stopPropagation(); dsMktRowToggle(); });
  audio.addEventListener('ended', function(){ thumbPlayBtn.innerHTML=dsMktPlayIcon(); if (thumbImg) thumbImg.classList.remove('dsaaSpinning'); setFill(0); timeEl.textContent = dsMktFmtTime(audio.duration); });
  audio.addEventListener('timeupdate', function(){
    var pct = audio.duration ? (audio.currentTime/audio.duration) : 0;
    setFill(pct);
    if (waveCursor) waveCursor.style.left = (pct*100)+'%';
    timeEl.textContent = dsMktFmtTime(audio.duration ? (audio.duration - audio.currentTime) : 0);
  });
  function seekFromClientX(clientX){
    if (!audio.duration) return;
    var r = wave.getBoundingClientRect();
    var pct = Math.min(1, Math.max(0, (clientX-r.left)/r.width));
    audio.currentTime = pct*audio.duration;
    setFill(pct);
    if (waveCursor) waveCursor.style.left = (pct*100)+'%';
    timeEl.textContent = dsMktFmtTime(audio.duration - audio.currentTime);
  }
  var dragging=false;
  wave.addEventListener('mousedown', function(e){ if (!audio.duration) return; dragging=true; seekFromClientX(e.clientX); e.preventDefault(); e.stopPropagation(); });
  document.addEventListener('mousemove', function(e){ if (dragging) seekFromClientX(e.clientX); });
  document.addEventListener('mouseup', function(){ dragging=false; });
  wave.addEventListener('touchstart', function(e){ if (!audio.duration) return; dragging=true; seekFromClientX(e.touches[0].clientX); }, {passive:true});
  wave.addEventListener('touchmove', function(e){ if (dragging) seekFromClientX(e.touches[0].clientX); }, {passive:true});
  wave.addEventListener('touchend', function(){ dragging=false; });
  var volSlider = el.querySelector('.dsaaVolSlider');
  var volIcon = el.querySelector('.dsaaVolIcon');
  if (volSlider && volIcon) {
    audio.volume = 1;
    volSlider.addEventListener('click', function(e){ e.stopPropagation(); });
    volSlider.addEventListener('input', function(){ audio.volume = parseFloat(volSlider.value); volIcon.innerHTML = audio.volume===0 ? dsMktVolMuteIcon() : dsMktVolIcon(); });
    volIcon.addEventListener('click', function(e){
      e.stopPropagation();
      if (audio.volume > 0) { volSlider.dataset.prev = audio.volume; audio.volume = 0; volSlider.value = 0; }
      else { audio.volume = parseFloat(volSlider.dataset.prev || '1'); volSlider.value = audio.volume; }
      volIcon.innerHTML = audio.volume===0 ? dsMktVolMuteIcon() : dsMktVolIcon();
    });
  }
}
function dsMktInitAllAudioRows(){
  document.querySelectorAll('.dsaaCard').forEach(function(el){
    if (el.dataset.dsaaInit) return;
    el.dataset.dsaaInit = '1';
    dsMktInitAudioRow(el);
  });
}
if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', dsMktInitAllAudioRows); } else { dsMktInitAllAudioRows(); }
})();

</script>
<?php return ob_get_clean();
});


/* ===== DS Marketplace video posters (added 2026-09-23) =====
 * Cards show a small poster image; the video loads/plays only on hover.
 * Tools -> Video Posters generates the missing posters in the admin's browser. */
if ( ! function_exists( 'ds_mkt_poster_url' ) ) {
	function ds_mkt_poster_url( $att_id ) {
		$u = $att_id ? get_post_meta( (int) $att_id, '_ds_video_poster', true ) : '';
		return is_string( $u ) ? $u : '';
	}
}

if ( ! function_exists( 'ds_mkt_poster_missing_ids' ) ) {
	function ds_mkt_poster_missing_ids() {
		global $wpdb;
		$sql = "SELECT DISTINCT a.ID FROM {$wpdb->posts} a
			LEFT JOIN {$wpdb->posts} p ON p.ID = a.post_parent
			WHERE a.post_type = 'attachment' AND a.post_mime_type LIKE 'video/%'
			AND ( p.post_type = 'dlm_download' OR a.ID IN (
				SELECT CAST( pm.meta_value AS UNSIGNED ) FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} d ON d.ID = pm.post_id AND d.post_type = 'dlm_download'
				WHERE pm.meta_key = '_thumbnail_id' ) )
			AND NOT EXISTS ( SELECT 1 FROM {$wpdb->postmeta} x WHERE x.post_id = a.ID AND x.meta_key = '_ds_video_poster' )
			ORDER BY a.ID DESC";
		return array_map( 'intval', (array) $wpdb->get_col( $sql ) );
	}
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'ds/v1', '/poster-queue', array(
		'methods'             => 'GET',
		'permission_callback' => function () { return current_user_can( 'manage_options' ); },
		'callback'            => function ( WP_REST_Request $r ) {
			$skip  = array_filter( array_map( 'intval', explode( ',', (string) $r->get_param( 'skip' ) ) ) );
			$ids   = array_values( array_diff( ds_mkt_poster_missing_ids(), $skip ) );
			$items = array();
			foreach ( array_slice( $ids, 0, 10 ) as $id ) {
				$items[] = array( 'id' => $id, 'url' => wp_get_attachment_url( $id ) );
			}
			return array( 'remaining' => count( $ids ), 'items' => $items );
		},
	) );
	register_rest_route( 'ds/v1', '/poster-save', array(
		'methods'             => 'POST',
		'permission_callback' => function () { return current_user_can( 'manage_options' ); },
		'callback'            => function ( WP_REST_Request $r ) {
			$id   = (int) $r->get_param( 'id' );
			$data = (string) $r->get_param( 'data' );
			if ( ! $id || get_post_type( $id ) !== 'attachment' ) {
				return new WP_Error( 'bad_id', 'Bad attachment id', array( 'status' => 400 ) );
			}
			if ( ! preg_match( '#^data:image/(webp|jpeg);base64,#', $data, $m ) ) {
				return new WP_Error( 'bad_data', 'Bad image data', array( 'status' => 400 ) );
			}
			$bin = base64_decode( substr( $data, strpos( $data, ',' ) + 1 ), true );
			if ( ! $bin || strlen( $bin ) < 500 || strlen( $bin ) > 3000000 ) {
				return new WP_Error( 'bad_size', 'Bad image size', array( 'status' => 400 ) );
			}
			$ext = ( 'jpeg' === $m[1] ) ? 'jpg' : 'webp';
			$up  = wp_upload_dir( null, false );
			$dir = trailingslashit( $up['basedir'] ) . 'ds-posters';
			if ( ! wp_mkdir_p( $dir ) ) {
				return new WP_Error( 'mkdir', 'Cannot create uploads/ds-posters', array( 'status' => 500 ) );
			}
			$name = 'poster-' . $id . '.' . $ext;
			if ( false === file_put_contents( $dir . '/' . $name, $bin ) ) {
				return new WP_Error( 'write', 'Cannot write poster file', array( 'status' => 500 ) );
			}
			$url = trailingslashit( $up['baseurl'] ) . 'ds-posters/' . $name . '?v=' . time();
			update_post_meta( $id, '_ds_video_poster', esc_url_raw( $url ) );
			return array( 'ok' => true, 'url' => $url );
		},
	) );
} );

add_action( 'admin_menu', function () {
	add_management_page( 'Video Posters', 'Video Posters', 'manage_options', 'ds-video-posters', 'ds_mkt_video_posters_page' );
} );

if ( ! function_exists( 'ds_mkt_video_posters_page' ) ) {
	function ds_mkt_video_posters_page() {
		$missing = count( ds_mkt_poster_missing_ids() );
		?>
		<div class="wrap">
			<h1>Marketplace Video Posters</h1>
			<p>Creates a small preview image for each marketplace video, so the Marketplace loads fast and videos play only when a visitor hovers a card.</p>
			<p><strong><?php echo (int) $missing; ?></strong> video(s) still need a poster. Run this again after uploading new videos. Keep this tab open until it finishes.</p>
			<p><button type="button" class="button button-primary" id="dsvpStart">Generate missing posters</button> <span id="dsvpStatus" style="margin-left:8px"></span></p>
			<ol id="dsvpLog" style="max-height:360px;overflow:auto;background:#fff;border:1px solid #ddd;padding:8px 8px 8px 30px"></ol>
		</div>
		<script>
		(function(){
			var REST  = <?php echo wp_json_encode( esc_url_raw( rest_url( 'ds/v1/' ) ) ); ?>;
			var NONCE = <?php echo wp_json_encode( wp_create_nonce( 'wp_rest' ) ); ?>;
			var btn = document.getElementById('dsvpStart'), st = document.getElementById('dsvpStatus'), log = document.getElementById('dsvpLog');
			var skip = [], done = 0, failed = 0, running = false;
			function line(t){ var li = document.createElement('li'); li.textContent = t; log.appendChild(li); log.scrollTop = log.scrollHeight; }
			function api(path, opt){
				opt = opt || {}; opt.credentials = 'same-origin';
				opt.headers = Object.assign({ 'X-WP-Nonce': NONCE }, opt.headers || {});
				return fetch(REST + path, opt).then(function(r){ return r.json().then(function(j){ if (!r.ok) throw new Error(j.message || ('HTTP ' + r.status)); return j; }); });
			}
			function grab(url){
				return new Promise(function(resolve, reject){
					var v = document.createElement('video');
					v.muted = true; v.preload = 'auto'; v.playsInline = true; v.crossOrigin = 'anonymous';
					var timer = setTimeout(function(){ finish(); reject(new Error('timeout')); }, 60000);
					function finish(){ clearTimeout(timer); v.removeAttribute('src'); try { v.load(); } catch(e){} }
					v.addEventListener('error', function(){ finish(); reject(new Error('video could not be loaded')); });
					v.addEventListener('loadedmetadata', function(){
						var t = (isFinite(v.duration) && v.duration > 0) ? Math.min(1, v.duration * 0.1) : 0.1;
						try { v.currentTime = t; } catch(e){ finish(); reject(e); }
					});
					v.addEventListener('seeked', function(){
						try {
							var vw = v.videoWidth || 640, vh = v.videoHeight || 360;
							var w = Math.min(640, vw), h = Math.round(w * vh / vw);
							var c = document.createElement('canvas'); c.width = w; c.height = h;
							c.getContext('2d').drawImage(v, 0, 0, w, h);
							var d = c.toDataURL('image/webp', 0.72);
							if (d.indexOf('data:image/webp') !== 0) d = c.toDataURL('image/jpeg', 0.75);
							finish(); resolve(d);
						} catch(e){ finish(); reject(e); }
					}, { once: true });
					v.src = url;
				});
			}
			function batch(){
				return api('poster-queue?skip=' + skip.join(',')).then(function(q){
					st.textContent = 'Created ' + done + ', failed ' + failed + ', remaining ' + q.remaining;
					if (!q.items.length) {
						st.textContent = 'Finished. Created ' + done + ' poster(s)' + (failed ? ', ' + failed + ' failed (see list).' : '.');
						running = false; btn.disabled = false; return;
					}
					return q.items.reduce(function(p, it){
						return p.then(function(){
							return grab(it.url).then(function(d){
								return api('poster-save', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: it.id, data: d }) });
							}).then(function(){ done++; line('OK  #' + it.id); })
							  .catch(function(e){ failed++; skip.push(it.id); line('FAILED  #' + it.id + ' - ' + e.message); });
						});
					}, Promise.resolve()).then(batch);
				});
			}
			btn.addEventListener('click', function(){
				if (running) return; running = true; btn.disabled = true; line('Starting...');
				batch().catch(function(e){ st.textContent = 'Error: ' + e.message; running = false; btn.disabled = false; });
			});
		})();
		</script>
		<?php
	}
}

if ( ! function_exists( 'ds_mkt_quality_label' ) ) {
	/** "2048x1152 (16:9)" -> "2K" (HD, FHD, 2K, 3K, 4K). Leaves anything else unchanged. */
	function ds_mkt_quality_label( $res ) {
		if ( ! is_string( $res ) || ! preg_match( '/(\d{3,5})\s*[x×X]\s*(\d{3,5})/u', $res, $m ) ) return $res;
		$long = max( (int) $m[1], (int) $m[2] );
		if ( $long >= 3840 ) return '4K';
		if ( $long >= 2880 ) return '3K';
		if ( $long >= 2048 ) return '2K';
		if ( $long >= 1920 ) return 'FHD';
		if ( $long >= 1280 ) return 'HD';
		return 'SD';
	}
}
