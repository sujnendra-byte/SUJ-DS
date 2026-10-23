<?php
/**
 * Code Snippets export — DS Template Preview Photos — real marketplace tech photos in template previews
 *
 * Snippet ID : 1188
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates, marketplace
 * Modified   : 2026-10-13
 * Load order : 351 of 352
 */
/**
 * DS Template Preview Photos — real marketplace photos in the template previews
 * Run: Everywhere
 *
 * AI Presentation home screen: the design-template cards, the 3-slide sample preview and the hover
 * preview used to show grey "Click to add image" boxes. They now show real tech photos from the
 * marketplace (AI & Technology and the AI visualisation categories), without watermark:
 *  - every template gets its own photo, and a card with several image boxes gets a different
 *    photo in each box;
 *  - the photos are the marketplace's own light preview images (about 400 px), so the cards stay fast;
 *  - only the sample previews change: real decks, the editor and downloads are untouched, and an
 *    empty image box in a real deck still says "Click to add image".
 * NEW SNIPPET. Undo: deactivate this snippet (the previews go back to the grey boxes).
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('ds_tpp_photos') ) {
	/* the marketplace's own preview images of the tech items (AI & Technology and the AI visualisation categories),
	   mixed in a fixed order so each template keeps its photo; cached for 12 hours */
	function ds_tpp_photos() {
		$list = get_transient('ds_tpp_photos_v2');
		if ( is_array($list) ) return array('photos' => $list, 'pending' => 0);
		$q = new WP_Query(array(
			'post_type' => 'dlm_download', 'post_status' => 'publish', 'posts_per_page' => 260, 'orderby' => 'date', 'order' => 'ASC',
			'fields' => 'ids', 'no_found_rows' => true,
			'tax_query' => array(array('taxonomy' => 'dlm_download_category', 'field' => 'slug',
				'terms' => array('ai-technology', 'ai-neural-network-visualization', 'abstract-machine-learning-visualization', 'ai-assistant-concept'))),
		));
		$rows = array();
		foreach ( $q->posts as $pid ) {
			$tid = (int) get_post_thumbnail_id($pid);
			if ( ! $tid || strpos((string) get_post_mime_type($tid), 'image/') !== 0 ) continue;
			$u = function_exists('ds_mkt_get_grid_url') ? ds_mkt_get_grid_url($tid) : '';
			if ( ! $u ) $u = wp_get_attachment_image_url($tid, 'medium_large') ?: wp_get_attachment_url($tid);
			if ( $u ) $rows[md5('tpp' . $tid)] = $u;
		}
		ksort($rows);
		$list = array_values(array_unique(array_values($rows)));
		set_transient('ds_tpp_photos_v2', $list, 12 * HOUR_IN_SECONDS);
		return array('photos' => $list, 'pending' => 0);
	}
}

add_action('rest_api_init', function () {
	register_rest_route('ds/v1', '/tpl-preview-photos', array(
		'methods' => 'GET',
		'callback' => function () { return rest_ensure_response(ds_tpp_photos()); },
		'permission_callback' => '__return_true',
	));
});

add_action('wp_footer', function () {
	if ( is_admin() ) return;
	?>
<script id="ds-tpl-preview-photos">
(function(){
	var API = <?php echo wp_json_encode(rest_url('ds/v1/tpl-preview-photos')); ?>, PH = [], KEY = 'dsTplPhotos1';
	try { var c = JSON.parse(localStorage.getItem(KEY) || 'null'); if (c && c.t > Date.now() - 86400000 && Array.isArray(c.p)) PH = c.p; } catch (e) {}
	function start(){
		var D = window.DSTE; if (!D || !D.renderPage || !D.toHTML || !D.SAMPLE) return false; if (D.__tpp) return true; D.__tpp = 1;
		function sampleKey(sl){ var S = D.SAMPLE, ks = Object.keys(S); for (var i = 0; i < ks.length; i++) if (S[ks[i]] === sl) return i; return -1; }
		function isSampleDeck(d){ if (!d || !d.slides) return false; for (var i = 0; i < d.slides.length; i++) if (sampleKey(d.slides[i]) >= 0) return true; return false; }
		/* the template of each drawn page; sample pages show every picture box as its own picture */
		var rp = D.renderPage;
		D.renderPage = function(T, slide, vid, idx, deck){
			var p = rp.apply(this, arguments);
			try { if (p && typeof p === 'object' && T && T.id) { Object.defineProperty(p, '__tid', {value:T.id, configurable:true, writable:true});
				if (PH.length && sampleKey(slide) >= 0 && !slide.image_data) (p.els || []).forEach(function(e){ if (e && e.t === 'img' && e.slot === 2) delete e.slot; }); } } catch (e) {}
			return p;
		};
		var th = D.toHTML;
		D.toHTML = function(page, idx, opts){
			var o = opts || {}, sl = o.slide, k = sl ? sampleKey(sl) : -1;
			if (!PH.length || k < 0 || sl.image_data || !page || !page.els || !page.els.some(function(e){ return e && e.t === 'img'; })) return th.apply(this, arguments);
			var ti = Math.max(0, (D.order || []).indexOf(page.__tid)), n = PH.length, base = (ti + k * 17) % n, A = PH[base];
			var s2 = {}, o2 = {}, q; for (q in sl) s2[q] = sl[q]; s2.image_data = A; s2.image_position = {x:50, y:50};
			for (q in o) o2[q] = o[q]; o2.slide = s2;
			var a = Array.prototype.slice.call(arguments); while (a.length < 3) a.push(undefined); a[2] = o2;
			var h = th.apply(this, a), parts = h.split('url(' + A + ')');
			if (parts.length < 2) return h;
			var out = parts[0]; for (var i = 1; i < parts.length; i++) out += 'url(' + PH[(base + (i - 1) * 53) % n] + ')' + parts[i];
			return out;
		};
		return true;
	}
	function refresh(){ try { if (window.DSTE && typeof DSTE.onRegister === 'function') DSTE.onRegister({id:'__dsr_refresh'}); } catch (e) {} var ag = document.getElementById('dssl-arch-g'); if (ag) ag.innerHTML = ''; }
	var tries = 0;
	function load(){
		fetch(API, {credentials:'same-origin'}).then(function(r){ return r.json(); }).then(function(j){
			if (!j || !Array.isArray(j.photos)) return;
			var grew = j.photos.length !== PH.length; PH = j.photos;
			try { localStorage.setItem(KEY, JSON.stringify({t:Date.now(), p:PH})); } catch (e) {}
			if (grew) refresh();
			if (j.pending > 0 && ++tries < 40) setTimeout(load, 2500);
		}).catch(function(){});
	}
	var iv = setInterval(function(){ if (start()) { clearInterval(iv); load(); } }, 300);
	setTimeout(function(){ clearInterval(iv); }, 30000);
})();
</script>
	<?php
}, 2010);
