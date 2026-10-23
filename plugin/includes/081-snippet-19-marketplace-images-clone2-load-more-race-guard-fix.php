<?php
/**
 * Code Snippets export — Snippet 19 marketplace images [CLONE2] [Load More + Race Guard Fix]
 *
 * Snippet ID : 764
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-078
 * Modified   : 2026-09-09 02:44:08
 * Load order : 081 of 144
 */
/**
 * Snippet 19: DS PPT Marketplace Image Picker
 * Run: Everywhere
 * NEW SNIPPET — does NOT modify any existing snippet
 *
 * Adds a REST endpoint to return marketplace images (dlm_download posts),
 * and injects a "Browse My Store" button into the PPT image gen popup.
 *
 * When user clicks "Browse My Store" → marketplace image grid appears →
 * user clicks an image → it gets used in their slide.
 *
 * v2 changes:
 *  - Only real images are listed (PowerPoint Templates and any other
 *    non-image product type are excluded — picking a slide's design
 *    thumbnail into a different slide as an "image" made no sense).
 *  - Free/Pro badges shown on each card, matching the public marketplace.
 *  - Picked images now use the full-resolution asset (still watermarked
 *    at edit time — see ds_mkt_get_unwatermarked_file_bytes() below for
 *    why) instead of the small grid thumbnail, so editing looks sharp.
 *  - Grid is a true masonry layout (CSS columns) so portrait images no
 *    longer leave a blank gap under their shorter row-mates.
 *  - Each picked slide is tagged with image_market_id / image_market_tier.
 *    Nothing is deducted from the user's free/pro quota at pick time —
 *    only at actual PPTX download (see the 12B / Snippet 16 patches that
 *    go with this snippet), which also swaps in the real, unwatermarked
 *    original in place of the editor's watermarked preview.
 */

if ( ! defined('ABSPATH') ) exit;

// ─── REST endpoint: return marketplace images ────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/mkt-slide-images', array(
        'methods'             => 'GET',
        'callback'            => 'ds_ppt_mkt_images',
        'permission_callback' => function () { return is_user_logged_in(); },
    ));
});

if ( ! function_exists('ds_ppt_mkt_get_excluded_term_ids') ) {
/**
 * Non-image product categories (PowerPoint Templates today; add more
 * names here if other non-image product types get their own category)
 * resolved dynamically by name so this keeps working regardless of slug.
 */
function ds_ppt_mkt_get_excluded_term_ids() {
    $names = array('PowerPoint Templates');
    $ids   = array();
    foreach ( $names as $name ) {
        $t = get_term_by('name', $name, 'dlm_download_category');
        if ( $t && ! is_wp_error($t) ) $ids[] = (int) $t->term_id;
    }
    return $ids;
}
}

if ( ! function_exists('ds_ppt_mkt_item_tier') ) {
/**
 * Same free/pro detection as the public marketplace shortcode: a
 * dlm_download_tag with slug "pro" marks it Pro, otherwise it's Free.
 */
function ds_ppt_mkt_item_tier( $post_id ) {
    $tags      = get_the_terms($post_id, 'dlm_download_tag') ?: array();
    $tag_slugs = is_array($tags) ? array_map(function ($t) { return strtolower($t->slug); }, $tags) : array();
    return in_array('pro', $tag_slugs, true) ? 'pro' : 'free';
}
}

