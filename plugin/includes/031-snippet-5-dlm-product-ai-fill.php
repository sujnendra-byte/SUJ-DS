<?php
/**
 * Code Snippets export — Snippet 5 dlm product ai fill
 *
 * Snippet ID : 717
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-031
 * Modified   : 2026-09-09 02:44:35
 * Load order : 031 of 144
 */
/**
 * DS Creator Portal — Snippet 5: AI Auto-Fill on DLM Product Edit Screen
 * WHERE TO ADD: New snippet, Run: Everywhere. Add AFTER Snippets 1-4.
 *
 * WHAT THIS DOES:
 *  - Adds an "AI Auto-Fill" meta box directly on the dlm_download (DLM
 *    product) edit screen — same image-preview + Auto-Fill flow as the
 *    Creator Uploads approval popup, but for editing a product that's
 *    already published, without going back through approval.
 *  - Reuses ds_ai_analyze_image_handler() from the Admin-Ajax snippet —
 *    no second copy of the Gemini-calling code.
 *  - Reuses ds_get_category_tree() if it's already defined by another
 *    snippet; falls back to its own copy if not.
 *  - "Apply to This Product" writes straight to the real fields: title,
 *    content, category/subcategory terms, tags, and AIOSEO title/
 *    description/keyphrase (same column structure already verified
 *    working in Snippet 4).
 */

if (!function_exists('ds_get_category_tree')) {
function ds_get_category_tree() {
    $tree = array();
    $marketplace = get_term_by('name', 'Marketplace', 'dlm_download_category');
    if (!$marketplace || is_wp_error($marketplace)) return $tree;

    $cats = get_terms(array(
        'taxonomy'   => 'dlm_download_category',
        'hide_empty' => false,
        'parent'     => $marketplace->term_id,
    ));
    if (is_wp_error($cats)) return $tree;

    foreach ($cats as $cat) {
        $subs = get_terms(array(
            'taxonomy'   => 'dlm_download_category',
            'hide_empty' => false,
            'parent'     => $cat->term_id,
        ));
        $sub_list = array();
        if (!is_wp_error($subs)) {
            foreach ($subs as $sub) {
                $sub_list[$sub->term_id] = wp_specialchars_decode($sub->name, ENT_QUOTES);
            }
        }
        $tree[$cat->term_id] = array('name' => wp_specialchars_decode($cat->name, ENT_QUOTES), 'subs' => $sub_list);
    }
    return $tree;
}
}

// ── META BOX ──────────────────────────────────────────
add_action('add_meta_boxes', function () {
    add_meta_box(
        'ds_dlm_ai_fill_box',
        '✨ AI Auto-Fill',
        'ds_dlm_ai_fill_box_render',
        'dlm_download',
        'normal',
        'high'
    );
});

