<?php
/**
 * Code Snippets export — DS Downloads — Free / Pro in one click (column, bulk Make Free / Make Pro, select all, filter)
 *
 * Snippet ID : 1246
 * Active     : yes
 * Priority   : 10
 * Scope      : admin
 * Tags       : 
 * Modified   : 2026-10-16 02:30:46
 * Load order : 369 of 369
 */
/**
 * DS Downloads - Free / Pro in one click
 * Downloads list: a Free/Pro column (click the badge to switch), "Make Free" and
 * "Make Pro" bulk actions, "select all items on every page", and a Free/Pro filter.
 * Pro = the "pro" tag (dlm_download_tag), exactly what the download limits read.
 */
if ( ! defined('ABSPATH') ) exit;

function ds_tier_is_pro( $id ) { return has_term('pro', 'dlm_download_tag', $id); }
function ds_tier_set( $id, $pro ) {
	if ( $pro ) wp_set_object_terms($id, 'pro', 'dlm_download_tag', true);
	else wp_remove_object_terms($id, 'pro', 'dlm_download_tag');
	clean_post_cache($id);
}

/* column */
add_filter('manage_dlm_download_posts_columns', function ( $cols ) {
	$out = array();
	foreach ( $cols as $k => $v ) { $out[$k] = $v; if ( $k === 'title' || $k === 'download_title' ) $out['ds_tier'] = 'Free / Pro'; }
	if ( ! isset($out['ds_tier']) ) $out['ds_tier'] = 'Free / Pro';
	return $out;
}, 999);
add_action('manage_dlm_download_posts_custom_column', function ( $col, $id ) {
	if ( $col !== 'ds_tier' ) return;
	$pro = ds_tier_is_pro($id);
	echo '<button type="button" class="ds-tier-btn ' . ( $pro ? 'is-pro' : 'is-free' ) . '" data-id="' . (int) $id . '" title="Click to switch">' . ( $pro ? 'PRO' : 'FREE' ) . '</button>';
}, 10, 2);

/* one-click switch */
add_action('wp_ajax_ds_tier_toggle', function () {
	check_ajax_referer('ds_tier', 'nonce');
	$id = (int) ($_POST['id'] ?? 0);
	if ( ! $id || get_post_type($id) !== 'dlm_download' || ! current_user_can('edit_post', $id) ) wp_send_json_error('Not allowed', 403);
	$pro = ! ds_tier_is_pro($id);
	ds_tier_set($id, $pro);
	wp_send_json_success(array('pro' => ds_tier_is_pro($id)));
});