if ( ! function_exists('ds_ppt_mkt_images') ) {
function ds_ppt_mkt_images( WP_REST_Request $req ) {
    $search   = sanitize_text_field($req->get_param('s') ?: '');
    $cat      = sanitize_text_field($req->get_param('cat') ?: '');
    $per_page = min(100, max(12, intval($req->get_param('per_page') ?: 24)));
    $page     = max(1, intval($req->get_param('page') ?: 1));

    $exclude_ids = ds_ppt_mkt_get_excluded_term_ids();

    $args = array(
        'post_type'      => 'dlm_download',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $tax_query = array();
    if ( ! empty($cat) ) {
        $tax_query[] = array('taxonomy' => 'dlm_download_category', 'field' => 'slug', 'terms' => $cat);
    }
    if ( ! empty($exclude_ids) ) {
        $tax_query[] = array('taxonomy' => 'dlm_download_category', 'field' => 'term_id', 'terms' => $exclude_ids, 'operator' => 'NOT IN');
    }
    if ( ! empty($tax_query) ) {
        $args['tax_query'] = (count($tax_query) > 1) ? array_merge(array('relation' => 'AND'), $tax_query) : $tax_query;
    }

    if ( ! empty($search) ) {
        $args['s'] = $search;
    }

    $q     = new WP_Query($args);
    $items = array();

    foreach ( $q->posts as $post ) {
        $thumb_id = get_post_thumbnail_id($post->ID);
        $thumb    = '';
        $full     = '';

        if ( $thumb_id && function_exists('ds_mkt_get_grid_url') ) {
            $thumb = ds_mkt_get_grid_url($thumb_id);
        } elseif ( $thumb_id ) {
            $thumb = wp_get_attachment_image_url($thumb_id, 'medium') ?: wp_get_attachment_url($thumb_id);
        }

        if ( ! $thumb ) {
            // No featured image — fall back to the first attached image.
            $att = get_posts(array(
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'post_parent'    => $post->ID,
                'posts_per_page' => 1,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            ));
            if ( $att ) {
                $thumb_id = $att[0]->ID;
                $thumb    = function_exists('ds_mkt_get_grid_url')
                    ? ds_mkt_get_grid_url($thumb_id)
                    : (wp_get_attachment_image_url($thumb_id, 'medium') ?: wp_get_attachment_url($thumb_id));
            }
        }

        if ( ! $thumb ) continue; // nothing usable to show for this item

        // Full-resolution asset used when actually inserted into a slide.
        // Still the watermarked copy at this stage — see the note on
        // ds_mkt_get_unwatermarked_file_bytes() near the bottom of this
        // file for why the true original is only released at download
        // time, after the free/pro quota check passes.
        if ( $thumb_id && function_exists('ds_mkt_get_watermarked_full_url') ) {
            $full = ds_mkt_get_watermarked_full_url($thumb_id);
        }
        if ( ! $full ) {
            $full = get_the_post_thumbnail_url($post->ID, 'full') ?: $thumb;
        }

        $items[] = array(
            'id'    => $post->ID,
            'title' => $post->post_title,
            'thumb' => $thumb,
            'full'  => $full,
            'tier'  => ds_ppt_mkt_item_tier($post->ID),
            'cat'   => wp_get_post_terms($post->ID, 'dlm_download_category', array('fields' => 'names')),
        );
    }

    // Categories for the filter bar — excluded categories are left out so
    // there's never a dead-end pill that yields zero image results.
    $cats = get_terms(array(
        'taxonomy'   => 'dlm_download_category',
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 30,
        'exclude'    => $exclude_ids,
    ));
    $cat_list = array();
    if ( ! is_wp_error($cats) ) {
        foreach ($cats as $ct) {
            $cat_list[] = array('slug' => $ct->slug, 'name' => $ct->name, 'count' => $ct->count);
        }
    }

    return rest_ensure_response(array(
        'items'      => $items,
        'total'      => $q->found_posts,
        'pages'      => $q->max_num_pages,
        'page'       => $page,
        'categories' => $cat_list,
    ));
}
}

// ─── Inject marketplace picker UI ────────────────────────────────────────────
add_action('wp_footer', function () {
    if ( ! is_user_logged_in() ) return;
    global $post;
    if ( ! is_a($post, 'WP_Post') ) return;
    $has_ppt = has_shortcode($post->post_content, 'ds_ai_ppt') || has_shortcode($post->post_content, 'ds_ai_studio');
    if ( ! $has_ppt ) return;
    ?>
<style>
#ds-mkt-pick-overlay{position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:300000;display:none;align-items:center;justify-content:center;}
#ds-mkt-pick-overlay.open{display:flex!important;}
#ds-mkt-pick-box{background:#1a1a2e;border-radius:16px;width:90vw;max-width:900px;max-height:86vh;display:flex;flex-direction:column;border:1px solid #2d3a55;overflow:hidden;}
#ds-mkt-pick-header{padding:14px 18px;background:#111;border-bottom:1px solid #252525;display:flex;align-items:center;gap:10px;flex-shrink:0;}
#ds-mkt-pick-header h3{margin:0;font-size:14px;font-weight:700;color:#f1f5f9;flex:1;}
#ds-mkt-pick-search{background:#1a1a2e;border:1px solid #2d4060;border-radius:8px;padding:6px 12px;color:#f1f5f9;font-size:13px;width:220px;outline:none;}
#ds-mkt-pick-close-btn{background:#292929;border:1px solid #374151;color:#9ca3af;border-radius:8px;padding:6px 12px;cursor:pointer;font-size:12px;font-family:inherit;}
#ds-mkt-pick-cats{display:flex;gap:6px;padding:8px 14px;overflow-x:auto;flex-shrink:0;background:#0d0d0d;border-bottom:1px solid #1e2a40;}
.ds-mkt-pick-cat{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;background:#1e2d4a;color:#94a3b8;border:none;cursor:pointer;font-family:inherit;white-space:nowrap;flex-shrink:0;}
.ds-mkt-pick-cat.active,.ds-mkt-pick-cat:hover{background:#2563eb;color:#fff;}

/* True masonry: CSS multi-column flow instead of flex-wrap rows, so a
   tall (portrait) image never forces blank space under its shorter
   neighbours the way a row-based grid did. */
#ds-mkt-pick-grid{flex:1;overflow-y:auto;overflow-x:hidden;padding:12px;column-gap:10px;column-count:2;}
@media(min-width:560px){#ds-mkt-pick-grid{column-count:3;}}
@media(min-width:760px){#ds-mkt-pick-grid{column-count:4;}}
@media(min-width:1000px){#ds-mkt-pick-grid{column-count:5;}}
@media(max-width:600px){
  #ds-mkt-pick-header{flex-wrap:wrap;}
  #ds-mkt-pick-search{width:100%;order:3;}
}
.ds-mkt-pick-item{box-sizing:border-box;border-radius:8px;overflow:hidden;cursor:pointer;border:2px solid transparent;transition:border-color .15s;background:#0a0a0a;break-inside:avoid;-webkit-column-break-inside:avoid;margin-bottom:10px;width:100%;display:inline-block;position:relative;}
.ds-mkt-pick-item:hover{border-color:#3b82f6;}
.ds-mkt-pick-item-thumb{width:100%;background:#0a0a0a;}
.ds-mkt-pick-item-thumb img{display:block;width:100%;height:auto;}
.ds-mkt-pick-item-lbl{padding:5px 8px;font-size:10px;color:#64748b;background:#111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ds-mkt-pick-tier{position:absolute;top:6px;left:6px;border-radius:10px;padding:2px 8px;font-size:10px;font-weight:700;z-index:2;}
.ds-mkt-pick-tier.free{background:rgba(0,0,0,.6);color:#cbd5e1;}
.ds-mkt-pick-tier.pro{background:rgba(0,0,0,.6);color:#f59e0b;}
#ds-mkt-pick-empty{grid-column:1/-1;padding:40px;text-align:center;color:#374151;font-size:13px;}
#ds-mkt-pick-footer{padding:10px 14px;background:#0d0d0d;border-top:1px solid #252525;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
#ds-mkt-pick-footer span{font-size:11px;color:#4b5563;}
#ds-mkt-pick-pg{display:flex;gap:6px;}
.ds-mkt-pick-pg-btn{background:#1e2d4a;border:none;color:#94a3b8;border-radius:6px;padding:4px 10px;cursor:pointer;font-size:11px;font-family:inherit;}
.ds-mkt-pick-pg-btn:hover{background:#2563eb;color:#fff;}
.ds-mkt-pick-pg-btn:disabled{opacity:.4;cursor:not-allowed;}
#vp-mkt-browse-btn{flex:none;background:#7c3aed;color:#fff;border:none;border-radius:8px;padding:9px 14px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;white-space:nowrap;}
#vp-mkt-browse-btn:hover{background:#6d28d9;}
</style>

<div id="ds-mkt-pick-overlay">
  <div id="ds-mkt-pick-box">
    <div id="ds-mkt-pick-header">
      <h3>🏪 Browse My Marketplace</h3>
      <input id="ds-mkt-pick-search" type="text" placeholder="Search images..." oninput="dsMktPickSearch(this.value)"/>
      <button id="ds-mkt-pick-close-btn" onclick="dsMktPickClose()">✕ Close</button>
    </div>
    <div id="ds-mkt-pick-cats"></div>
    <div id="ds-mkt-pick-grid"><div id="ds-mkt-pick-empty">Loading...</div></div>
    <div id="ds-mkt-pick-footer">
      <span id="ds-mkt-pick-info"></span>
      <div id="ds-mkt-pick-pg">
        <button class="ds-mkt-pick-pg-btn" id="ds-mkt-pg-more" onclick="dsMktPickLoadMore()" style="display:none;">Load More</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
'use strict';

	var DEFAULT_CAT = '<?php
    $ds_mkt_images_term = get_term_by('name', 'Images', 'dlm_download_category');
    echo esc_js( ($ds_mkt_images_term && ! is_wp_error($ds_mkt_images_term)) ? $ds_mkt_images_term->slug : '' );
?>';
	
var MKT = { open:false, cat:'', s:'', page:1, total:0, pages:1, slideIdx:-1, debounce:null, loading:false, loaded:0 };
var REST_URL = '<?php echo esc_js(rest_url('ds/v1/mkt-slide-images')); ?>';
var NONCE    = '<?php echo esc_js(wp_create_nonce('wp_rest')); ?>';

function mktLoad(resetPage){
  if(resetPage){ MKT.page=1; MKT.loaded=0; }
  if(MKT.loading) return;
  MKT.loading=true;
  var grid=document.getElementById('ds-mkt-pick-grid');
  var moreBtn=document.getElementById('ds-mkt-pg-more');
  if(resetPage && grid) grid.innerHTML='<div id="ds-mkt-pick-empty">Loading...</div>';
  if(moreBtn){ moreBtn.disabled=true; moreBtn.textContent='Loading...'; }

  var url=REST_URL+'?per_page=100&page='+MKT.page;
  if(MKT.cat) url+='&cat='+encodeURIComponent(MKT.cat);
  if(MKT.s)   url+='&s='+encodeURIComponent(MKT.s);

  fetch(url,{headers:{'X-WP-Nonce':NONCE}})
  .then(function(r){return r.json();})
  .then(function(d){
    MKT.loading=false;
    var firstLoad = d.categories && d.categories.length && !document.querySelector('.ds-mkt-pick-cat[data-loaded]');
    if(firstLoad && !MKT.cat){
      var imgCat=d.categories.find(function(c){return /image/i.test(c.name);});
      if(imgCat){
        MKT.cat=imgCat.slug;
        mktRenderCats(d.categories);
        mktLoad(true);
        return;
      }
    }
    if(firstLoad) mktRenderCats(d.categories);
    mktRenderGrid(d.items||[], !resetPage);
    MKT.total=d.total||0;
    MKT.pages=d.pages||1;
    MKT.loaded=(resetPage?0:MKT.loaded)+(d.items?d.items.length:0);
    var info=document.getElementById('ds-mkt-pick-info');
    if(info) info.textContent=MKT.loaded+' of '+MKT.total+' images loaded';
    var moreBtn2=document.getElementById('ds-mkt-pg-more');
    if(moreBtn2){
      if(MKT.page<MKT.pages){ moreBtn2.style.display=''; moreBtn2.disabled=false; moreBtn2.textContent='Load More'; }
      else { moreBtn2.style.display='none'; }
    }
  })
  .catch(function(){
    MKT.loading=false;
    var moreBtn3=document.getElementById('ds-mkt-pg-more');
    if(moreBtn3){ moreBtn3.disabled=false; moreBtn3.textContent='Load More'; }
    if(resetPage){
      var g=document.getElementById('ds-mkt-pick-grid');
      if(g) g.innerHTML='<div id="ds-mkt-pick-empty">Could not load images.</div>';
    }
  });
}

function mktRenderGrid(items, append){
  var grid=document.getElementById('ds-mkt-pick-grid');
  if(!grid)return;
  if(!append && !items.length){grid.innerHTML='<div id="ds-mkt-pick-empty">No images found.</div>';return;}
  var html=items.map(function(item){
    var tier=item.tier==='pro'?'pro':'free';
    var badge=tier==='pro'?'👑 Pro':'Free';
    return '<div class="ds-mkt-pick-item" onclick="dsMktPickUse(\''+escJ(item.full)+'\',\''+escJ(item.title)+'\','+parseInt(item.id,10)+',\''+tier+'\')">'
          +'<span class="ds-mkt-pick-tier '+tier+'">'+badge+'</span>'
          +'<div class="ds-mkt-pick-item-thumb"><img src="'+escH(item.thumb)+'" alt="'+escH(item.title)+'" loading="lazy"/></div>'
          +'<div class="ds-mkt-pick-item-lbl">'+escH(item.title)+'</div>'
          +'</div>';
  }).join('');
  if(append){ grid.insertAdjacentHTML('beforeend', html); }
  else { grid.innerHTML=html; }
}

function mktRenderCats(cats){
  var bar=document.getElementById('ds-mkt-pick-cats');
  if(!bar)return;
  cats.forEach(function(ct){
    var btn=document.createElement('button');
    btn.className='ds-mkt-pick-cat'+(ct.slug===MKT.cat?' active':'');
    btn.setAttribute('data-loaded','1');
    btn.textContent=ct.name+' ('+ct.count+')';
    btn.onclick=function(){dsMktPickCat(ct.slug,btn);};
    bar.appendChild(btn);
  });
}

function escH(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function escJ(s){return String(s).replace(/'/g,"\\'").replace(/\\/g,'\\\\');}

window.dsMktPickOpen=function(slideIdx){
  MKT.slideIdx=slideIdx;
  var ov=document.getElementById('ds-mkt-pick-overlay');
  if(ov)ov.classList.add('open');
  if(!document.querySelector('.ds-mkt-pick-item')) mktLoad(true);
};

window.dsMktPickClose=function(){
  var ov=document.getElementById('ds-mkt-pick-overlay');
  if(ov)ov.classList.remove('open');
};

window.dsMktPickCat=function(cat,btn){
  MKT.cat=cat;
  document.querySelectorAll('.ds-mkt-pick-cat').forEach(function(b){b.classList.remove('active');});
  if(btn)btn.classList.add('active');
  mktLoad(true);
};

window.dsMktPickSearch=function(val){
  clearTimeout(MKT.debounce);
  MKT.s=val;
  MKT.debounce=setTimeout(function(){mktLoad(true);},400);
};

window.dsMktPickLoadMore=function(){
  if(MKT.loading) return;
  if(MKT.page>=MKT.pages) return;
  MKT.page=MKT.page+1;
  mktLoad(false);
};

window.dsMktPickUse=function(imageUrl, title, marketId, marketTier){
  var idx = (typeof MKT !== 'undefined' && MKT.slideIdx >= 0) ? MKT.slideIdx : (window._vpMktTargetSlide >= 0 ? window._vpMktTargetSlide : window.S ? window.S.idx : -1);
  var targetVariant=window._vpMktTargetVariant||null;

  dsMktPickClose();
  if(window.dspToast) dspToast('Importing image from marketplace...','success',4000);

  fetch('<?php echo esc_js(rest_url('ds/v1/mkt-fetch-image')); ?>',{
    method:'POST',
    headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
    body:JSON.stringify({post_id:marketId,url:imageUrl})
  })
  .then(function(r){return r.json();})
  .then(function(d){
    if(d&&d.success&&d.data){
      if(window.dspUpdateSlideImage){
        window.dspUpdateSlideImage(idx, d.data, title);
      }
      // Tag BOTH data stores — G.pptData is what dspBuild() sends to
      // the server, S.data is what Snippet 17's layout builder sends.
      // Previously only S.data was tagged, so dspBuild() never saw the
      // market ID and the watermarked thumbnail was embedded as-is.
      if(window.G && window.G.pptData && window.G.pptData.slides && window.G.pptData.slides[idx]){
        window.G.pptData.slides[idx].image_market_id   = marketId;
        window.G.pptData.slides[idx].image_market_tier = marketTier;
      }
      if(window.S && window.S.data && window.S.data.slides && window.S.data.slides[idx]){
        window.S.data.slides[idx].image_market_id   = marketId;
        window.S.data.slides[idx].image_market_tier = marketTier;
      }
      if(targetVariant&&window.dspVPPick){
        window.dspVPPick(idx, targetVariant);
      }
      window._vpMktTargetVariant=null;
      // Mark this slide so the build step knows the real file is already
      // in image_data and quota was already deducted — no swap needed.
      var _tier=d.tier||marketTier;
      if(window.G&&window.G.pptData&&window.G.pptData.slides&&window.G.pptData.slides[idx]){
        window.G.pptData.slides[idx].image_market_id   =marketId;
        window.G.pptData.slides[idx].image_market_tier =_tier;
        window.G.pptData.slides[idx].image_mkt_fetched =true;
      }
      if(window.S&&window.S.data&&window.S.data.slides&&window.S.data.slides[idx]){
        window.S.data.slides[idx].image_market_id   =marketId;
        window.S.data.slides[idx].image_market_tier =_tier;
        window.S.data.slides[idx].image_mkt_fetched =true;
      }
      if(window.dspToast) dspToast('✅ Image added from marketplace!','success');
    } else {
      var errMsg=(d&&d.error)?d.error:'Could not load image.';
      if(window.dspToast) dspToast('❌ '+errMsg,'error',5000);
    }
  })
  .catch(function(e){
    if(window.dspToast) dspToast('❌ Error: '+e.message,'error');
  });
};

function injectBrowseBtn(){
  if(document.getElementById('vp-mkt-browse-btn')) return;
  var cancelBtn=document.getElementById('vp-img-cancelbtn');
  if(!cancelBtn) return;

  var btn=document.createElement('button');
  btn.id='vp-mkt-browse-btn';
  btn.type='button';
  btn.textContent='🏪 Browse Store';
  btn.onclick=function(){
    if(window.vpImgClose) window.vpImgClose();
    var idx=window.S?window.S.genIdx:-1;
    if(idx<0&&window.S) idx=window.S.idx;
    MKT.slideIdx=idx;
    setTimeout(function(){
      var ov=document.getElementById('ds-mkt-pick-overlay');
      if(ov){ov.classList.add('open');}
      if(!document.querySelector('.ds-mkt-pick-item')) mktLoad(true);
    },100);
  };

  cancelBtn.parentNode.insertBefore(btn, cancelBtn);
}

var _genObs=new MutationObserver(function(){
  var modal=document.getElementById('vp-imgmod');
  if(modal&&modal.classList.contains('open')){
    injectBrowseBtn();
  }
});
var _modal=document.getElementById('vp-imgmod');
if(_modal) _genObs.observe(_modal,{attributes:true,attributeFilter:['class']});

setTimeout(injectBrowseBtn,1000);

})();
</script>
<?php
});

// ─── REST endpoint: proxy-fetch marketplace image as base64 ──────────────────
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/mkt-fetch-image', array(
        'methods'             => 'POST',
        'callback'            => 'ds_ppt_mkt_fetch_image',
        'permission_callback' => function () { return is_user_logged_in(); },
    ));
});

if ( ! function_exists('ds_ppt_mkt_fetch_image') ) {
function ds_ppt_mkt_fetch_image( WP_REST_Request $req ) {
    $uid     = get_current_user_id();
    $post_id = (int) $req->get_param('post_id');

    // Primary path: post_id supplied — fetch the REAL DLM file directly
    // and deduct quota NOW (at pick time), so nothing needs to happen at
    // build time. This eliminates the fragile build-time swap entirely.
    if ( $post_id > 0
        && function_exists('ds_mkt_get_unwatermarked_file_bytes')
        && function_exists('ds_dl_check_core') ) {

        $tags      = get_the_terms($post_id, 'dlm_download_tag') ?: array();
        $tag_slugs = is_array($tags)
            ? array_map(function($t){ return strtolower($t->slug); }, $tags)
            : array();
        $tier = in_array('pro', $tag_slugs, true) ? 'pro' : 'free';

        /* Ask first without counting; the download is counted below, only
           once the real file is in hand. A missing file used to cost a
           download and still return the preview. */
        $check = ds_dl_check_core($uid, $tier, false);
        if ( empty($check['ok']) ) {
            $reason = $check['reason'] ?? 'limit_reached';
            $msg    = ($reason === 'upgrade_required')
                ? 'Upgrade your plan to use Pro marketplace images.'
                : 'You have reached your ' . ucfirst($tier) . ' image download limit for this period.';
            return new WP_REST_Response(array(
                'success' => false,
                'error'   => $msg,
                'reason'  => $reason,
            ), 403);
        }

        $real = ds_mkt_get_unwatermarked_file_bytes($post_id);
        if ( $real && ! empty($real['bytes']) ) {
            ds_dl_check_core($uid, $tier, true);
            $b64 = 'data:' . $real['mime'] . ';base64,' . base64_encode($real['bytes']);
            return new WP_REST_Response(array(
                'success'        => true,
                'data'           => $b64,
                'mime'           => $real['mime'],
                'size'           => strlen($real['bytes']),
                'tier'           => $tier,
                'quota_deducted' => true,
            ), 200);
        }
        // DLM file not found — fall through to URL proxy as last resort
    }

    // Fallback: proxy the URL directly (backward compat / no DLM file)
    $url = esc_url_raw($req->get_param('url') ?: '');
    if ( empty($url) ) {
        return new WP_REST_Response(array('success'=>false,'error'=>'No image source provided'), 400);
    }
    $site_host = parse_url(home_url(), PHP_URL_HOST);
    $img_host  = parse_url($url, PHP_URL_HOST);
    if ( $img_host !== $site_host ) {
        return new WP_REST_Response(array('success'=>false,'error'=>'External URLs not allowed'), 403);
    }
    $response = wp_remote_get($url, array('timeout'=>15,'sslverify'=>false));
    if ( is_wp_error($response) ) {
        return new WP_REST_Response(array('success'=>false,'error'=>$response->get_error_message()), 500);
    }
    $code = wp_remote_retrieve_response_code($response);
    if ( $code !== 200 ) {
        return new WP_REST_Response(array('success'=>false,'error'=>"HTTP $code"), 500);
    }
    $body = wp_remote_retrieve_body($response);
    $ct   = wp_remote_retrieve_header($response, 'content-type');
    if ( strpos($ct,'png') !== false )      $mime = 'image/png';
    elseif ( strpos($ct,'webp') !== false ) $mime = 'image/webp';
    else                                    $mime = 'image/jpeg';
    $b64 = 'data:'.$mime.';base64,'.base64_encode($body);
    return new WP_REST_Response(array('success'=>true,'data'=>$b64,'mime'=>$mime,'size'=>strlen($body)), 200);
}
}

// ════════════════════════════════════════════════════════════════════════
// NEW — fetches the real, unwatermarked original file for a marketplace
// image product, straight from Download Monitor (the same source the
// "Download Now" button on the public marketplace page uses). This is
// ONLY ever called from the PPTX-build step, after a free/pro quota
// check has already passed for that exact image -- never at pick/edit
// time, so editing a slide can never leak the real file for free.
// Used by the matching patches in Snippet 12B and Snippet 16.
// ════════════════════════════════════════════════════════════════════════
if ( ! function_exists('ds_mkt_get_unwatermarked_file_bytes') ) {
function ds_mkt_get_unwatermarked_file_bytes( $post_id ) {
    // Primary: query DLM version post and read _download_files meta —
    // same reliable approach Snippet 21 already uses for template files.
    $versions = get_posts(array(
        'post_type'      => 'dlm_download_version',
        'post_parent'    => $post_id,
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'fields'         => 'ids',
    ));
    $url = '';
    if ( ! empty($versions) ) {
        $url = (string) get_post_meta($versions[0], '_download_files', true);
    }

    // Fallback: DLM service API (if installed and accessible)
    if ( empty($url) && function_exists('download_monitor') ) {
        try {
            $obj = download_monitor()->service('download_repository')->retrieve_single($post_id);
            if ( $obj ) {
                $ver = $obj->get_version();
                if ( $ver ) $url = $ver->get_url();
            }
        } catch ( Exception $e ) {
            error_log('[ds_mkt_get_unwatermarked_file_bytes] DLM service error: '.$e->getMessage());
        }
    }

    if ( empty($url) ) return false;

    $resp = wp_remote_get($url, array('timeout' => 30, 'sslverify' => false));
    if ( is_wp_error($resp) ) return false;
    if ( (int) wp_remote_retrieve_response_code($resp) !== 200 ) return false;

    $bytes = wp_remote_retrieve_body($resp);
    if ( empty($bytes) || strlen($bytes) < 1000 ) return false;

    $ct = wp_remote_retrieve_header($resp, 'content-type');
    if ( strpos($ct, 'png') !== false )       $mime = 'image/png';
    elseif ( strpos($ct, 'webp') !== false )  $mime = 'image/webp';
    elseif ( strpos($ct, 'gif') !== false )   $mime = 'image/gif';
    else                                      $mime = 'image/jpeg';

    return array('bytes' => $bytes, 'mime' => $mime);
}
}