function ds_dlm_ai_fill_box_render($post) {
    if (!current_user_can('manage_options')) {
        echo '<p>You do not have permission to use this.</p>';
        return;
    }

    $image_url = get_the_post_thumbnail_url($post->ID, 'large') ?: '';
    $tree = ds_get_category_tree();

    // Figure out which already-assigned term is the top-level category vs the
    // subcategory, so both dropdowns start correctly populated on an existing product.
    $current_cat_id = 0;
    $current_sub_id = 0;
    $existing_terms = wp_get_post_terms($post->ID, 'dlm_download_category', array('fields' => 'ids'));
    if (!is_wp_error($existing_terms)) {
        foreach ($existing_terms as $term_id) {
            if (isset($tree[$term_id])) {
                $current_cat_id = $term_id; // it's a top-level category
            }
        }
        foreach ($existing_terms as $term_id) {
            foreach ($tree as $cat_id => $cat) {
                if (isset($cat['subs'][$term_id])) {
                    $current_sub_id = $term_id; // it's a subcategory
                    if (!$current_cat_id) $current_cat_id = $cat_id;
                }
            }
        }
    }
    ?>
    <div style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap">
      <div style="flex:1;min-width:300px">
        <input type="hidden" id="ds-dlm-post-id" value="<?php echo esc_attr($post->ID); ?>">
        <button type="button" id="ds-dlm-ai-fill-btn" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border:none;padding:10px 16px;border-radius:6px;font-weight:600;cursor:pointer;font-size:13px;width:100%;margin-bottom:14px;">✨ Auto-Fill with AI</button>
        <div id="ds-dlm-ai-status" style="display:none;margin:0 0 14px;padding:10px 12px;border-radius:4px;font-size:12px;"></div>

        <table class="form-table" style="font-size:13px;margin:0">
          <tr><th style="width:140px;padding:6px 0">Title</th>
            <td style="padding:6px 0"><input type="text" id="ds-dlm-title" value="<?php echo esc_attr($post->post_title); ?>" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px"></td></tr>
          <tr><th style="padding:6px 0">Category</th>
            <td style="padding:6px 0">
              <select id="ds-dlm-cat" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px;background:#fff;">
                <option value="">— Select category —</option>
                <?php foreach ($tree as $cat_id => $cat) {
                    $sel = ($cat_id == $current_cat_id) ? ' selected' : '';
                    echo '<option value="' . esc_attr($cat_id) . '"' . $sel . '>' . esc_html($cat['name']) . '</option>';
                } ?>
              </select>
            </td></tr>
          <tr><th style="padding:6px 0">Subcategory</th>
            <td style="padding:6px 0">
              <select id="ds-dlm-subcat" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px;background:#fff;">
                <?php if ($current_cat_id && !empty($tree[$current_cat_id]['subs'])) : ?>
                  <option value="">— Select subcategory —</option>
                  <?php foreach ($tree[$current_cat_id]['subs'] as $sub_id => $sub_name) {
                      $sel = ($sub_id == $current_sub_id) ? ' selected' : '';
                      echo '<option value="' . esc_attr($sub_id) . '"' . $sel . '>' . esc_html($sub_name) . '</option>';
                  } ?>
                <?php else : ?>
                  <option value="">— Select category first —</option>
                <?php endif; ?>
              </select>
            </td></tr>
          <tr><th style="padding:6px 0">Short Description</th>
            <td style="padding:6px 0"><input type="text" id="ds-dlm-short" value="<?php echo esc_attr($post->post_excerpt); ?>" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px"></td></tr>
          <tr><th style="padding:6px 0">Meta Description</th>
            <td style="padding:6px 0"><textarea id="ds-dlm-meta" rows="2" maxlength="160" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px;resize:vertical"></textarea></td></tr>
          <tr><th style="padding:6px 0">Full Description</th>
            <td style="padding:6px 0"><textarea id="ds-dlm-long" rows="4" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px;resize:vertical"><?php echo esc_textarea($post->post_content); ?></textarea></td></tr>
          <tr><th style="padding:6px 0">Keywords</th>
            <td style="padding:6px 0"><input type="text" id="ds-dlm-kw" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px" placeholder="Comma separated"></td></tr>
          <tr><th style="padding:6px 0">Tags</th>
            <td style="padding:6px 0"><input type="text" id="ds-dlm-tags" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px" placeholder="Comma separated"></td></tr>
        </table>

        <button type="button" id="ds-dlm-apply-btn" style="margin-top:14px;padding:10px 24px;border-radius:8px;border:none;background:#10b981;color:#fff;cursor:pointer;font-weight:700;font-size:13px">✅ Apply to This Product</button>
      </div>

      <div style="flex:0 0 260px;min-width:220px">
        <p style="margin:0 0 8px;font-weight:600;font-size:13px;color:#444">Product Image</p>
        <?php if ($image_url) : ?>
          <img id="ds-dlm-preview-img" data-image-url="<?php echo esc_url($image_url); ?>" src="<?php echo esc_url($image_url); ?>" alt="Preview" style="width:100%;border-radius:8px;border:1px solid #ddd;display:block;object-fit:contain;max-height:480px;background:#f5f5f5;">
        <?php else : ?>
          <img id="ds-dlm-preview-img" data-image-url="" src="" alt="" style="display:none;">
          <p style="font-size:12px;color:#999;border:1px dashed #ddd;border-radius:8px;padding:16px;text-align:center;margin:0;">No featured image set on this product yet — set one first, then Auto-Fill can read it.</p>
        <?php endif; ?>
      </div>
    </div>

			<!-- Template Matching Fields -->
<label style="display:block;margin-top:14px;font-weight:600;">
  <input type="checkbox" id="ds-dlm-show-template" style="width:18px;height:18px;margin-right:6px;"/> 
  Show in AI Studio "Pick Design" matches
</label>

<label style="display:block;margin:10px 0 4px;">Content Shape:</label>
<input type="text" id="ds-dlm-content-shape" placeholder="e.g., steps_4, timeline, swot, pros_c" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;"/>

<label style="display:block;margin:10px 0 4px;">
  <input type="checkbox" id="ds-dlm-semantic" style="width:18px;height:18px;margin-right:6px;"/> 
  Semantic (has fixed meaningful labels like STRENGTHS/WEAKNESSES)
</label>

<label style="display:block;margin:10px 0 4px;">Template Keywords:</label>
<input type="text" id="ds-dlm-template-kw" placeholder="e.g., strength, weakness, opportunity, threat" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;"/>
	
    <script>
    window.dsCategoryTree = window.dsCategoryTree || <?php echo wp_json_encode($tree); ?>;

    function dsDlmPopulateSubcategories(selectedCatId, preselectSubId) {
        var subSelect = document.getElementById('ds-dlm-subcat');
        if (!subSelect) return;
        var tree = window.dsCategoryTree || {};
        var subs = (tree[selectedCatId] && tree[selectedCatId].subs) ? tree[selectedCatId].subs : {};
        var keys = Object.keys(subs);
        if (!keys.length) {
            subSelect.innerHTML = '<option value="">— No subcategories for this category —</option>';
            return;
        }
        subSelect.innerHTML = '<option value="">— Select subcategory —</option>';
        keys.forEach(function(subId) {
            var opt = document.createElement('option');
            opt.value = subId;
            opt.textContent = subs[subId];
            subSelect.appendChild(opt);
        });
        if (preselectSubId) subSelect.value = preselectSubId;
    }

    document.getElementById('ds-dlm-cat').addEventListener('change', function() {
        dsDlmPopulateSubcategories(this.value, null);
    });

    function dsDlmApplySubcategory(subName) {
        var tree = window.dsCategoryTree || {};
        var catId = document.getElementById('ds-dlm-cat').value;
        if (!catId || !tree[catId] || !subName) return;
        var subs = tree[catId].subs || {};
        var matchedSubId = null;
        Object.keys(subs).forEach(function(id) {
            if (subs[id].toLowerCase() === subName.toLowerCase()) matchedSubId = id;
        });
        if (matchedSubId) document.getElementById('ds-dlm-subcat').value = matchedSubId;
    }

    document.getElementById('ds-dlm-ai-fill-btn').addEventListener('click', function() {
        var btn = this;
        var status = document.getElementById('ds-dlm-ai-status');
        var imgEl = document.getElementById('ds-dlm-preview-img');
        var imageUrl = imgEl ? imgEl.getAttribute('data-image-url') : '';

        function showStatus(msg, ok) {
            status.textContent = msg;
            status.style.display = 'block';
            status.style.background = ok ? '#ecfdf5' : '#fee2e2';
            status.style.color      = ok ? '#065f46' : '#991b1b';
        }

        if (!imageUrl) {
            showStatus('No featured image on this product — set one first.', false);
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Analyzing…';

        var form = new FormData();
        form.append('action', 'ds_ai_analyze_image');
        form.append('image_url', imageUrl);

        // Derive asset_type from the category already selected on this product —
        // it's already known, no need to re-detect from a file here.
        var tree = window.dsCategoryTree || {};
        var catId = document.getElementById('ds-dlm-cat').value;
        var catName = (tree[catId] && tree[catId].name) ? tree[catId].name.toLowerCase() : '';
        var assetType = 'images';
        if (catName === 'motion graphics') assetType = 'motion_graphics';
        else if (catName === 'powerpoint templates') assetType = 'powerpoint';
        form.append('asset_type', assetType);

        // If no category is selected yet, fall back to whichever one matches the
        // asset_type we're sending — so the dropdown still has something to select
        // once the AI responds, instead of staying blank.
        var fallbackCatId = catId;
        if (!fallbackCatId) {
            var nameMap = { images: 'images', motion_graphics: 'motion graphics', powerpoint: 'powerpoint templates' };
            Object.keys(tree).forEach(function(id) {
                if (tree[id].name.toLowerCase() === nameMap[assetType]) fallbackCatId = id;
            });
        }

        fetch(ajaxurl, { method: 'POST', body: form })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                btn.disabled = false;
                btn.textContent = '✨ Auto-Fill with AI';
                if (!d.success) {
                    showStatus('Error: ' + (d.data || 'AI analysis failed'), false);
                    return;
                }
                var a = d.data;
                document.getElementById('ds-dlm-title').value = a.title || '';
                if (!document.getElementById('ds-dlm-cat').value && fallbackCatId) {
                    document.getElementById('ds-dlm-cat').value = fallbackCatId;
                    dsDlmPopulateSubcategories(fallbackCatId, null);
                }
                dsDlmApplySubcategory(a.subcategory);
                document.getElementById('ds-dlm-short').value = a.short_description || '';
                document.getElementById('ds-dlm-meta').value  = a.meta_description || '';
                document.getElementById('ds-dlm-long').value  = a.full_description || '';
                document.getElementById('ds-dlm-kw').value    = a.keywords || '';
                document.getElementById('ds-dlm-tags').value  = a.tags || '';
				// Template fields
if (a.content_shape) document.getElementById('ds-dlm-content-shape').value = a.content_shape;
if (a.semantic) document.getElementById('ds-dlm-semantic').checked = true;
if (a.template_keywords) document.getElementById('ds-dlm-template-kw').value = a.template_keywords;
if (a.show_in_template_picker) document.getElementById('ds-dlm-show-template').checked = true;
                showStatus('✅ Filled in — review, then click "Apply to This Product".', true);
            })
            .catch(function(err) {
                btn.disabled = false;
                btn.textContent = '✨ Auto-Fill with AI';
                showStatus('Error: ' + (err && err.message ? err.message : 'Network error.'), false);
            });
    });

    document.getElementById('ds-dlm-apply-btn').addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.textContent = 'Saving…';

        var data = new URLSearchParams();
        data.append('action',         'ds_dlm_apply_ai_fill');
        data.append('nonce',          '<?php echo wp_create_nonce("ds_dlm_ai_fill_nonce"); ?>');
        data.append('post_id',        document.getElementById('ds-dlm-post-id').value);
        data.append('title',          document.getElementById('ds-dlm-title').value);
        data.append('category_id',    document.getElementById('ds-dlm-cat').value);
        data.append('subcategory_id', document.getElementById('ds-dlm-subcat').value);
        data.append('shortDesc',      document.getElementById('ds-dlm-short').value);
        data.append('metaDesc',       document.getElementById('ds-dlm-meta').value);
        data.append('longDesc',       document.getElementById('ds-dlm-long').value);
        data.append('keywords',       document.getElementById('ds-dlm-kw').value);
        data.append('tags',           document.getElementById('ds-dlm-tags').value);
		data.append('content_shape',        document.getElementById('ds-dlm-content-shape').value);