/* bulk actions */
add_filter('bulk_actions-edit-dlm_download', function ( $a ) {
	$a['ds_make_free'] = 'Make Free';
	$a['ds_make_pro'] = 'Make Pro';
	return $a;
});
add_filter('handle_bulk_actions-edit-dlm_download', function ( $redirect, $action, $ids ) {
	if ( $action !== 'ds_make_free' && $action !== 'ds_make_pro' ) return $redirect;
	if ( ! current_user_can('edit_others_posts') ) return $redirect;
	if ( ! empty($_REQUEST['ds_tier_all']) ) {
		/* every item in the current list view, on every page */
		$q = array('post_type' => 'dlm_download', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true);
		if ( ! empty($_REQUEST['s']) ) $q['s'] = sanitize_text_field(wp_unslash($_REQUEST['s']));
		if ( ! empty($_REQUEST['post_status']) && $_REQUEST['post_status'] !== 'all' ) $q['post_status'] = sanitize_key($_REQUEST['post_status']);
		if ( ! empty($_REQUEST['author']) ) $q['author'] = (int) $_REQUEST['author'];
		if ( ! empty($_REQUEST['m']) ) $q['m'] = (int) $_REQUEST['m'];
		$tax = array();
		if ( ! empty($_REQUEST['dlm_download_category']) ) $tax[] = array('taxonomy' => 'dlm_download_category', 'field' => 'slug', 'terms' => sanitize_title(wp_unslash($_REQUEST['dlm_download_category'])));
		if ( ! empty($_REQUEST['ds_tier_f']) ) $tax[] = array('taxonomy' => 'dlm_download_tag', 'field' => 'slug', 'terms' => 'pro', 'operator' => $_REQUEST['ds_tier_f'] === 'pro' ? 'IN' : 'NOT IN');
		if ( $tax ) $q['tax_query'] = $tax;
		$ids = get_posts($q);
	}
	@set_time_limit(600);
	wp_defer_term_counting(true);
	$pro = $action === 'ds_make_pro'; $n = 0;
	foreach ( (array) $ids as $id ) {
		$id = (int) $id;
		if ( get_post_type($id) !== 'dlm_download' ) continue;
		if ( ds_tier_is_pro($id) !== $pro ) ds_tier_set($id, $pro);
		$n++;
	}
	wp_defer_term_counting(false);
	return add_query_arg(array('ds_tier_done' => $n, 'ds_tier_to' => $pro ? 'pro' : 'free'), remove_query_arg(array('ds_tier_done', 'ds_tier_to', 'ds_tier_all'), $redirect));
}, 10, 3);
add_action('admin_notices', function () {
	if ( ! isset($_GET['ds_tier_done']) || ( get_current_screen()->id ?? '' ) !== 'edit-dlm_download' ) return;
	$n = (int) $_GET['ds_tier_done']; $to = ( $_GET['ds_tier_to'] ?? '' ) === 'pro' ? 'Pro' : 'Free';
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(number_format_i18n($n) . ' ' . _n('item is', 'items are', $n) . ' now ' . $to . '.') . '</p></div>';
});

/* Free / Pro filter */
add_action('restrict_manage_posts', function ( $pt ) {
	if ( $pt !== 'dlm_download' ) return;
	$c = sanitize_key($_GET['ds_tier_f'] ?? '');
	echo '<select name="ds_tier_f"><option value="">Free &amp; Pro</option><option value="free"' . selected($c, 'free', false) . '>Free only</option><option value="pro"' . selected($c, 'pro', false) . '>Pro only</option></select>';
});
add_action('pre_get_posts', function ( $q ) {
	if ( ! is_admin() || ! $q->is_main_query() || ( $q->get('post_type') !== 'dlm_download' ) || empty($_GET['ds_tier_f']) ) return;
	$tq = (array) $q->get('tax_query');
	$tq[] = array('taxonomy' => 'dlm_download_tag', 'field' => 'slug', 'terms' => 'pro', 'operator' => $_GET['ds_tier_f'] === 'pro' ? 'IN' : 'NOT IN');
	$q->set('tax_query', $tq);
});