data.append('semantic',             document.getElementById('ds-dlm-semantic').checked ? '1' : '0');
data.append('template_keywords',    document.getElementById('ds-dlm-template-kw').value);
data.append('show_in_template_picker', document.getElementById('ds-dlm-show-template').checked ? '1' : '0');

        fetch(ajaxurl, { method: 'POST', body: data })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                btn.disabled = false;
                btn.textContent = '✅ Apply to This Product';
                if (d.success) {
                    alert('Saved! Reloading…');
                    window.location.reload();
                } else {
                    alert('Error: ' + (d.data || 'Could not save'));
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.textContent = '✅ Apply to This Product';
                alert('Network error. Please try again.');
            });
    });
    </script>
    <?php
}

// ── AJAX: APPLY AI FILL DIRECTLY TO THIS DLM PRODUCT ───────────
add_action('wp_ajax_ds_dlm_apply_ai_fill', 'ds_dlm_apply_ai_fill_handler');
function ds_dlm_apply_ai_fill_handler() {
    check_ajax_referer('ds_dlm_ai_fill_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Not allowed');

    $post_id = (int) ($_POST['post_id'] ?? 0);
    if (!$post_id || get_post_type($post_id) !== 'dlm_download') {
        wp_send_json_error('Invalid product');
    }

    $title          = sanitize_text_field($_POST['title'] ?? '');
    $category_id    = (int) ($_POST['category_id'] ?? 0);
    $subcategory_id = (int) ($_POST['subcategory_id'] ?? 0);
    $short_desc     = sanitize_text_field($_POST['shortDesc'] ?? '');
    $meta_desc      = sanitize_textarea_field($_POST['metaDesc'] ?? '');
    $long_desc      = sanitize_textarea_field($_POST['longDesc'] ?? '');
    $keywords       = array_values(array_filter(array_map('trim', explode(',', sanitize_text_field($_POST['keywords'] ?? '')))));
	// Template matching fields
$content_shape         = sanitize_text_field($_POST['content_shape'] ?? '');
$semantic              = (int)($_POST['semantic'] ?? 0) ? 1 : 0;
$template_keywords     = sanitize_text_field($_POST['template_keywords'] ?? '');
$show_in_template_picker = (int)($_POST['show_in_template_picker'] ?? 0) ? 1 : 0;
    $tags           = array_values(array_filter(array_map('trim', explode(',', sanitize_text_field($_POST['tags'] ?? '')))));

    // ── Update title/content/excerpt ──
    $post_content = '';
    if ($short_desc) $post_content .= '<p>' . wp_kses_post($short_desc) . '</p>';
    if ($long_desc)  $post_content .= wp_kses_post($long_desc);

    $update = array('ID' => $post_id, 'post_content' => $post_content, 'post_excerpt' => $short_desc);
    if ($title) $update['post_title'] = $title;
    wp_update_post($update);
	// Save template fields as post meta
update_post_meta($post_id, 'ds_content_shape', $content_shape);
update_post_meta($post_id, 'ds_semantic', $semantic);
update_post_meta($post_id, 'ds_template_keywords', $template_keywords);
update_post_meta($post_id, 'ds_show_in_template_picker', $show_in_template_picker);

    // ── Category/subcategory: real term IDs only, same safeguard as Snippet 4 ──
    $term_ids = array();
    if ($category_id    && term_exists($category_id,    'dlm_download_category')) $term_ids[] = $category_id;
    if ($subcategory_id && term_exists($subcategory_id, 'dlm_download_category')) $term_ids[] = $subcategory_id;
    if ($term_ids) wp_set_object_terms($post_id, $term_ids, 'dlm_download_category');

    // ── Tags ──
    $all_tags = array_merge($keywords, $tags);
    if ($all_tags) {
        $tag_taxonomy = taxonomy_exists('dlm_download_tag') ? 'dlm_download_tag' : 'post_tag';
        wp_set_object_terms($post_id, $all_tags, $tag_taxonomy);
    }

    // ── AIOSEO title/description/keyphrase — same column structure as Snippet 4 ──
    $focus_kw = !empty($keywords) ? $keywords[0] : '';
    if ($meta_desc || $focus_kw || $title) {
        global $wpdb;
        $aioseo_table = $wpdb->prefix . 'aioseo_posts';
        if ($wpdb->get_var("SHOW TABLES LIKE '$aioseo_table'") === $aioseo_table) {
            $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $aioseo_table WHERE post_id = %d", $post_id));
            if ($existing) {
                $wpdb->update(
                    $aioseo_table,
                    array(
                        'title'       => $title,
                        'description' => $meta_desc,
                        'keyphrases'  => $focus_kw ? wp_json_encode(array('focus' => array('keyphrase' => $focus_kw, 'score' => 0, 'analysis' => array()))) : null,
                        'updated'     => current_time('mysql'),
                    ),
                    array('post_id' => $post_id),
                    array('%s', '%s', '%s', '%s'),
                    array('%d')
                );
            } else {
                $wpdb->insert(
                    $aioseo_table,
                    array(
                        'post_id'     => $post_id,
                        'title'       => $title,
                        'description' => $meta_desc,
                        'keyphrases'  => $focus_kw ? wp_json_encode(array('focus' => array('keyphrase' => $focus_kw, 'score' => 0, 'analysis' => array()))) : null,
                        'created'     => current_time('mysql'),
                        'updated'     => current_time('mysql'),
                    ),
                    array('%d', '%s', '%s', '%s', '%s', '%s')
                );
            }
        }
    }

    wp_send_json_success(array('message' => 'Applied'));
}