/* styles + click handling + "select all on every page" */
add_action('admin_footer-edit.php', function () {
	if ( ( get_current_screen()->id ?? '' ) !== 'edit-dlm_download' ) return;
	global $wp_query;
	$total = (int) ( $wp_query->found_posts ?? 0 );
	?>
	<style>
	.column-ds_tier{width:90px}
	.ds-tier-btn{border:0;border-radius:12px;padding:4px 12px;font-weight:700;font-size:11px;letter-spacing:.04em;cursor:pointer;min-width:58px}
	.ds-tier-btn.is-free{background:#e7f5ea;color:#0a6b2a}
	.ds-tier-btn.is-pro{background:#fdf1d8;color:#8a5a00;box-shadow:inset 0 0 0 1px #f0c36a}
	.ds-tier-btn:hover{filter:brightness(.95)}
	.ds-tier-btn.busy{opacity:.5;cursor:wait}
	#ds-tier-all{margin:6px 0 10px;padding:8px 12px;background:#f0f6fc;border-left:4px solid #2271b1;display:none}
	</style>
	<script>
	(function(){
		var nonce = <?php echo wp_json_encode(wp_create_nonce('ds_tier')); ?>, total = <?php echo (int) $total; ?>;
		document.addEventListener('click', function(e){
			var b = e.target.closest('.ds-tier-btn'); if (!b || b.classList.contains('busy')) return;
			e.preventDefault(); b.classList.add('busy');
			var fd = new FormData(); fd.append('action','ds_tier_toggle'); fd.append('nonce',nonce); fd.append('id',b.dataset.id);
			fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){
				b.classList.remove('busy');
				if (!j || !j.success) { alert('Could not change this item.'); return; }
				var pro = j.data.pro; b.className = 'ds-tier-btn ' + (pro ? 'is-pro' : 'is-free'); b.textContent = pro ? 'PRO' : 'FREE';
			}).catch(function(){ b.classList.remove('busy'); });
		});
		try { var qs = new URLSearchParams(location.search), dn = qs.get('ds_tier_done'); if (dn !== null && !document.getElementById('ds-tier-msg')) { var m = document.createElement('div'); m.id = 'ds-tier-msg'; m.className = 'notice notice-success'; m.innerHTML = '<p><b>' + Number(dn).toLocaleString() + '</b> ' + (dn == 1 ? 'item is' : 'items are') + ' now <b>' + (qs.get('ds_tier_to') === 'pro' ? 'Pro' : 'Free') + '</b>.</p>'; var h = document.querySelector('.wrap .wp-header-end') || document.querySelector('.wrap h1'); if (h) h.parentNode.insertBefore(m, h.nextSibling); history.replaceState(null, '', location.href.replace(/([?&])ds_tier_(done|to)=[^&]*/g, '$1').replace(/[?&]+$/, '')); } } catch (x) {}
		document.querySelectorAll('.wp-list-table tr').forEach(function(tr){ var t = tr.querySelector('.column-ds_tier'), ti = tr.querySelector('.column-download_title, .column-title'); if (t && ti && ti.nextElementSibling !== t) ti.parentNode.insertBefore(t, ti.nextElementSibling); });
		var form = document.getElementById('posts-filter'); if (!form) return;
		var bar = document.createElement('div'); bar.id = 'ds-tier-all';
		var hid = document.createElement('input'); hid.type = 'hidden'; hid.name = 'ds_tier_all'; hid.value = '';
		form.appendChild(hid);
		var tablenav = form.querySelector('.tablenav.top'); if (tablenav) tablenav.parentNode.insertBefore(bar, tablenav.nextSibling);
		var rows = function(){ return form.querySelectorAll('#the-list .check-column input[type=checkbox]'); };
		function draw(){
			var boxes = rows(), on = 0; boxes.forEach(function(c){ if (c.checked) on++; });
			if (!boxes.length || on < boxes.length || total <= boxes.length) { bar.style.display = 'none'; hid.value = ''; return; }
			bar.style.display = 'block';
			if (hid.value) bar.innerHTML = 'All <b>' + total.toLocaleString() + '</b> items in this list are selected (for Make Free / Make Pro). <a href="#" data-x="clear">Clear selection</a>';
			else bar.innerHTML = 'All ' + boxes.length + ' items on this page are selected. <a href="#" data-x="all">Select all ' + total.toLocaleString() + ' items</a>';
		}
		bar.addEventListener('click', function(e){
			var a = e.target.closest('a'); if (!a) return; e.preventDefault();
			if (a.dataset.x === 'all') hid.value = '1';
			else { hid.value = ''; form.querySelectorAll('.check-column input[type=checkbox]').forEach(function(c){ c.checked = false; }); }
			draw();
		});
		form.addEventListener('change', function(e){ if (e.target.matches('.check-column input[type=checkbox]')) { if (!e.target.checked) hid.value = ''; setTimeout(draw, 0); } });
		form.addEventListener('submit', function(e){
			if (!hid.value) return;
			var act = (form.querySelector('#bulk-action-selector-top') || {}).value, act2 = (form.querySelector('#bulk-action-selector-bottom') || {}).value;
			var a = (act && act !== '-1') ? act : act2;
			if (a !== 'ds_make_free' && a !== 'ds_make_pro') { hid.value = ''; return; }
			if (!confirm('Change all ' + total.toLocaleString() + ' items to ' + (a === 'ds_make_pro' ? 'Pro' : 'Free') + '?')) { e.preventDefault(); }
		});
	})();
	</script>
	<?php
});
