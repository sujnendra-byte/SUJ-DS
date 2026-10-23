<?php
/**
 * Code Snippets export — DS PowerPoint Add-in Bridge (Office)
 *
 * Snippet ID : 859
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : powerpoint, office-addin, designstager, ds-order-148
 * Load order : 148 of 149
 *
 * Lets the DesignStager PowerPoint add-in open this website in a full-size
 * PowerPoint window and insert downloads (slides, images, SVG) straight into
 * the presentation. Does nothing for normal visitors.
 *
 * Source: the standalone DesignStager Office Bridge plugin, internal VERSION
 * 1.9.2. Only the "Plugin Name:" header is stripped, so WordPress does not
 * list this bundle a second time as its own plugin.
 *
 * DO NOT reintroduce a class_exists( 'DS_Office_Bridge' ) guard here. PHP
 * binds an unconditional top-level class declaration at COMPILE time, so that
 * check is already true the first time this file is required: it returned
 * every single time, init() was never called, and every /ds-office/ URL 404'd
 * with no error anywhere. The constant guard below is hoist-proof.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Re-entry guard.
 *
 * This deliberately does NOT test class_exists( 'DS_Office_Bridge' ). PHP binds
 * an unconditional top-level class declaration at COMPILE time, so the class
 * below already exists by the time this line runs, even on the very first load
 * of this file. The old class_exists() check was therefore ALWAYS true: the
 * file returned here every single time, DS_Office_Bridge::init() was never
 * reached, no hooks were registered, and every /ds-office/ URL 404'd - while
 * the file itself looked perfectly healthy and threw no error.
 *
 * A constant is not hoisted, so it is a correct re-entry guard.
 */
if ( defined( 'DS_OFFICE_BRIDGE_LOADED' ) ) {
	return;
}
define( 'DS_OFFICE_BRIDGE_LOADED', '1.10.0' );


/**
 * Credits / plan for the current user, taken from the site's own REST route.
 * rest_do_request() runs in-process with the current cookie session, so this
 * returns exactly what the website shows - no nonce, no duplicated logic.
 */
function ds_office_billing() {
    if ( ! is_user_logged_in() || ! function_exists( 'rest_do_request' ) ) {
        return null;
    }
    try {
        $req = new WP_REST_Request( 'GET', '/ds/v1/credits' );
        $res = rest_do_request( $req );
        if ( is_wp_error( $res ) || ! is_object( $res ) ) {
            return null;
        }
        if ( method_exists( $res, 'get_status' ) && (int) $res->get_status() >= 400 ) {
            return array( 'error' => (int) $res->get_status() );
        }
        $data = $res->get_data();
        return is_array( $data ) ? $data : null;
    } catch ( Exception $e ) {
        return null;
    }
}


/**
 * Extra marketplace kinds for the add-in library (videos, audios, icons).
 * The bridge's own library_json() is hard-coded to templates-vs-images, so
 * this runs just before it and answers only for the extra kinds. Same JSON
 * shape. Nothing else is touched.
 */
/**
 * Preview stream for audio and video tiles in the task pane.
 *
 * Direct access to /wp-content/uploads/*.mp3 and *.mp4 is blocked, which is
 * correct for a paid library. This serves those files inline to a logged-in
 * user, straight from disk, with byte-range support so a player can scrub.
 * It never touches Download Monitor, so listening is not a download.
 */
/**
 * Marketplace pages opened inside the PowerPoint window are much narrower than
 * a browser, so the category rail stacks on top and buries the products. In
 * add-in mode only, it collapses behind a Categories button. The website in a
 * normal browser is not touched: html.ds-office-mode is only set by the add-in.
 */
function ds_office_marketplace_compact() {
	?>
	<style id="ds-office-mkt-compact">
	html.ds-office-mode .ds-mkt { flex-direction: column; height: auto; }
	html.ds-office-mode .ds-mkt-sb {
		width: 100%; height: auto; max-height: 50vh; overflow-y: auto;
		border-right: 0; border-bottom: 1px solid #1e2a40; padding: 10px;
	}
	html.ds-office-mode .ds-mkt-sb[hidden] { display: none; }
	html.ds-office-mode .ds-mkt-main { height: auto; flex: 1 1 auto; min-height: 0; }
	html.ds-office-mode #ds-office-cats {
		display: flex; align-items: center; justify-content: space-between; gap: 8px;
		width: 100%; margin: 0 0 10px; padding: 9px 12px; box-sizing: border-box;
		background: #10131c; border: 1px solid #1e2a40; border-radius: 8px;
		color: #cbd5e1; font-size: 13px; font-weight: 600; cursor: pointer;
	}
	html.ds-office-mode #ds-office-cats:hover { border-color: #ff6b35; color: #eef3ff; }
	html.ds-office-mode #ds-office-cats .chev { transition: transform .15s; }
	html.ds-office-mode #ds-office-cats.open .chev { transform: rotate(180deg); }
	</style>
	<script>
	(function () {
		function go() {
			var r = document.documentElement;
			if (r.className.indexOf('ds-office-mode') === -1) { return; }
			var sb = document.querySelector('.ds-mkt-sb');
			var wrap = document.querySelector('.ds-mkt');
			if (!sb || !wrap || document.getElementById('ds-office-cats')) { return; }
			var btn = document.createElement('button');
			btn.id = 'ds-office-cats';
			btn.type = 'button';
			btn.innerHTML = '<span>Categories</span><span class="chev">&#9662;</span>';
			var active = sb.querySelector('.ds-mkt-main-btn.active') || sb.querySelector('a.active');
			if (active) { btn.firstChild.textContent = 'Categories: ' + active.textContent.trim(); }
			sb.hidden = true;
			btn.addEventListener('click', function () {
				sb.hidden = !sb.hidden;
				btn.className = sb.hidden ? '' : 'open';
			});
			wrap.insertBefore(btn, wrap.firstChild);
		}
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', go);
		} else { go(); }
		setTimeout(go, 1200);
		setTimeout(go, 3000);
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'ds_office_marketplace_compact', 99 );

/**
 * AppSource reviewers open the add-in's Support and Terms links first.
 *
 * /support/ : the manifest's SupportUrl. When no real page exists at that
 *             address, send people to the contact page (Settings > DesignStager
 *             Office > Support page, default /contact/).
 * /terms/   : Terms of Use. The site already has the text as the [ds_terms]
 *             shortcode, but no page used it. When no real page exists at
 *             /terms/ (or /terms-of-use/, /terms-and-conditions/), render that
 *             shortcode inside the theme. A real WordPress page at the same
 *             address always wins, because this only runs on a 404.
 */
function ds_office_legal_pages() {
	if ( ! is_404() ) {
		return;
	}
	$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : ''; // phpcs:ignore
	$path = '/' . trim( strtolower( $path ), '/' ) . '/';

	if ( in_array( $path, array( '/support/', '/help/' ), true ) ) {
		$o    = class_exists( 'DS_Office_Bridge' ) ? DS_Office_Bridge::opts() : array();
		$dest = ! empty( $o['support_path'] ) ? '/' . trim( (string) $o['support_path'], '/' ) . '/' : '/contact/';
		if ( $dest === $path ) {
			$dest = '/contact/';
		}
		wp_safe_redirect( home_url( $dest ), 301 );
		exit;
	}

	if ( in_array( $path, array( '/terms/', '/terms-of-use/', '/terms-and-conditions/', '/terms-of-service/' ), true )
		&& shortcode_exists( 'ds_terms' ) ) {
		global $wp_query;
		$wp_query->is_404 = false;
		status_header( 200 );
		add_filter( 'pre_get_document_title', function () {
			return 'Terms of Use - ' . get_bloginfo( 'name' );
		} );
		$html = do_shortcode( '[ds_terms]' );
		/* The default text still names the staging mailbox. */
		$live = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$live = preg_replace( '/^www\./i', '', $live );
		$html = preg_replace( '/stagingtest\.designstager\.com/i', $live, $html );
		get_header();
		echo '<main id="ds-terms-page" class="site-main">' . $html . '</main>'; // phpcs:ignore
		get_footer();
		exit;
	}
}
add_action( 'template_redirect', 'ds_office_legal_pages', 1 );

function ds_office_preview_stream() {
	$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : ''; // phpcs:ignore
	if ( ! $path || ! preg_match( '#/ds-office/(?:[0-9][0-9.]*/)?preview$#', $path ) ) {
		return;
	}
	if ( function_exists( 'do_action' ) ) {
		do_action( 'litespeed_control_set_nocache', 'ds office preview' );
	}
	nocache_headers();
	$stop = function ( $code, $msg ) {
		status_header( $code );
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo esc_html( $msg );
		exit;
	};
	if ( ! is_user_logged_in() ) { $stop( 401, 'Please log in first.' ); }
	$url = isset( $_GET['url'] ) ? esc_url_raw( wp_unslash( $_GET['url'] ) ) : ''; // phpcs:ignore
	$up   = wp_upload_dir();
	$base = trailingslashit( (string) $up['baseurl'] );
	$alt  = set_url_scheme( $base, 'https' );
	if ( '' === $url ) { $stop( 400, 'Nothing to preview.' ); }
	$rel = '';
	if ( 0 === strpos( $url, $base ) ) { $rel = substr( $url, strlen( $base ) ); }
	elseif ( 0 === strpos( $url, $alt ) ) { $rel = substr( $url, strlen( $alt ) ); }
	else { $stop( 400, 'That file is not in the library.' ); }
	$rel = str_replace( '\\', '/', (string) strtok( $rel, '?' ) );
	if ( '' === $rel || false !== strpos( $rel, '..' ) ) { $stop( 400, 'That file is not in the library.' ); }
	$file = trailingslashit( (string) $up['basedir'] ) . $rel;
	$ext  = strtolower( (string) pathinfo( $file, PATHINFO_EXTENSION ) );
	$mimes = array(
		'mp3' => 'audio/mpeg', 'm4a' => 'audio/mp4', 'wav' => 'audio/wav',
		'ogg' => 'audio/ogg',  'aac' => 'audio/aac',
		'mp4' => 'video/mp4',  'webm' => 'video/webm', 'mov' => 'video/quicktime',
	);
	if ( ! isset( $mimes[ $ext ] ) ) { $stop( 415, 'Only audio and video can be previewed.' ); }
	$bytes = null;
	$cf    = '';
	if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
		/* Not on this disk. Keep a private copy in the system temp directory
		   (not web-served) so scrubbing a clip does not re-fetch it each time. */
		$cdir = trailingslashit( get_temp_dir() ) . 'ds-office-preview';
		if ( ! is_dir( $cdir ) ) { @mkdir( $cdir, 0700, true ); } // phpcs:ignore
		$cf = trailingslashit( $cdir ) . md5( $url ) . '.' . $ext;
		if ( file_exists( $cf ) && is_readable( $cf ) && filesize( $cf ) > 0
			&& ( time() - (int) filemtime( $cf ) ) < DAY_IN_SECONDS ) {
			$file = $cf;
		}
	}
	if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
		/* Staging (and any offloaded-media setup) may not hold the file on this
		   disk. Fall back to fetching it the way the insert proxy does. */
		$cookies = array();
		foreach ( $_COOKIE as $ck => $cv ) { // phpcs:ignore
			if ( is_string( $cv ) ) {
				$cookies[] = new WP_Http_Cookie( array( 'name' => $ck, 'value' => $cv ) );
			}
		}
		$got = wp_remote_get( $url, array( 'timeout' => 30, 'cookies' => $cookies, 'headers' => array( 'Accept-Encoding' => 'identity' ) ) );
		if ( is_wp_error( $got ) || (int) wp_remote_retrieve_response_code( $got ) >= 400 ) {
			$stop( 404, 'Preview not available.' );
		}
		$bytes = (string) wp_remote_retrieve_body( $got );
		if ( '' === $bytes ) { $stop( 404, 'Preview not available.' ); }
		if ( '' !== $cf ) {
			@file_put_contents( $cf, $bytes ); // phpcs:ignore
			if ( file_exists( $cf ) && filesize( $cf ) > 0 ) { $file = $cf; $bytes = null; }
		}
	}
	$size  = ( null === $bytes ) ? (int) filesize( $file ) : strlen( $bytes );
	$start = 0;
	$end   = $size - 1;
	if ( isset( $_SERVER['HTTP_RANGE'] ) && preg_match( '/bytes=(\\d*)-(\\d*)/', (string) wp_unslash( $_SERVER['HTTP_RANGE'] ), $m ) ) { // phpcs:ignore
		if ( '' !== $m[1] ) { $start = (int) $m[1]; }
		if ( '' !== $m[2] ) { $end = (int) $m[2]; }
		if ( $end >= $size ) { $end = $size - 1; }
		if ( $start > $end || $start >= $size ) {
			status_header( 416 );
			header( 'Content-Range: bytes */' . $size );
			exit;
		}
		status_header( 206 );
		header( 'Content-Range: bytes ' . $start . '-' . $end . '/' . $size );
	}
	header( 'Content-Type: ' . $mimes[ $ext ] );
	header( 'Accept-Ranges: bytes' );
	header( 'Content-Length: ' . ( $end - $start + 1 ) );
	header( 'Content-Disposition: inline' );
	header( 'X-Content-Type-Options: nosniff' );
	while ( ob_get_level() ) { ob_end_clean(); }
	if ( null !== $bytes ) {
		echo substr( $bytes, $start, $end - $start + 1 ); // phpcs:ignore
		exit;
	}
	$fh = fopen( $file, 'rb' ); // phpcs:ignore
	if ( ! $fh ) { $stop( 500, 'Preview not available.' ); }
	fseek( $fh, $start );
	$left = $end - $start + 1;
	while ( $left > 0 && ! feof( $fh ) ) {
		$chunk = fread( $fh, (int) min( 262144, $left ) ); // phpcs:ignore
		if ( false === $chunk || '' === $chunk ) { break; }
		echo $chunk; // phpcs:ignore
		$left -= strlen( $chunk );
		flush();
	}
	fclose( $fh ); // phpcs:ignore
	exit;
}
add_action( 'wp_loaded', 'ds_office_preview_stream', 4 );

/**
 * Marketplace search, the same way the website's /marketplace/ search works
 * (ds-marketplace-shortcode 04/28): matches titles and descriptions AND the
 * dlm_download_tag names, which include the tags Groq's vision model writes
 * on every approved design (DS Marketplace AI Auto-Tagger). If nothing
 * matches the whole phrase, each word (2+ letters, up to 6) is tried.
 * Returns matching dlm_download ids; the caller keeps its own category
 * filter, so a search stays inside the section it was made from.
 */
function ds_office_mkt_search_ids( $q ) {
	$q = trim( (string) $q );
	if ( '' === $q ) {
		return array();
	}
	$norm = trim( preg_replace( '/[^\p{L}\p{N}]+/u', ' ', $q ) );
	$match = function ( $text ) {
		$ids = get_posts( array(
			'post_type' => 'dlm_download', 'post_status' => 'publish',
			'posts_per_page' => -1, 'fields' => 'ids', 'suppress_filters' => true,
			's' => $text,
		) );
		$tags = get_terms( array(
			'taxonomy' => 'dlm_download_tag', 'hide_empty' => false,
			'fields' => 'ids', 'name__like' => $text,
		) );
		if ( $tags && ! is_wp_error( $tags ) ) {
			$ids = array_merge( (array) $ids, (array) get_posts( array(
				'post_type' => 'dlm_download', 'post_status' => 'publish',
				'posts_per_page' => -1, 'fields' => 'ids', 'suppress_filters' => true,
				'tax_query' => array( array( 'taxonomy' => 'dlm_download_tag', 'field' => 'term_id', 'terms' => $tags ) ),
			) ) );
		}
		return (array) $ids;
	};
	$found = array_values( array_unique( array_map( 'intval', array_merge(
		$match( '' !== $norm ? $norm : $q ),
		array()
	) ) ) );
	if ( ! $found ) {
		$words = array_filter( preg_split( '/[^\p{L}\p{N}]+/u', $q ), function ( $w ) { return mb_strlen( $w ) >= 2; } );
		foreach ( array_slice( array_values( array_unique( $words ) ), 0, 6 ) as $w ) {
			$found = array_merge( $found, $match( $w ) );
		}
		$found = array_values( array_unique( array_map( 'intval', $found ) ) );
	}
	return $found;
}

function ds_office_extra_kind_map() {
    /*
     * Candidate category slugs per tab. Staging and production do not use the
     * same names - production files video under "videos", staging under
     * "motion-graphics", and audio is split into sub-categories on both - so
     * every slug that exists is matched, and the rest are ignored.
     */
    return array(
        'videos' => array( 'videos', 'motion-graphics', 'video', 'motion' ),
        'audios' => array(
            'audio', 'audios', 'ai-music', 'ambience', 'background-music',
            'cinematic-trailer', 'jingles-intros', 'sound-effects',
        ),
        'icons'  => array( 'icons', 'icon' ),
    );
}

/**
 * The term ids, among a tab's candidate slugs, that actually exist here.
 */
function ds_office_extra_term_ids( $slugs ) {
    $ids = array();
    foreach ( (array) $slugs as $slug ) {
        $term = get_term_by( 'slug', $slug, 'dlm_download_category' );
        if ( $term && ! is_wp_error( $term ) ) {
            $ids[] = (int) $term->term_id;
        }
    }
    return array_values( array_unique( $ids ) );
}

/**
 * Download Monitor lets a version's file be a server disk path instead of a
 * URL, e.g. /home/<acct>/domains/designstager.com/public_html/wp-content/
 * uploads/2026/ai-videos/clip.mp4. That is how the marketplace videos are
 * stored. Glued onto the site host it becomes a URL that 404s, so preview
 * shows only a play glyph and "Insert video" fails with HTTP 404. Turn any
 * such path back into the public uploads URL. Real URLs pass through as-is.
 */
function ds_office_extra_path_to_url( $f ) {
	$f = trim( (string) $f );
	if ( '' === $f || preg_match( '#^(https?:)?//#i', $f ) ) { return $f; }
	$f    = str_replace( '\\', '/', $f );
	$up   = wp_upload_dir();
	$bdir = trailingslashit( str_replace( '\\', '/', (string) $up['basedir'] ) );
	$burl = trailingslashit( (string) $up['baseurl'] );
	if ( 0 === strpos( $f, $bdir ) ) {
		return $burl . ltrim( substr( $f, strlen( $bdir ) ), '/' );
	}
	$pos = strpos( $f, '/wp-content/uploads/' );
	if ( false !== $pos ) {
		return $burl . ltrim( substr( $f, $pos + strlen( '/wp-content/uploads/' ) ), '/' );
	}
	$abs = trailingslashit( str_replace( '\\', '/', ABSPATH ) );
	if ( 0 === strpos( $f, $abs ) ) {
		return site_url( '/' . ltrim( substr( $f, strlen( $abs ) ), '/' ) );
	}
	return site_url( '/' . ltrim( $f, '/' ) );
}

function ds_office_extra_file_url( $post_id ) {
	return ds_office_extra_path_to_url( ds_office_extra_file_raw( $post_id ) );
}

function ds_office_extra_file_raw( $post_id ) {
    if ( function_exists( 'download_monitor' ) ) {
        try {
            $dl = download_monitor()->service( 'download_repository' )->retrieve_single( $post_id );
            if ( $dl && $dl->has_version() ) {
                $m = $dl->get_version()->get_mirrors();
                if ( ! empty( $m[0] ) ) { return (string) $m[0]; }
            }
        } catch ( Exception $e ) {}
    }
    global $wpdb;
    $raw = $wpdb->get_var( $wpdb->prepare(
        "SELECT pm.meta_value FROM {$wpdb->postmeta} pm
          INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
          WHERE p.post_parent = %d AND p.post_type = 'dlm_download_version'
            AND pm.meta_key = '_files' ORDER BY p.post_date DESC LIMIT 1",
        $post_id
    ) );
    if ( $raw ) {
        $files = maybe_unserialize( $raw );
        if ( is_string( $files ) ) {
            $dec = json_decode( $files, true );
            if ( is_array( $dec ) ) { $files = $dec; }
        }
        if ( is_array( $files ) && ! empty( $files[0] ) ) { return (string) $files[0]; }
        if ( is_string( $files ) && '' !== $files ) { return $files; }
    }
    return '';
}

/**
 * Keep media on the host the add-in is talking to, so a preview in the task
 * pane is same-origin and the proxy does not have to cross domains.
 */
function ds_office_extra_local_url( $url ) {
	$url = (string) $url;
	if ( '' === $url ) { return ''; }
	$p = wp_parse_url( $url );
	if ( empty( $p['path'] ) || false === strpos( $p['path'], '/wp-content/uploads/' ) ) { return $url; }
	$home = wp_parse_url( home_url() );
	if ( empty( $home['host'] ) ) { return $url; }
	$scheme = ! empty( $home['scheme'] ) ? $home['scheme'] : 'https';
	return $scheme . '://' . $home['host'] . ( ! empty( $home['port'] ) ? ':' . $home['port'] : '' ) . $p['path'];
}

/* The file to insert for a video / audio / icon item: the file itself, or its
   Download Monitor link when it sits in the protected dlm_uploads folder. */
function ds_office_extra_insert_url( $post_id ) {
    $u = ds_office_extra_local_url( ds_office_extra_file_url( $post_id ) );
    if ( $u && false !== strpos( $u, '/dlm_uploads/' ) && function_exists( 'download_monitor' ) ) {
        try {
            $dl = download_monitor()->service( 'download_repository' )->retrieve_single( $post_id );
            if ( $dl ) {
                $link = $dl->get_the_download_link();
                if ( $link ) { return $link; }
            }
        } catch ( Exception $e ) {}
    }
    return $u;
}

/* A download is Pro when it carries the 'pro' tag - the same rule the
 * Marketplace tab uses. Videos, icons, audios and images were hard-coded
 * as free here, so paid items looked free in the pane. */
function ds_office_extra_tier( $post_id ) {
	if ( function_exists( 'ds_ppt_mkt_item_tier' ) ) {
		return 'pro' === ds_ppt_mkt_item_tier( $post_id ) ? 'pro' : 'free';
	}
	$terms = get_the_terms( $post_id, 'dlm_download_tag' );
	if ( is_array( $terms ) ) {
		foreach ( $terms as $t ) {
			if ( 'pro' === strtolower( $t->slug ) ) {
				return 'pro';
			}
		}
	}
	return 'free';
}

function ds_office_extra_can_pro() {
	if ( ! is_user_logged_in() ) {
		return false;
	}
	$tier = function_exists( 'ds_marketplace_get_user_tier' ) ? ds_marketplace_get_user_tier() : 'free';
	return in_array( $tier, array( 'premium', 'pro', 'business' ), true );
}

function ds_office_extra_library() {
    $path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
    if ( ! $path || ! preg_match( '#/ds-office/(?:[0-9][0-9.]*/)?library$#', $path ) ) { return; }
    $kind = isset( $_GET['kind'] ) ? sanitize_key( wp_unslash( $_GET['kind'] ) ) : '';
    $map  = ds_office_extra_kind_map();
    if ( ! isset( $map[ $kind ] ) ) { return; }

    nocache_headers();
    header( 'Content-Type: application/json; charset=utf-8' );

    $term_ids = ds_office_extra_term_ids( $map[ $kind ] );
    if ( ! $term_ids ) {
        $names = implode( '", "', (array) $map[ $kind ] );
        echo wp_json_encode( array(
            'items' => array(), 'page' => 1, 'pages' => 0, 'total' => 0,
            'kind' => $kind, 'auth' => array( 'loggedIn' => is_user_logged_in() ? 1 : 0 ),
            'note' => 'No "' . $names . '" category on this site yet.',
        ) );
        exit;
    }

    $page   = max( 1, isset( $_GET['page'] ) ? (int) $_GET['page'] : 1 );
    $q      = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ( isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '' );
    $logged = is_user_logged_in();

    $args = array(
        'post_type'      => 'dlm_download',
        'post_status'    => 'publish',
        'posts_per_page' => 24,
        'paged'          => $page,
        'orderby'        => array( 'date' => 'DESC', 'ID' => 'DESC' ),
        'tax_query'      => array( array(
            'taxonomy'         => 'dlm_download_category',
            'field'            => 'term_id',
            'terms'            => $term_ids,
            'include_children' => true,
        ) ),
    );
    if ( '' !== $q ) {
        $hits = ds_office_mkt_search_ids( $q );
        $args['post__in'] = $hits ? $hits : array( 0 );
    }

    $query = new WP_Query( $args );
    $items = array();
    foreach ( $query->posts as $post ) {
        $tid = (int) get_post_thumbnail_id( $post->ID );
        $thumb = '';
        if ( $tid ) {
            $thumb = wp_get_attachment_image_url( $tid, 'medium_large' );
            if ( ! $thumb ) { $thumb = wp_get_attachment_image_url( $tid, 'large' ); }
            if ( ! $thumb ) { $thumb = wp_get_attachment_image_url( $tid, 'medium' ); }
            /* Same as the Images tab: never hand the grid a multi-MB original. */
            $orig = wp_get_attachment_url( $tid );
            if ( $thumb && $orig && strtok( $thumb, '?' ) === strtok( $orig, '?' )
                && 0 === strpos( (string) get_post_mime_type( $tid ), 'image/' ) && 'image/gif' !== get_post_mime_type( $tid ) ) {
                $thumb = DS_Office_Bridge::asset_url( 'thumb', array( 'id' => $tid ) );
            }
        }
        /* Videos: use the poster image the website's marketplace already makes
           (_ds_video_poster on the video attachment). Without it the pane loads
           every clip through PHP at once, the server answers some with HTTP 500,
           and those cards show only a play glyph. */
        if ( ! $thumb && 'videos' === $kind ) {
            $vids = $tid ? array( $tid ) : array();
            foreach ( get_children( array( 'post_parent' => $post->ID, 'post_type' => 'attachment', 'post_mime_type' => 'video', 'fields' => 'ids' ) ) as $cid ) {
                $vids[] = (int) $cid;
            }
            foreach ( $vids as $vid ) {
                $poster = function_exists( 'ds_mkt_poster_url' ) ? ds_mkt_poster_url( $vid ) : (string) get_post_meta( $vid, '_ds_video_poster', true );
                if ( $poster ) { $thumb = ds_office_extra_local_url( $poster ); break; }
            }
        }
        $items[] = array(
            'id'     => (int) $post->ID,
            'title'  => html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, 'UTF-8' ),
            'thumb'  => $thumb ? $thumb : '',
            'kind'   => $kind,
            'tier'   => ds_office_extra_tier( $post->ID ),
            'locked' => ( 'pro' === ds_office_extra_tier( $post->ID ) && ! ds_office_extra_can_pro() ),
            'insert' => $logged ? ds_office_extra_insert_url( $post->ID ) : '',
        );
    }

    echo wp_json_encode( array(
        'items' => $items,
        'page'  => $page,
        'pages' => (int) $query->max_num_pages,
        'total' => (int) $query->found_posts,
        'kind'  => $kind,
        'auth'  => array( 'loggedIn' => $logged ? 1 : 0 ),
    ) );
    exit;
}
add_action( 'wp_loaded', 'ds_office_extra_library', 4 );

final class DS_Office_Bridge {

	const OPT     = 'ds_office_bridge';
	const VERSION = '1.10.0';

	public static function defaults() {
		return array(
			'enabled'       => 1,
			// Extra CSS selectors for download buttons that are not plain <a href="file.ext"> links.
			'selectors'     => '',
			// CSS applied ONLY inside PowerPoint (e.g. hide header, footer, cookie bar).
			'hide_css'      => "#wpadminbar{display:none!important}\nhtml{margin-top:0!important}",
			// How inserted .pptx slides are styled: KeepSourceFormatting | UseDestinationTheme.
			'formatting'    => 'KeepSourceFormatting',
			'dialog_width'  => 95,
			'dialog_height' => 95,
			// Page the PowerPoint window opens on (relative to the site home).
			'start_path'    => '/',
			// Where the task pane sends people to sign in / sign up. AppSource
			// rejects add-ins whose authentication entry points are not obvious.
			'login_path'    => '/login/',
			'signup_path'   => '/register/',
			'account_path' => '/my-account/',
			// Real support page. AppSource requires SupportUrl to be a genuine
			// support destination, not the marketing home page.
			'support_path'  => '/contact/',
			// Hide pricing, upgrade and checkout UI while the site is open inside
			// PowerPoint. Marketplace policy 1100.3 does not allow an add-in to
			// up-sell or send people out to buy elsewhere.
			'hide_upsell'   => 1,
			// URL fragments that mean "this is a download" (Download Monitor uses /download/ID/).
			'url_patterns'  => '/download/',
			// Other hosts whose files may be fetched for the user (e.g. where uploads really live).
			'proxy_hosts'   => self::default_proxy_hosts(),
			// AI Suites tile pictures chosen in Settings > DesignStager Office (key => image URL).
			'tile_thumbs'   => array(),
		);
	}

	/* AI Suites tiles whose picture can be set in the admin: key => label. */
	public static function tile_keys() {
		return array(
			'presentation' => 'AI Presentation',
			'matcher'      => 'Template Matcher',
			'marketplace'  => 'Marketplace',
			'image'        => 'AI Image',
			'video'        => 'AI Video',
			'audio'        => 'AI Audio',
			'generations'  => 'My Generations',
			'account'      => 'My Account',
		);
	}

	public static function default_proxy_hosts() {
		$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$root = preg_replace( '/^(www|staging[a-z0-9-]*)\./i', '', $host );
		return $root !== $host ? $root . ', www.' . $root : '';
	}

	public static function proxy_hosts() {
		$o     = self::opts();
		$hosts = array( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
		foreach ( explode( ',', (string) $o['proxy_hosts'] ) as $h ) {
			$h = strtolower( trim( $h ) );
			if ( $h ) {
				$hosts[] = $h;
			}
		}
		return array_values( array_unique( $hosts ) );
	}

	public static function opts() {
		$saved = get_option( self::OPT, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
	}

	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'print_loader' ), 1 );
		add_action( 'init', array( __CLASS__, 'auth_by_token' ), -100 );
		add_action( 'init', array( __CLASS__, 'maybe_serve_asset' ), 0 );
		// Served later than init 0: Download Monitor registers dlm_download_category on init,
		// so at priority 0 get_term_by() fails and the category filter is silently dropped.
		add_action( 'wp_loaded', array( __CLASS__, 'maybe_serve_library' ), 5 );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'admin_notices', array( __CLASS__, 'health_notice' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_post_ds_office_manifest', array( __CLASS__, 'download_manifest' ) );
	}

	/* ------------------------------------------------------------------
	 * Front end: a ~1 KB inline loader. It only activates when the page
	 * was opened by the PowerPoint add-in (?ds_office=1, remembered for
	 * that window in sessionStorage). Normal visitors load nothing extra.
	 * ------------------------------------------------------------------ */
	public static function print_loader() {
		$o = self::opts();
		if ( empty( $o['enabled'] ) ) {
			return;
		}
		$cfg = array(
			'bridge'    => self::asset_url( 'bridge.js' ),
			'proxy'     => self::asset_url( 'fetch' ),
			'mediaTemplate' => self::asset_url( 'media-template.pptx' ),
			'proxyHosts' => self::proxy_hosts(),
			'patterns'  => array_values( array_filter( array_map( 'trim', explode( ',', (string) $o['url_patterns'] ) ) ) ),
			'selectors' => (string) $o['selectors'],
			'hideCss'   => self::office_css( $o ),
			/* Side-panel view (page shown inside the PowerPoint task pane): only the tool itself, no site header, tool tabs, breadcrumbs, footer or chat bubble. The large window keeps the whole page. */
			'inlineCss' => 'html.ds-office-inline #wpadminbar,html.ds-office-inline #masthead,html.ds-office-inline #colophon,html.ds-office-inline .site-header,html.ds-office-inline .site-footer,html.ds-office-inline .skip-link,html.ds-office-inline .dst-bar,html.ds-office-inline .dstv-bar,html.ds-office-inline div:has(>.dst-bar),html.ds-office-inline div:has(>.dstv-bar),html.ds-office-inline .dsAI-bc,html.ds-office-inline .dsAIV-bc,html.ds-office-inline #ds-suji-btn,html.ds-office-inline #ast-scroll-top{display:none!important}html.ds-office-inline #ds30-screen.open{flex-direction:column!important;overflow-y:auto!important}html.ds-office-inline #ds30-sidebar{width:100%!important;max-width:none!important;flex:0 0 auto!important;height:auto!important;min-height:0!important}html.ds-office-inline #ds30-sidebar .ds30-sb-body{flex:0 0 auto!important;overflow:visible!important}html.ds-office-inline #ds30-content-input{height:120px!important;min-height:120px!important}html.ds-office-inline #ds30-main{width:100%!important;max-width:none!important;flex:0 0 auto!important;height:auto!important;min-height:0!important}html.ds-office-inline #ds30-grid-wrap{height:auto!important;max-height:none!important;overflow:visible!important}html.ds-office-inline #ds30-grid-v2{width:auto!important;grid-template-columns:repeat(auto-fill,minmax(150px,1fr))!important}html.ds-office-inline{margin-top:0!important}html.ds-office-inline body,html.ds-office-inline #page,html.ds-office-inline #content,html.ds-office-inline .site-content,html.ds-office-inline .ast-container,html.ds-office-inline #primary,html.ds-office-inline #main,html.ds-office-inline article,html.ds-office-inline .entry-content{margin-top:0!important;padding-top:0!important}html.ds-office-inline #dsp-home.dssl #dsp-mode-row{display:flex!important;flex-direction:column!important;flex-wrap:nowrap!important;gap:10px!important}html.ds-office-inline #dsp-home.dssl #dsp-mode-row>*{flex:none!important;width:100%!important;max-width:none!important;min-width:0!important;box-sizing:border-box!important}html.ds-office-inline body.dsfp #dsp-mode-row>.dsfp-locked::after{position:static!important;display:inline-block!important;margin-top:8px!important}@media (max-width:1024px){html.ds-office-inline:not(.dsx-lay-open) #dsp-vp-panel{display:none!important}html.ds-office-inline:not(.dsx-lay-open) body.vp-showing #dsp-editor-fullscreen.dsp-open{bottom:0!important}html.ds-office-inline.dsx-lay-open #dsp-vp-panel{box-shadow:0 -10px 30px rgba(0,0,0,.5)!important}html.ds-office-inline #dsp-vp-panel .vp-card{flex:0 0 calc(var(--dsmb-cw,100px) * 1.6)!important;width:calc(var(--dsmb-cw,100px) * 1.6)!important}html.ds-office-inline #dsx-lay-btn{order:2;padding:11px 18px;border:1px solid #4b5563;border-radius:2px;background:#2a2d33;color:#fff;font:800 13px/1 -apple-system,"Segoe UI",Arial,sans-serif;letter-spacing:.2px;cursor:pointer;white-space:nowrap}html.ds-office-inline.dsx-lay-open #dsx-lay-btn{background:#f15a29;border-color:#f15a29}}html.ds-office-inline :is(#dsel-bar,#dsel-dock){padding:4px 8px!important;gap:4px 5px!important;border-radius:12px!important}html.ds-office-inline :is(#dsel-bar,#dsel-dock) :is(.dsel-left,.dsel-right){gap:4px!important}html.ds-office-inline :is(#dsel-bar,#dsel-dock) .dsel-left{min-height:22px!important}html.ds-office-inline :is(#dsel-bar,#dsel-dock) .dsel-sep{min-height:16px!important;margin:0 2px!important}html.ds-office-inline :is(#dsel-bar,#dsel-dock) .dsel-b{height:22px!important;padding:0 7px!important;font-size:11px!important;border-radius:6px!important;gap:4px!important}html.ds-office-inline :is(#dsel-bar,#dsel-dock) .dsel-b.dsel-ic{width:22px!important;padding:0!important;font-size:12px!important}html.ds-office-inline :is(#dsel-bar,#dsel-dock) .dsel-b svg{width:12px!important;height:12px!important}html.ds-office-inline :is(#dsel-bar,#dsel-dock) :is(.dsel-hint,.dsel-lbl,.dsel-kind){font-size:10px!important}html.ds-office-inline :is(#dsel-bar,#dsel-dock) :is(input,select){height:22px!important;font-size:11px!important}html.ds-office-inline #dsel-keys button{height:22px!important;padding:0 7px!important;font-size:10.5px!important}html.ds-office-inline #dsel-keys button svg{width:12px!important;height:12px!important}html.ds-office-inline #dsp-ppt-bottom-bar{gap:5px!important;padding:5px 8px!important}html.ds-office-inline #dsp-ed-theme-trigger.dsp-ed-theme-trigger-barfixed,html.ds-office-inline #ds-tpl-pick-btn,html.ds-office-inline #dsx-lay-btn{padding:6px 10px!important;font-size:11px!important}html.ds-office-inline #dsp-ed-theme-trigger.dsp-ed-theme-trigger-barfixed::before{font-size:11px!important}html.ds-office-inline #dsp-bottom-pill{padding:2px 6px!important}html.ds-office-inline #dsp-bottom-pill button{width:22px!important;height:22px!important;flex:0 0 22px!important}html.ds-office-inline #dsp-bottom-pill #dsp-ppt-nav-lbl{font-size:10.5px!important;min-width:34px!important}html.ds-office-inline #dsp-ppt-toolbar{min-height:34px!important;padding:3px 8px!important;gap:6px!important}html.ds-office-inline #dsp-ppt-toolbar .dsp-tb-back{padding:3px 5px!important;font-size:10.5px!important}html.ds-office-inline #dsp-ppt-toolbar .dsp-tb-build,html.ds-office-inline #dsp-ppt-toolbar #dsp-tb-sharebtn{padding:5px 8px!important;font-size:10.5px!important}html.ds-office-inline #dsp-ppt-toolbar #dsp-etitle{font-size:12px!important}html.ds-office-inline #dsp-ppt-toolbar #dsfp-chip{font-size:8.5px!important;padding:2px 5px!important}',
			'noUpsell'  => empty( $o['hide_upsell'] ) ? 0 : 1,
		);
		?>
<script id="ds-office-loader" data-no-optimize="1" data-no-defer="1" data-cfasync="false">/* DesignStager Office Bridge - inactive unless opened from the PowerPoint add-in */
(function(c){try{var q=new URLSearchParams(location.search);if(q.get('ds_office')==='1')sessionStorage.setItem('ds_office','1');if(q.get('ds_office')==='0')sessionStorage.removeItem('ds_office');if(sessionStorage.getItem('ds_office')!=='1')return;}catch(e){return;}
window.DSOfficeConfig=c;var d=document,r=d.documentElement;r.className+=' ds-office-mode';try{window.__dsInl=window.parent!==window&&!!window.parent.DSAddin;}catch(e){window.__dsInl=false;}if(window.__dsInl){r.className+=' ds-office-inline';if(c.inlineCss){var si=d.createElement('style');si.id='ds-office-inline-css';si.textContent=c.inlineCss;d.head.appendChild(si);}}
if(c.hideCss){var st=d.createElement('style');st.id='ds-office-css';st.textContent=c.hideCss;d.head.appendChild(st);}
var h=window.history,ps=h&&h.pushState,rs=h&&h.replaceState;var nat=window.__dsNative={alert:window.alert,confirm:window.confirm,prompt:window.prompt};
var s=d.createElement('script');s.src='https://appsforoffice.microsoft.com/lib/1/hosted/office.js';
s.onload=function(){try{if(h&&!h.pushState){h.pushState=ps;h.replaceState=rs;}window.alert=nat.alert;window.confirm=nat.confirm;window.prompt=nat.prompt;}catch(e){}var b=d.createElement('script');b.src=c.bridge;d.head.appendChild(b);};
if(window.__dsInl){var b0=d.createElement('script');b0.src=c.bridge;d.head.appendChild(b0);}else d.head.appendChild(s);})(<?php echo wp_json_encode( $cfg ); ?>);</script>
		<?php
	}

	/* ------------------------------------------------------------------
	 * Add-in files are served by WordPress itself: /?ds_office_asset=NAME
	 * (no files to upload; works from a Code Snippet or a plugin).
	 * ------------------------------------------------------------------ */
	public static function asset_url( $name, $extra = array() ) {
		// Clean path, no query string: desktop PowerPoint refuses manifests whose URLs carry one.
		$url = home_url( '/ds-office/' . self::VERSION . '/' . $name );
		return empty( $extra ) ? $url : add_query_arg( $extra, $url );
	}

	public static function config() {
		$o = self::opts();
		return array(
			'startUrl'     => add_query_arg( 'ds_office', '1', home_url( $o['start_path'] ) ),
			'dialogWidth'  => max( 30, min( 100, (int) $o['dialog_width'] ) ),
			'dialogHeight' => max( 30, min( 100, (int) $o['dialog_height'] ) ),
			'formatting'   => 'UseDestinationTheme' === $o['formatting'] ? 'UseDestinationTheme' : 'KeepSourceFormatting',
		);
	}

	/* ------------------------------------------------------------------
	 * Health check. The add-in lives entirely at /ds-office/ URLs served by
	 * PHP, so if this plugin stops running every URL in the manifest 404s and
	 * PowerPoint shows "This add-in could not be started" - with nothing
	 * visibly wrong on the website itself. This makes that failure loud.
	 * ------------------------------------------------------------------ */
	public static function run_health_check() {
		$url = self::asset_url( 'health' );
		$res = wp_remote_get( $url, array( 'timeout' => 8, 'redirection' => 2 ) );

		if ( is_wp_error( $res ) ) {
			return array( 'ok' => 0, 'msg' => $res->get_error_message(), 'url' => $url );
		}

		$code = (int) wp_remote_retrieve_response_code( $res );
		if ( 200 !== $code ) {
			return array( 'ok' => 0, 'msg' => 'returned HTTP ' . $code, 'url' => $url );
		}

		$body = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! is_array( $body ) || empty( $body['ok'] ) ) {
			return array( 'ok' => 0, 'msg' => 'did not return the expected JSON', 'url' => $url );
		}

		return array( 'ok' => 1, 'msg' => 'serving correctly', 'url' => $url );
	}

	public static function health_state( $force = false ) {
		if ( $force ) {
			delete_transient( 'ds_office_health' );
		}
		$state = get_transient( 'ds_office_health' );
		if ( false === $state || ! is_array( $state ) ) {
			$state = self::run_health_check();
			set_transient( 'ds_office_health', $state, HOUR_IN_SECONDS );
		}
		return $state;
	}

	/*
	 * READ-ONLY. This must never perform the HTTP check itself.
	 *
	 * It runs on admin_notices, i.e. on every wp-admin page load - including
	 * the one WordPress uses to activate the plugin. A blocking loopback
	 * request there collides with WordPress's own activation loopback check,
	 * and on managed hosting that stalls long enough for activation to fail
	 * silently: the plugin simply stays inactive with no error shown.
	 *
	 * The live check runs only on this plugin's own settings page, where a
	 * slow request costs nothing but a slow page.
	 */
	public static function health_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$state = get_transient( 'ds_office_health' );
		if ( ! is_array( $state ) || ! empty( $state['ok'] ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p><strong>DesignStager Office Bridge:</strong> the PowerPoint add-in is not being served - '
			. esc_html( $state['msg'] ) . '. Until this is fixed the add-in shows an error for every user. Test URL: <code>'
			. esc_html( $state['url'] ) . '</code></p></div>';
	}

		/* -----------------------------------------------------------------
	 * Panel sign-in without third-party cookies.
	 *
	 * In PowerPoint on the web the task pane is an iframe of this site
	 * inside office.com, so the browser withholds the WordPress login
	 * cookie (SameSite=Lax) and the pane always read as signed out.
	 * The big window is a real top-level window here, so it still has
	 * the cookie: it fetches a short signed token and hands it to the
	 * pane, which sends it back as ds_t on every call.
	 * ----------------------------------------------------------------- */
	const TOKEN_TTL = 43200; // 12 hours

	public static function make_token( $uid ) {
		$uid = (int) $uid;
		if ( ! $uid ) {
			return '';
		}
		$exp = time() + self::TOKEN_TTL;
		$sig = hash_hmac( 'sha256', $uid . '|' . $exp, wp_salt( 'auth' ) );
		return $uid . '.' . $exp . '.' . substr( $sig, 0, 32 );
	}

	public static function token_user( $token ) {
		$parts = explode( '.', (string) $token );
		if ( 3 !== count( $parts ) ) {
			return 0;
		}
		$uid = (int) $parts[0];
		$exp = (int) $parts[1];
		if ( ! $uid || $exp < time() ) {
			return 0;
		}
		$want = substr( hash_hmac( 'sha256', $uid . '|' . $exp, wp_salt( 'auth' ) ), 0, 32 );
		return hash_equals( $want, (string) $parts[2] ) ? $uid : 0;
	}

	/* Signs the request in when it carries a valid ds_t token. */
	public static function auth_by_token() {
		if ( empty( $_GET['ds_t'] ) || is_user_logged_in() ) { // phpcs:ignore
			return;
		}
		$uid = self::token_user( sanitize_text_field( wp_unslash( $_GET['ds_t'] ) ) ); // phpcs:ignore
		if ( $uid ) {
			wp_set_current_user( $uid );
		}
	}

	public static function maybe_serve_asset() {
		$name = '';
		if ( ! empty( $_GET['ds_office_asset'] ) ) { // phpcs:ignore
			$name = sanitize_file_name( wp_unslash( $_GET['ds_office_asset'] ) ); // phpcs:ignore
		} else {
			// Clean paths: /ds-office/NAME or /ds-office/1.8.0/NAME
			$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : ''; // phpcs:ignore
			if ( $path && preg_match( '#/ds-office/(?:[0-9][0-9.]*/)?([A-Za-z0-9._-]+)$#', $path, $mm ) ) {
				$name = sanitize_file_name( $mm[1] );
			}
		}
		if ( '' === $name ) {
			return;
		}

		if ( 'token' === $name ) {
			nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			echo wp_json_encode( array(
				'token' => is_user_logged_in() ? self::make_token( get_current_user_id() ) : '',
				'ttl'   => self::TOKEN_TTL,
			) );
			exit;
		}
		if ( 'fetch' === $name ) {
			self::proxy();
		}
		if ( 'thumb' === $name ) {
			self::thumb();
		}
		if ( 'whoami' === $name ) {
			nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			$o    = self::opts();
			$home = untrailingslashit( home_url() );
			$rel  = function ( $url ) use ( $home ) {
				$url = (string) $url;
				if ( 0 === strpos( $url, $home ) ) {
					$url = substr( $url, strlen( $home ) );
				}
				return '' === $url ? '/' : $url;
			};
			$logged = is_user_logged_in();
			$who    = $logged ? wp_get_current_user() : null;
			$label  = '';
			if ( $who ) {
				$label = $who->display_name ? $who->display_name : $who->user_login;
			}
			echo wp_json_encode( array(
				'loggedIn'    => $logged ? 1 : 0,
				'name'        => $label,
				'signInPath'  => (string) $o['login_path'],
				'signUpPath'  => (string) $o['signup_path'],
				'accountPath' => (string) $o['account_path'],
				/* wp_logout_url() is HTML-escaped (&amp;), which hid the nonce and
				   left people on WordPress's "Do you really want to log out?" page. */
				'signOutPath' => $rel( html_entity_decode( wp_logout_url( home_url( '/' ) ), ENT_QUOTES, 'UTF-8' ) ),
                /* Same numbers the website's My Account popup shows: run the site's
                   own /ds/v1/credits route in-process, so no nonce is needed and the
                   add-in can never drift from the website. */
                'billing'     => ds_office_billing(),
			) );
			exit;
		}
		if ( 'health' === $name ) {
			nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			$hz = self::assets();
			$ho = self::opts();
			echo wp_json_encode( array(
				'ok'      => isset( $hz['taskpane.html'] ) ? 1 : 0,
				'version' => self::VERSION,
				'enabled' => empty( $ho['enabled'] ) ? 0 : 1,
				'assets'  => array_keys( $hz ),
				'time'    => gmdate( 'c' ),
			) );
			exit;
		}
		if ( 'config.json' === $name ) {
			nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			echo wp_json_encode( self::config() );
			exit;
		}
		if ( preg_match( '/^icon-(16|20|24|32|40|48|64|80|96|128)\.png$/', $name, $m ) ) {
			self::icon( (int) $m[1] );
		}
		$assets = self::assets();
		if ( ! isset( $assets[ $name ] ) ) {
			return;
		}
		list( $mime, $body ) = $assets[ $name ];
		if ( ! empty( $assets[ $name ][2] ) ) {
			$body = base64_decode( $body ); // phpcs:ignore
		}
		if ( 'taskpane.html' === $name ) {
			$body = str_replace( '{{COMMANDS_URL}}', esc_url( self::asset_url( 'commands.js' ) ), $body );
			$body = str_replace( '{{ICON_URL}}', esc_url( self::asset_url( 'icon-80.png' ) ), $body );
			$body = str_replace( '{{SITE_URL}}', esc_url( untrailingslashit( home_url() ) ), $body );
			$tp_o = self::opts();
			$tp_t = is_array( $tp_o['tile_thumbs'] ) ? $tp_o['tile_thumbs'] : array();
			$body = str_replace( '{{TILE_THUMBS}}', wp_json_encode( (object) array_map( 'esc_url_raw', $tp_t ) ), $body );
			if ( ! empty( $tp_o['hide_upsell'] ) ) {
				/* Policy 1100.3: no buying or upgrading from inside the add-in. */
				$body = str_replace( '</head>', "<style id=\"ds-no-upsell\">#ds-buy{display:none!important}</style>\n</head>", $body );
			}
		}
		if ( 'commands.js' === $name ) {
			$body = str_replace( '{{ADDIN_VERSION}}', self::VERSION, $body );
		}
		if ( function_exists( 'do_action' ) ) {
			do_action( 'litespeed_control_set_nocache', 'ds office asset' );
		}
		header( 'Content-Type: ' . $mime );
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		header( 'Pragma: no-cache' );
		header( 'X-Content-Type-Options: nosniff' );
		echo $body; // phpcs:ignore
		exit;
	}

	/*
	 * Same-site download proxy. Used when a download link redirects to a file
	 * the browser may not read directly (another domain, http, CDN). Only for
	 * logged-in users and only for links on this site; the visitor's own
	 * cookies are forwarded so permissions and download limits still apply.
	 */
	private static function proxy() {
		nocache_headers();
		$url = isset( $_GET['url'] ) ? esc_url_raw( wp_unslash( $_GET['url'] ) ) : ''; // phpcs:ignore
		$fail = function ( $code, $msg ) {
			status_header( $code );
			header( 'Content-Type: text/plain; charset=utf-8' );
			echo esc_html( $msg );
			exit;
		};
		if ( ! is_user_logged_in() ) {
			$fail( 401, 'Please log in first.' );
		}
		$site = wp_parse_url( home_url(), PHP_URL_HOST );
		$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		if ( ! $url || ! in_array( $host, self::proxy_hosts(), true ) || 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) ) {
			$fail( 400, 'This file cannot be inserted (host not allowed in Settings > DesignStager Office).' );
		}
		if ( isset( $_SERVER['HTTP_SEC_FETCH_SITE'] ) && 'same-origin' !== $_SERVER['HTTP_SEC_FETCH_SITE'] ) { // phpcs:ignore
			$fail( 403, 'Forbidden.' );
		}

		/* AI Studio's download buttons point at one of our own REST wrappers
		   (…/wp-json/ds/v1/download-audio?url=…&_wpnonce=…). That nonce belongs
		   to the browser's session and cannot be recreated here, so the inner
		   request would fail. The real file sits on the storage CDN in the url
		   parameter: fetch that directly instead. */
		if ( $host === $site && false !== strpos( (string) wp_parse_url( $url, PHP_URL_PATH ), '/wp-json/' ) ) {
			$q = array();
			parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $q );
			$inner = isset( $q['url'] ) ? esc_url_raw( (string) $q['url'] ) : '';
			$ihost = strtolower( (string) wp_parse_url( $inner, PHP_URL_HOST ) );
			$known = $ihost && ( in_array( $ihost, self::proxy_hosts(), true )
				|| preg_match( '#\.(cloudfront\.net|r2\.dev|amazonaws\.com|cloudflarestorage\.com|backblazeb2\.com)$#', $ihost ) );
			if ( $inner && $known && 'https' === wp_parse_url( $inner, PHP_URL_SCHEME ) ) {
				$url  = $inner;
				$host = $ihost;
			}
		}

		/* The pane is a cross-site iframe, so the browser sends us no cookie to
		   pass on. Carry a fresh token for this same user on the inner request
		   instead, so Download Monitor still sees who is asking. */
		if ( $host === $site ) {
			$url = add_query_arg( 'ds_t', self::make_token( get_current_user_id() ), $url );
		}
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : 'DesignStager Office';

		// 1) Ask the site itself (as this user) whether the download is allowed and where the file really is.
		//    This keeps Download Monitor's permission checks. Nothing big is downloaded here.
		$final = $url;
		/* A file in Download Monitor's protected folder is fetched once, through
		   its Download link with the visitor's cookies (step 2), so it counts
		   as one download and the plan checks still apply. */
		/* My Generations files in cloud storage (/wp-json/ds-hub/v1/file/ID): the
		   REST route needs a nonce, so look the file up here, for its owner only,
		   and fetch its short-lived storage link directly. */
		if ( $host === $site && preg_match( '#/ds-hub/v1/file/(\d+)#', (string) wp_parse_url( $url, PHP_URL_PATH ), $hm )
			&& function_exists( 'ds_hub_get_generated_file' ) && function_exists( 'ds_hub_user_owns_file' ) && function_exists( 'ds_hub_get_download_url' ) ) {
			$hub_file = ds_hub_get_generated_file( (int) $hm[1] );
			if ( ! $hub_file || ! ds_hub_user_owns_file( $hub_file, get_current_user_id() ) ) {
				$fail( 403, 'This file is not in your account.' );
			}
			$hub_url = ds_hub_get_download_url( $hub_file );
			if ( is_wp_error( $hub_url ) || ! $hub_url ) {
				$fail( 410, 'This file has expired and is no longer stored.' );
			}
			$url  = (string) $hub_url;
			$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		}
		/* Marketplace pictures, videos, audio and icons are inserted from their
		   own file in the uploads folder, not through a Download link, so
		   Download Monitor never saw the request and the insert was not counted
		   against the plan's download limit. Count it here, with the same
		   counter the website uses, before the file is fetched. */
		if ( $host === $site && function_exists( 'ds_dl_check_core' ) ) {
			$mk_id = self::mkt_item_for_file( $url );
			if ( $mk_id ) {
				$mk_uid  = get_current_user_id();
				$mk_seen = 'ds_office_cnt_' . $mk_uid . '_' . $mk_id;
				/* A retry of the same item within 30 seconds is one insert. */
				if ( ! get_transient( $mk_seen ) ) {
					$mk_chk = ds_dl_check_core( $mk_uid, self::mkt_item_is_pro( $mk_id ) ? 'pro' : 'free', true );
					if ( empty( $mk_chk['ok'] ) ) {
						self::limit_stop( $mk_chk );
					}
					set_transient( $mk_seen, 1, 30 );
				}
			} elseif ( preg_match( '#/download/(\d+)/?#', (string) wp_parse_url( $url, PHP_URL_PATH ), $dl_m ) ) {
				/* A Download link (templates). Download Monitor's gate counts it,
				   but when the limit is reached that gate answers with a redirect
				   to the pricing page, which the add-in could only report as a
				   failed insert. Ask first, without counting, and say why. */
				$dl_chk = ds_dl_check_core( get_current_user_id(), self::mkt_item_is_pro( (int) $dl_m[1] ) ? 'pro' : 'free', false );
				if ( empty( $dl_chk['ok'] ) ) {
					self::limit_stop( $dl_chk );
				}
			}
		}
		$dm_real   = ( $host === $site ) ? self::dm_file_url( $url ) : '';
		$protected = ( $dm_real && false !== strpos( $dm_real, '/dlm_uploads/' ) );
		if ( $host === $site && ! $protected ) {
			$cookies = array();
			foreach ( $_COOKIE as $k => $v ) { // phpcs:ignore
				if ( is_string( $v ) ) {
					$cookies[] = new WP_Http_Cookie( array( 'name' => $k, 'value' => $v ) );
				}
			}
			$check = wp_remote_get(
				$url,
				array(
					'timeout'             => 30,
					'redirection'         => 0,
					'cookies'             => $cookies,
					'user-agent'          => $ua,
					'limit_response_size' => 4096,
					'headers'             => array( 'Accept-Encoding' => 'identity' ),
				)
			);
			if ( is_wp_error( $check ) ) {
				$fail( 502, 'Could not reach the download: ' . $check->get_error_message() );
			}
			$ccode = (int) wp_remote_retrieve_response_code( $check );
			if ( $ccode >= 400 ) {
				if ( 404 === $ccode || 410 === $ccode ) {
					$fail( $ccode, 'This file could not be found on DesignStager. Please try another item, or tell us via the Contact page.' );
				}
				$fail( $ccode, 'This download is not available for your account (HTTP ' . $ccode . ').' );
			}
			$loc = (string) wp_remote_retrieve_header( $check, 'location' );
			if ( $ccode >= 300 && $loc ) {
				$final = WP_Http::make_absolute_url( $loc, $url );
				if ( preg_match( '#/(wp-login\.php|login|my-account)(/|\?|$)#i', (string) wp_parse_url( $final, PHP_URL_PATH ) ) ) {
					$fail( 403, 'Please log in to download this item.' );
				}
			} else {
				// Download Monitor sent the file itself: look up the real file URL of the latest version.
				$real = self::dm_file_url( $url );
				if ( $real && false === strpos( $real, '/dlm_uploads/' ) ) {
					$final = $real;
				}
			}
		}

		// 2) Download the real file with cURL. It decodes gzip/brotli correctly and
		//    never cuts the file short, which is what broke .pptx files before.
		$tmp  = wp_tempnam( 'ds-office' );
		$hdrs = array();
		$code = 0;
		$err  = '';
		if ( function_exists( 'curl_init' ) ) {
			$fp = fopen( $tmp, 'wb' ); // phpcs:ignore
			$ch = curl_init( $final ); // phpcs:ignore
			curl_setopt_array( // phpcs:ignore
				$ch,
				array(
					CURLOPT_FILE           => $fp,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_MAXREDIRS      => 5,
					CURLOPT_ENCODING       => '',
					CURLOPT_CONNECTTIMEOUT => 20,
					CURLOPT_TIMEOUT        => 180,
					CURLOPT_USERAGENT      => $ua,
					/* Our own site: send the visitor's cookies, so Download Monitor
					   serves protected files to people allowed to have them. */
					CURLOPT_COOKIE         => ( strtolower( (string) wp_parse_url( $final, PHP_URL_HOST ) ) === strtolower( (string) $site ) ) ? self::cookie_header() : '',
					CURLOPT_HEADERFUNCTION => function ( $c, $line ) use ( &$hdrs ) {
						$pos = strpos( $line, ':' );
						if ( false !== $pos ) {
							$hdrs[ strtolower( trim( substr( $line, 0, $pos ) ) ) ] = trim( substr( $line, $pos + 1 ) );
						}
						return strlen( $line );
					},
				)
			);
			curl_exec( $ch ); // phpcs:ignore
			$code = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE ); // phpcs:ignore
			$err  = curl_error( $ch ); // phpcs:ignore
			curl_close( $ch ); // phpcs:ignore
			fclose( $fp ); // phpcs:ignore
		} else {
			$res = wp_remote_get( $final, array( 'timeout' => 180, 'stream' => true, 'filename' => $tmp, 'user-agent' => $ua ) );
			if ( is_wp_error( $res ) ) {
				$err = $res->get_error_message();
			} else {
				$code = (int) wp_remote_retrieve_response_code( $res );
				foreach ( array( 'content-type', 'content-disposition' ) as $h ) {
					$hdrs[ $h ] = (string) wp_remote_retrieve_header( $res, $h );
				}
			}
		}
		if ( $err || ! $code ) {
			@unlink( $tmp ); // phpcs:ignore
			$fail( 502, 'Could not get the file: ' . ( $err ? $err : 'no response' ) );
		}
		if ( $code >= 400 ) {
			@unlink( $tmp ); // phpcs:ignore
			$fail( $code, 'The file server answered HTTP ' . $code . '.' );
		}
		$type = isset( $hdrs['content-type'] ) ? $hdrs['content-type'] : '';
		$disp = isset( $hdrs['content-disposition'] ) ? $hdrs['content-disposition'] : '';
		if ( ! $disp ) {
			$base = rawurldecode( basename( (string) wp_parse_url( $final, PHP_URL_PATH ) ) );
			if ( $base && false !== strpos( $base, '.' ) ) {
				$disp = 'attachment; filename="' . str_replace( '"', '', $base ) . '"';
			}
		}
		header( 'Content-Type: ' . ( $type ? $type : 'application/octet-stream' ) );
		if ( $disp ) {
			header( 'Content-Disposition: ' . $disp );
		}
		header( 'Content-Length: ' . filesize( $tmp ) );
		readfile( $tmp ); // phpcs:ignore
		@unlink( $tmp ); // phpcs:ignore
		exit;
	}

	/* Refuse an insert because of the plan, in words the add-in can show as
	   they are. X-DS-Limit tells the add-in which case it is: day, month or
	   plan. A reached limit is HTTP 429, an item outside the plan HTTP 403. */
	private static function limit_stop( $chk ) {
		$reason = isset( $chk['reason'] ) ? (string) $chk['reason'] : 'limit_reached';
		nocache_headers();
		header( 'Content-Type: text/plain; charset=utf-8' );
		if ( 'not_logged_in' === $reason ) {
			status_header( 401 );
			echo 'Please log in first.';
			exit;
		}
		if ( 'upgrade_required' === $reason ) {
			status_header( 403 );
			header( 'X-DS-Limit: plan' );
			echo 'This item is part of a paid DesignStager plan and is not included in your current plan.';
			exit;
		}
		$limit = isset( $chk['limit'] ) ? (int) $chk['limit'] : 0;
		$month = isset( $chk['period'] ) && 'month' === $chk['period'];
		$count = $limit > 0 ? $limit . ' ' : '';
		status_header( 429 );
		header( 'X-DS-Limit: ' . ( $month ? 'month' : 'day' ) );
		if ( $month ) {
			echo esc_html( 'Your plan includes ' . $count . 'downloads of this kind per month and you have used all of them this month. This is a plan limit, not an error. You can insert again when the limit resets on the 1st of next month. Inserts in PowerPoint and downloads on designstager.com count towards the same limit.' );
			exit;
		}
		$hours = max( 1, (int) ceil( ( strtotime( 'tomorrow 00:00:00 UTC' ) - time() ) / HOUR_IN_SECONDS ) );
		echo esc_html( 'Your plan includes ' . $count . 'downloads per day and you have used all of them today. This is a plan limit, not an error. You can insert again when the limit resets at midnight UTC (in about ' . $hours . ( 1 === $hours ? ' hour' : ' hours' ) . '). Inserts in PowerPoint and downloads on designstager.com count towards the same limit.' );
		exit;
	}

	/* Whether a marketplace item carries the 'pro' tag. Read straight from the
	   database: the file proxy answers at init priority 0, before Download
	   Monitor has registered its tag taxonomy, so get_the_terms() finds
	   nothing there and every item would look free. */
	private static function mkt_item_is_pro( $post_id ) {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare(
			"SELECT 1 FROM {$wpdb->term_relationships} tr
			  INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
			  INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
			  WHERE tr.object_id = %d AND tt.taxonomy = 'dlm_download_tag' AND t.slug = 'pro' LIMIT 1",
			(int) $post_id
		) );
	}

	/* The published marketplace item (dlm_download id) a file in the uploads
	   folder belongs to, or 0. Download links (/download/123/) return 0: those
	   are counted by Download Monitor's own gate. */
	private static function mkt_item_for_file( $url ) {
		global $wpdb;
		$path = (string) wp_parse_url( (string) $url, PHP_URL_PATH );
		if ( '' === $path || false === strpos( $path, '/wp-content/uploads/' ) ) {
			return 0;
		}
		$base = rawurldecode( basename( $path ) );
		if ( '' === $base || false === strpos( $base, '.' ) ) {
			return 0;
		}
		$key = 'ds_office_mk_' . md5( $path );
		$hit = get_transient( $key );
		if ( false !== $hit ) {
			return (int) $hit;
		}
		$tail = substr( $path, strpos( $path, '/wp-content/uploads/' ) + strlen( '/wp-content/uploads/' ) );
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT p.post_parent AS id, pm.meta_value AS files FROM {$wpdb->postmeta} pm
			  INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			  INNER JOIN {$wpdb->posts} d ON d.ID = p.post_parent
			  WHERE pm.meta_key = '_files' AND pm.meta_value LIKE %s
			    AND p.post_type = 'dlm_download_version'
			    AND d.post_type = 'dlm_download' AND d.post_status = 'publish'
			  ORDER BY p.post_date DESC LIMIT 20",
			'%' . $wpdb->esc_like( $base ) . '%'
		) );
		$id = 0;
		foreach ( (array) $rows as $r ) {
			/* Same folder and file name, not just a name that contains it. */
			$files = str_replace( array( '\\/', '\\' ), '/', (string) $r->files );
			if ( false !== strpos( $files, '/' . $tail ) || false !== strpos( $files, '"' . $tail ) ) {
				$id = (int) $r->id;
				break;
			}
		}
		set_transient( $key, $id, HOUR_IN_SECONDS );
		return $id;
	}

	/*
	 * Small, cached JPEG thumbnail of a marketplace image for the task pane grid.
	 * Only for images that are the picture of a published marketplace item.
	 * Made once (480 px) and then served from uploads/ds-office-thumbs/.
	 * If the server cannot resize it, it falls back to the original.
	 */
	private static function thumb() {
		global $wpdb;
		$id  = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0; // phpcs:ignore
		$bad = function () {
			status_header( 404 );
			header( 'Content-Type: text/plain; charset=utf-8' );
			echo 'Not found.';
			exit;
		};
		if ( $id <= 0 || 0 !== strpos( (string) get_post_mime_type( $id ), 'image/' ) ) {
			$bad();
		}
		$att    = get_post( $id );
		$parent = $att ? get_post( (int) $att->post_parent ) : null;
		$ok     = $parent && 'dlm_download' === $parent->post_type && 'publish' === $parent->post_status;
		if ( ! $ok ) {
			$ok = (bool) $wpdb->get_var( $wpdb->prepare(
				"SELECT 1 FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				  WHERE pm.meta_key = '_thumbnail_id' AND pm.meta_value = %s
				    AND p.post_type = 'dlm_download' AND p.post_status = 'publish' LIMIT 1",
				(string) $id
			) );
		}
		if ( ! $ok ) {
			$bad();
		}

		$up   = wp_upload_dir();
		$dir  = trailingslashit( $up['basedir'] ) . 'ds-office-thumbs';
		$file = $dir . '/' . $id . '-480.jpg';
		$src  = get_attached_file( $id );

		if ( ! file_exists( $file ) || ( $src && file_exists( $src ) && filemtime( $src ) > filemtime( $file ) ) ) {
			if ( ! $src || ! file_exists( $src ) ) {
				wp_safe_redirect( wp_get_attachment_url( $id ), 302 );
				exit;
			}
			if ( function_exists( 'wp_raise_memory_limit' ) ) {
				wp_raise_memory_limit( 'image' );
			}
			wp_mkdir_p( $dir );
			$made = false;
			$ed   = wp_get_image_editor( $src );
			if ( ! is_wp_error( $ed ) ) {
				$ed->resize( 480, 480, false );
				$ed->set_quality( 82 );
				$res  = $ed->save( $file, 'image/jpeg' );
				$made = ! is_wp_error( $res ) && file_exists( $file );
			}
			if ( ! $made ) {
				wp_safe_redirect( wp_get_attachment_url( $id ), 302 );
				exit;
			}
		}
		do_action( 'litespeed_control_set_nocache', 'ds office thumb' );
		while ( ob_get_level() ) {
			ob_end_clean();
		}
		header( 'Content-Type: image/jpeg' );
		header( 'Cache-Control: public, max-age=604800' );
		header( 'Content-Length: ' . filesize( $file ) );
		header( 'X-Content-Type-Options: nosniff' );
		readfile( $file ); // phpcs:ignore
		exit;
	}

	private static function cookie_header() {
		$parts = array();
		foreach ( $_COOKIE as $k => $v ) { // phpcs:ignore
			if ( is_string( $v ) ) {
				$parts[] = $k . '=' . rawurlencode( $v );
			}
		}
		return implode( '; ', $parts );
	}

	/* Real file URL behind a Download Monitor link like /download/123/ (latest version, first mirror). */
	private static function dm_file_url( $url ) {
		if ( ! preg_match( '#/download/(\d+)/?#', (string) wp_parse_url( $url, PHP_URL_PATH ), $m ) ) {
			return '';
		}
		$versions = get_posts(
			array(
				'post_type'      => 'dlm_download_version',
				'post_parent'    => (int) $m[1],
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
				'fields'         => 'ids',
			)
		);
		if ( empty( $versions ) ) {
			return '';
		}
		$files = get_post_meta( $versions[0], '_files', true );
		$list  = is_array( $files ) ? $files : json_decode( (string) $files, true );
		if ( ! is_array( $list ) ) {
			$list = array_filter( array_map( 'trim', explode( "\n", (string) $files ) ) );
		}
		foreach ( $list as $f ) {
			$f = trim( (string) $f );
			if ( 0 === strpos( $f, 'http' ) ) {
				return $f;
			}
			if ( 0 === strpos( $f, '/' ) || 0 === strpos( $f, 'wp-content' ) ) {
				return home_url( '/' . ltrim( str_replace( ABSPATH, '', $f ), '/' ) );
			}
		}
		return '';
	}

	/* Ribbon/store icon drawn on the fly (replace with your logo PNG later). */
	private static function logo_icons() {
		return array(
			16 => 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABpElEQVR4nKWTv08UQRTHP7M73uwdt6tBg3pnBLHCzkILgzGhIBc7'
				. 'KwpCjJWFGP8Bz4pTCmJBISQWVjZGCnKJfwMWJBbEQoVCMaDxLv4A9/aYnWenckfcmHvJK+blzSff930zShsj9BBeL5cPBLTjmL1W'
				. '63d+3tri9vQ0NklQSnUBdGdBKcX92Vnqy8sU+vqoVCo8nJsjCAIe1GpoYzoIxsjfKSIyOTUlgKCUADJTq4lzTo4NDEhn/4Ee5PMF'
				. 'fN/HhBHg8WxpCYBzIyPZIwD4nkJEOB1obpYj3p8pIUqx/fVbNsBaS6PZxDnH04slNlsp49UZNjfWubL7gY0sgNaaavUe5ahI9OYF'
				. 'L4fHOBGE3JiYoP97nK3g0eICR8IIly+yEnvc2XnNk8kxdt9us/rDoXP7bVOdL9EmyZ9DznB98DDXyhGXj4c8ftfg7tqnfwO01ohA'
				. 'zoOzBZ9XX36CS7k62E99dAjzfG0foGuNqbXsWQtpSn10iPkLpxgOAy4dLbC+0+7yoEsBgKegbR3jJ0Pmz5coHvJoJCm3Vj+y0oyz'
				. 'Af8TPf/GX21smeM8Db/pAAAAAElFTkSuQmCC'
			,
			20 => 'iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAACYUlEQVR4nLWUzUtUYRTGf3funbl3xsmPATUIx0YqCSmJwFISQmot'
				. 'ChUiLqRVK3ETEmGL/oGSXEmL2hhtglDapKgEBVkSlDNlVAvFtGYUnXTu3I/Toqice0UoO3BW7zk/3ud5H15F03VhFyuwmzBfYH5z'
				. 'EyuX8/TE+Dhtra3YpokCKIriC1QKJVu5HOl0mluDgwCoqkpNIkF7ezuGYXBzYIDe3l60UAgRH7c0XZc/W0QkmUwKsKWr4nF5Pj0t'
				. 'IiIdnZ0CSOGupuviC5ybmxOjqEj0SETC0ajsKSkWQE40NYnjOPJ4bExQFF+g5ueDiGDbNiKCpgZYz7lAgNlkCtM0qUkk0A0dx/VK'
				. '9gXy03QFMC2X5soo5/eGeZgLg6qRzWYxTQst6F33jY2I4FgWVt6muybG1NmDFJtZTl/oIhwK8nRqkiPGNvEt9MCyLHmbSkmsvFzC'
				. 'kYi8a6uXkaZ9culyn4iIfJpfkI7jh+XBqWpfDz031DSNQ7W1pJeXudbfz2omQ1nEwE0vMTR8n8aWM9x7kaT7VdrfqsIcXunrQwA9'
				. 'GOTl61mizx5xp/kA75fTjC6sMbmaZ3LVIbNhogXVnYG2aW4dCOk0lBmcq47RUhnlaInO/DeTnplFRhfXdwZq2u+Xc0XI5y2wXQgo'
				. 'oAY4Vmow3BinOKgSH0l5gB4PbdvGsW3ylo0qLndPxrnRUPUD6AgzX9Z48nWDCt0rF7bJoQBaAHKWg+UKPXUVqApMfF6nPhaha38p'
				. 'tz+u+AI9kn8dACKgKnC1roKLiRilIZUN22XoQ4brb5bI+2xuC/zb+v8f7L/WdyzUMpZ1Yx+0AAAAAElFTkSuQmCC'
			,
			24 => 'iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAADCklEQVR4nMWVTWxUVRTHf+e+N++1byoDdNLW0spE/KhfiGKIMTZt'
				. 'FBdUXVg1hoW6qFHTjUtYmJAYF25MTIxuTExU0gK6IpKYKAmGxkhVghsIQbDBUtKJg5QyfTPv4x4XBRGZebUG4knO4t3cd373nnP+'
				. '54rr+8oNNHMjgzcFRGFIXKtd45VymS/37uW54WGSeh1rLSKSCZBGKYrCEBHht+lpwoUFxBhcx6G7uxvf9wHYvWcPIyMj1KMIMQbV'
				. 'Jpl2fV//6UmSqKpq/+CgAuoHgfpBoL1r1+q27ds1DENVVR3ftUsB9Vpbr4lx2TMBjw4M/BXA8TzFcRTQF7Zu1SiKrtrTDJBZZBFB'
				. 'RDDGYIzB8zyCtjZ2j49zcGICVeWpoaHMGizZRaq6mF9VUEuYWgAmJycREUqlUub/7lKAxZtAbBWsckd7AE5MeuUEmIxGWloHIiSp'
				. '0tXi8snDvRzfcjubiy3cu3EToJycmsJmSDUbcCk1Any4cQ0v9XXy7vfHqDzyNFsG+kkRjn77DcPdbcsDXM67uSSivoLPk2sK7Dt2'
				. 'mu82DLHz08/wci4f7Rxj5fEfePPBUlNAwxq47uLy+NgY5fMXeOvFZ0njiMiC5DxmKueYnDjIttHXCTThi99/XR5gbm4OEWFloUB7'
				. 'sYjbu44js0d45s4eZn7+mtHBTRw4dYZqPeaCm0NMitNkZDQcFcViEQBrLQaYD2tsaLF88FAP97cH2HrEL2HK/tkqH586x4+VBVy3'
				. 'cTkbApI4vurbcRxShELO0F/M83jXTTzWkWf9qlbO1xNGf5rh8+m5fw/4+4QUgShVSNIrC6p4ruG129p5Z30XZ8KYu7860RCQ2UWo'
				. 'kiSWW4Icb9zVwfOlVeAIed8lRnj/RIWTFyM6/OZ6zVSyCFhV8q7hvQdupppYTi/EHJq9CMDmnhWU8h5T1ah5jKWeTCMQxZYd93Wy'
				. '455OZmsxB8pVXCM80dkGAi8fmmbf2fn/BhBAAWuVV9et5pVbV9O3widV5fAfNd4+Wmb/2fnlddH1tP/n0b+e9icCiGyh7nOTkAAA'
				. 'AABJRU5ErkJggg=='
			,
			32 => 'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAEY0lEQVR4nO2XXWwUVRTHf/fO7M7u7JSy/aYYtC2mFgwGgWCIgTQq'
				. 'QYwawBAejJoQTHwwIZHEF181kqgQI2JC/CCgiNCIQWMiGkKixkZAkJKI9EugtQhs26Xddmdn5vjQuhqxs1uKwQdPch5m7r3n/u69'
				. '557/jDItS7iJpm/m5P8JADOs0R0ZQSmFiOD7PgACIEIQBKRSKU61tdHS0sIHe/YwlE5j2Xa+b3EEliUTeRAEUqydamuT+5cvF0As'
				. '254w5t9dhSVhNpNBa02qv58tW7cS+D5KKQAcx2HunDk0NzfjOE5+zNp169i3d2/xOxFG53meiIicbW+X8d2/xhtmz5aPDxwQERHf'
				. '9yWTyUhjU5Now5BoPF5wB4oC6OzqEqe0VCzblrjjSCyRkFgiIVYikQc5ePBg/jh27d49dhSJREGAom6BiOB53jXuex6xRAKlNc9u'
				. '3MhwJoOI8NDKlZRVVuKOjhaMPeVr6HkeVixGd0cHra2tKKVIJpM0NjYiReTAlAAMpVCAKwCK7q6ufFtlRUVRMa4bQAFZ1yMXCLfE'
				. 'IzimQhl/lhXf91H/BoACEPACYf3scg4tq+P4Aw00lcaov6Mp3+9C768UIzKhlfCfTBhLym0LZrKhPokZt3jnWDvSOJ97Fy4gEKGn'
				. 'p5fezrPErCjejQIwDAMlAaOuz+pZpWyoTzIUQMuP53j+nMcXh7ejlKCV5vWdu3k04VJdN4PNp/umDhAEAcPp9PiTYuWMaSil6By4'
				. 'yosXDQ4d+Zr5d84BoOvcefa98RpfLqrjsgebT4fHDgWQcdGprqpi3/79aKV46dUtmG4nRqSC3qsjODUz85Nf7Otj1apVpC5f4snj'
				. 'PgNZt+DiQgFMc6y5pKSEx9asAeDIsRP88tFJfF9YXFtOU+sZFi5tZsWKFbz79g56OzuwbJtv+9KgwDTC8zwU4OjRo2NyDORcF20Y'
				. 'VJUlabnsssn3iSp4855b+bTzDAe2fEMsq9FWjOyISyRqoJXCl/C7EKqGXi53zbtoxMT1Ye2sUl6eV0NtPIIVHVtH37DLif4Mn/Sk'
				. '2dndz4gvmDq8GoQC/CG9fzURQQOuF1BtR3ikdhr3VSe4a3qcWxMRouMTfnVxmCe+O8+VXHg5DgWYyAwFplKMBoKf80EpquIRFpfF'
				. 'eeb2chaX2Tim5sPzg6z//sKNB/C8AAIBpYhFDQIENxDwAiKm5vNldSwqsxnM+dR/diY01qQqoQJ8EZ5qKKPBiXIp67G9PYVSENMK'
				. 'Kx5hMJPj8G/DLK1IMFSEGEwKQCtFLufzcO00Vs8qxc0FnBwY5UjPIJgmo64HWrGk3MYT4Uq2sBxPWgtQirc6rvBgjcOwF7Dt7lpe'
				. 'saP8lM5SEtE8fluSJRU2Ea14r7u/cLjJ5oBW4OYCNjVV8sLcKiytMRRkvICoVkQNRS6AHR0pnvuhF5nKNZxwEJDzApprHJ6uL2Pe'
				. '9DjTo5qsL/w8lGVX9wDvd/djak2hj4LrAriRdtN/zf4H+B1qQUVDimdPjgAAAABJRU5ErkJggg=='
			,
			40 => 'iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAFI0lEQVR4nO2YXYhVVRTHf3vvc879mLneZux6x5y8o6V9aUL0RWGW'
				. 'YUKWRpQZPURUCj1EESUEFVQG9ZRERQ8hkRRD9aCkURP1oEWZVDZZU9k0I6SOemcczftxztl79XBrUrK6d2bUHlywuXDOunv9zjpr'
				. '/fe6V3mJhPA/Nn2yAf7LTgGO1U4BjtW8eh3DchmlFADOOUSObn5rLcVikW+6u1m3fj2dnZ0M7t9PIp3GOgcyOrFQ9crMkYD1WG9v'
				. 'L08+/TSvrVlDkEwi8LeHOm6A3d3dDA0NoY1BRFBKMSGToVAokM1mj/re86tX8+ADDxCkUjjnGgbESySknmWtFWutiIjMnTdPAEGp'
				. '2ieI9jyZ0t4u9y5fLv39/SIiUqlURETk4ZUrBZBEOl1XrCPXqAAXLFwoWmtJNTdLkEpJkErVNjNGAMm3tcmWLVtERCQMQ6lWqzLj'
				. 'nHNEaT3iW+8aVReLCM65oxaA7/ukMxkG9uzhlqVLa2WgNUEQsGLFCsQ5tG4s5LjKjIgQhiGpTIadfX28umYN5o86XbhgAcb3ieP4'
				. '5AGOgFqLVoquri4AlFIUCgVOz+WIo+jkARoFRiliJzgRBgYGRu6lUikmZDLQYCfXLdT/ZVpBNaoFb076aN/g+f7Ifecc1tmG9x0X'
				. 'QAWEkePayRnuKJzG/MlZXtnWy9eFaSM+w8PDDA4OoT1zYgEVEFnHE7PzrDwvh0FhfI/OvmFWPXs7UGuenp4eDhSLmGQKpP7XPKoa'
				. 'VEqhtcbXmihy3FZo4bEL8hwMHb8JXL9uK4WFi1l202KiKEIpxStvdHJLPsncSc0NxRpVBsMwxDlHqVJBGc0901o4HFsSnuHWj3/k'
				. '8KXX0fXGWqI4xvd9evv72fDm62yffzabBqtsHjh4fAHb29vp6OhAPI/fBnYzJeXha8PWvQf4LtHKrx+8N+JrreX2O+9ijhfyc0XY'
				. 'XQ4bC1bvkRNFkcRxLHEcS7lcllK5JL/s3CmFfE6+XXSeHFp2kXQvOl9Ob22RDV0fiojI8NCQLF6yRABJNjUJxhf8oKGjru5pJqpU'
				. 'jnn9wosvYVVygGum5rBOeKuvyKPde5l55dX09v3C7p9+INXUhLW1E0SkNl3UncB6HTds3PjXPCiCdQ7PGHKTz+CFz7/lhult7CuH'
				. 'LO2YyFVtE1i34ws+KUVsa51A33AZgMBvvCfrzmBcrR7zuvE8LJq7p7fw5Ow8rYFBofA9Q9U69pRCtgyWWds3xLu7DuJpXdOm8QY0'
				. '5tgC65xDiRBGjqmZgJvbsyzIN3N+NkEu4WGUwoqQ0IqXfx7koS93Ibp+wroB/3UTwGhFxTqIHHiajiafy1rTNeC2ZirWMSnh8fj2'
				. 'vTzXs+/EAQpgnYyQJrXGihA5ASsgwn3n5nhmdp7QCYOhZdb7P9W9/5inGU8p2tM+U5t8Jid9rAgC+FqRDAx+YHhpR5GeQ1UCrcj6'
				. 'J+gsVkDkhNak4YN508j6mooV5n7Uy65yRNrT/JlYrRQKBQriBn/ZjTqDf2apWInZW4nJeIZ80uORc3NgHaVqTCWMiaox90xvYWYm'
				. 'QAHbDhxbT//JxlSDRimqkWXxmVneuWIqg6El0IrN+0ts2ncYK8JFLSmua2vGVwoBbtzcz2fF0okBhNqgGkaOFTMm8tSsPCmj8bXi'
				. 'SCUJnXAwctz/1S7e7juA14Bgj4vMaAVhaJkzMc3ys1q5vDXNxERNsIthzKf7S7y4o8j3QxWCwOAaqMNxATye9r//d+sU4FjtFOBY'
				. '7Xf4ZqQjtTy1UQAAAABJRU5ErkJggg=='
			,
			48 => 'iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAGmklEQVR4nO2ZbYxUVxnHf+fl3jszO7MLwzq7C4Vms1gCdSmRD5BW'
				. 'ihA/WNY2qVHqqvFDNaWm0ajEluILTcXE4gdTivEtgpJoTEGD/VBCbCyhqcGUuIalyFJbtshmd1t2Fne3szP33nOOH2Z2hSqUmZ2y'
				. 'kPBPTiaTM/ee87vPPP/nOTNCB4HjBpac7Q3MVDcBZls3AWZbNzyArvaCUqGAlO/NHYYhw8PD9Pb2cvDQIQ4cOMC5s2cRWuP7PsaY'
				. 'mjb8bolq68C7AYwxOOdACLjoVQiBUmr6c8PDw+zes4enduzg36OjBKlUXSBmDHA1CsMQ3/cBONXXR3d3N3/v6akLRE0AQgiEEOTz'
				. 'eR57/HHCMLwESmtNSy7HihUrWL9uHdlsFmstsTH4nkc+n+ej69fTe/w4fjKJnQmEDgJXzTDGOGutc865/v5+B1xxtM2f757ascNN'
				. 'KQxD55xzp/r6XKapyWnfd14iUdUeLh4zciEpJU3ZLH4ySTKdJtHQcMkIUikGh4d57NFH+dTGjURRhFKKMAxZctttbN68mTgML8mV'
				. 'qvcwEwCAOI4vO4wxeJ5HKpPh9/v2sWXrVqSUSClxzvHwpk00ZbOEpRJCiNkBeC8554iiCD+RYOfTT/OPU6fQWmOMoSWX4+41a7Bx'
				. 'XLUxTOmaFDLnHFJr4ihi3/79QDlyzjlWr1oFcP1GYFrWIoSgp6cHYNrJ2tvbZ3TbqitxNRKAEgKHK1uSc4xeuAAw/ZXJZDJQmatF'
				. '7xuAEoLQWqLIgBBoX6KEmC5oUxuOomhG67wvAFIISpFhbkLzyfYsH2lOsXxemk0vvkpu4a0AWGsBGBoaQorac6DuAFIIwsjQtaCR'
				. 'H65oo73BQwnBqIHeccPWT3QB/93wiRMnsDM4ldc1iVVl8/csaOTZOxfRmtDkSwalNQ8dOc38zju4956PY62d7kgPHzlCe9qfjsg1'
				. 'B5hyEyEEsXU0BYodd7QSWkfJWBKe4sGXXuO5vGH/nl+iPY84jhFC8MKfX+Rkby+/vfuDdDb6swNgrcVai3AWExvW5tIsTgeMx5Y5'
				. 'gccjfz3L/kKSl/50iA+vXIkxZtqBvvndJ7i3Nc3q1gwPLGyaHQClFEopdKWfWdYYIICEFJybjNnfP8IffrOXu9asoVgsYq1Fa82T'
				. '27/PyaMv8+XOhbw2MkEuqC0da7pqKgHb2troOXYMYy1KSroe6MaOv155wuWDjgUmCwUAEokEADufeYZt3/k2qVSSz/yln2gGWVw1'
				. 'QHxR3yKEoKOjY3qutW0+bw70oqSgaB0LkpqHlrRw/+e+wJZvfI37uzawd++v2bXrx/jJBKF1TFqLoPxAZA1OWvWBJioWLzv3k917'
				. 'eHLTg/R9eiWFMEYISCrJT0+/xc7jAwwYATjwfJQUeEJgcUwV4VriUHUEvrd9+/8UHVc5A4+cP8+Q9fjRqbfYdnuOocmY8cjw1aUt'
				. 'fGlJK38bmeDlkSIvDI5xbLRAsRSDlvhKYmtsJaqOQFwqXXHeCwKMdXxrWY6vL2mmQUkmYkvkHAklCKSkZC19YyWeGxhj95lRBgsR'
				. 'gacwNUBUDaD15YPmnMMYgwCiyNKZTdK9aA4fa2lgcSYgqSShtUzGDk8KGj3J2ULEF185x+HBCbRXvSlWDXC1UkJQig0YRyJQLG9K'
				. 'cFdzA+tyDayalyKlJPnQMNdXjMWGriP9vDp25ej+P9UdQFD+aQhAVtwlcg5jLBgHSnB7U4InPtTChrYMI6Fhnq94fnCc7qP/qn69'
				. 'egPE1jFtKwiUEkjKUJKy64SRRUnBwbXtrJ6XomAsRWNZ/PzpqterWzcqAOvglpRH1lcYV7bFNyZCYle2SlsxypSvKRQj/jgwxrpc'
				. 'A+OxI6VqawrqBqCEIApjHulsYfOSZs6XYlJKsvbwGXrzBQJPlaMDTJlwUktc5X1YYzWu75lYwMmxEs5B5CBQgoc7stjYMhmVrTRy'
				. 'jndKEemEZuPCJiZiS1JJTtaQwFDHCBjnkFpycHCMNwsfoDnQnC8ZPr9oDpF1/OKfIwyHBi0ESzM+W5blWJoJuBAZ5gWKn72Rr2nd'
				. 'uiaxqhwl77ulid/duZDQOiYiy1xfUTCW0QpAc6AxzjFpLG0pn12nz/OVYwNofR3Ugakj5YYFjfxgeSsdaZ+SddhKu4EDhyOQkqK1'
				. '/Pz1PNtODCMQuGvRzF2NlBCUQkM2qfnsrXPY0JahI+2T1hLj4O1izNF8gV+dGeWVt99Be4pKm3d9AFxL3fD/kd0EmG3dBJht3fAA'
				. '/wFDnhurd19pmQAAAABJRU5ErkJggg=='
			,
			64 => 'iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAJ0ElEQVR4nO2ae4wV1R3HP+fMmTt379297C7LsrvKS9SCVC2KWsQX'
				. 'UAViU7W2FTQxVuyDNE2MWm3S9I/2n5qmxqa2mvpoNE1RmjY+2lQRBURr8QVYFKvlobAuyGvZZe/unZnz6B+z9yJi8XGHvdvKN5lk'
				. 'sjtz5nc+c87vNVeoIHB8hiVrbUCtdRRArQ2otY4CqLUBtdZRALU2oNb6zANQ1Q4Q9vcj5Sfj6Jyjr6+Prq4u1q9fz8pnnuHJZcv4'
				. '95tvAhDkcjjnsNZWa95HSlSbCX4aAB+mnt5eli1bxm/uvJOVy5eDlATZLMaYqsc+nI4IAGMMzh1+WCHEQefvH+OhJUu48aab6Ors'
				. 'JJvPo7WuxsTD21HrFWCtxTmHlBJrbQXGts5O5i9YwPPPPUeQz2OOEIRUATjnEELw6GOPsWnjRjylsB9YCZ6UjCgUGDt2LJMnT6at'
				. 'rQ2gMnkhBFprlFIU+/uZN28ez65aRZDLHZHtkCoAYwye5zH7wgtZ/tRTH3lvc0sL58yYwaJFi5g7Zw6QgJBSVsbavXs30848k23b'
				. 'tqF8P3XHeETCYGNjI0op8oUC2Xz+Q49MXR3d+/bx2KOPMm/uXBZceSXd3d2VreB5HlprWlpauO/ee3GDWyVtHREAxhi01oc9rLUo'
				. 'pQhyOYJcjocefJBZs2ezc9cuhBCV/2utmT1rFl+9/HLiUgmlqo7cB6mmiZBzDmMMxhhyDQ2sW7uW+QsWHLTMhRA457jxhhuQnpe6'
				. 'Hxg2mWAURdTV17Pi6ae5+557kFKitcbzPADOOvNMPn/KKcSlUip5R1nDBgAkW0d6HrfdfjulMMTzvMoqkVIye9YsgP9fANZaVCbD'
				. 'prfe4qUXX6z4grLOmDYtOUnRGQ4rAHDg7b708svAgdwCYML48SAEJsVQOOwAlLV169bKeRlAY1MTKpM5CEq1GhYApAAlBEoI5OC8'
				. 'iv39h1zn+37FL6SldIPqJ5QnBMY5Im3BOnCASwjk8/lDro/jGGNMam8fagRACMBBGGmkkkxtznFqY5ZJDQFbQ8tdr3UybsyYyvXl'
				. 'N969dy86ishks6mlxEMOQAqIrcNZuHJCM9+e2MzJjVkCKchnFT97bScOmH7WWcCBRAhgyzvvIN5XOaahIQUgBWjjGOFLfjvtWC47'
				. 'tkDJOIraEhrLfuO4840djD3hRE6fNq1SJpezv7Vr1uBIdktaGjIAgiR8B1KwePpYLhpdz46SRopk67dnfX64YTedxZC7brgeP5Op'
				. 'lMVlx7f06eUUfA/lQY9OJxIMWRSQQhDHhh9MbmVuWwPbSxolBZ4QjAoUv9rSw8/XvsO5553HdQuvq1SE5bf/6to1rF+3jmtOHM1P'
				. 'TxqFidLZAkMCQACRsbTXB1w7oZE9kcaXAgEoAd9ds4Prn9/IpMmTePDBxSjfT+4b3P9CCG79xW1gDV85tpEFYxsZ15hNxbYjAsDz'
				. 'PJRSlcNXCmcsZ7fkGJ31iazDOscI3+Put3v43b+6mDdnDiuWr+CYjmMqTRFtDEopXnxhNUuW/JHzj2nm9BEZlBRc3N6Qiq1HBMC+'
				. 'ffvQWlPs7aVULNJfLIKDkwpBJdGRQhA6WLzxPdrHjefhRx6hra2tUvgYY1CeR19fH9dcu5DmjOSXp3WQ8yQ5TzKjJZeKrak6wbJT'
				. 'um7hQs45+2w8pUAItmzayP0P/J7mQCUej4R8yTp29g0w7uRxBNksURRVmiBKKeI45htXXMEbGzbQ3tjAzeu6GDAOXwi643T6AqkC'
				. 'KBcyC+bPP+jv69au4YH7HyC0JA4BMA4KEj43qpHVa9awauUKzrtgZmWc3Xv2cOVVV7Fs6VKy+Tzb+0O295Yq9yNAedUv4KpHcM4d'
				. 'cmhjiOOYMAyJ45i6bBYnFZv3l3h/4DIOfjylDS8qcf6X5rBw4UKee3YVjz/+N6ZPn16ZvNYaXwiCjCTwkyOj0tm9VXeFo4GBj4zH'
				. 'RmvGTZ5CdsfbvHzxFEJtESIBMMKXrN47wI9e3c7q93oOui+Xz6ONxrnKzkldVWOM4/iwzc9SGIIQXP31y9nUF/HY9iItgUdoHUpA'
				. 'T2w5o6mOJy44jicvOonvT+ng1NYRBHV19PeHRLHFklSL6ZVAB1T1CpgwYcLhL3AOhCCOIjo7O2nKKP567nimNmXZFWrK05IC6pXE'
				. 'l4Le2PLW/pBndhZ5Ysd+XtjTTxgZ8CWBlJgUy+GqAegw/JhPEmSCgEhb2uoUvz6tgy93FDAuqQVi5yqdLl8Ksp4g60lC49jQW+JP'
				. '23p44O1udg1oAt9LDULVAD5ug7LsIKWAyCSb+tJjC1w9vpEvjswzMpNMasA4wsFECZKeQZ0S5DzJlr6I69du5/F3ewky6UCoGsCn'
				. 'euhgPyCOLUg4oSHLrNF5ZrXWM7UpS0edjy8ERWMpGYsgaZzklSQjBfP/sY2/bNuH8r3qbanlL0U9IXC4ZEUYC0Iwqs7ntKYsF41u'
				. 'YG57AxPrM/TEplIGZz1Bb2yYvXIL7/THVdtQUwBlSQESgcURWZfER+doyiq+c/xIbpk0KmmiANpCU0Zy9+a93PTqjqqfPeQNkQ8G'
				. 'M+sc1oEdjPS+EEhfIoBe7bj1nzvYUdLcMbWDPm3xJAwYx8zW+nRsSmWUj6lIW8JIE0Zm8NDEH3BkDjDOoQcdZpBV3L95Ly/s7ade'
				. 'SZwD7RytQTrvbkhWgACsdZzRkmdMXVIOC0BJWL1ngJ2lGCnEIdmeG7xbWseb+0POacmTbIQD7fNqNSQApBDExrBoYjPfnNhMX6hx'
				. 'QEOguO6FTu57azd+oND/JaxZISgob3Dygw2WlBqDQ9YRwsHrPSVKsaE7tvTGlt5Qc9kxBZAC61zlw4gg8Re+FITaMrLOZ3pLjqJO'
				. '3n5GCjb1RanYNiQALA6k4Jld/UQ2qed9KShqy8zWPDdPaSWKDKXYEBlHbB2RdgyUNIEU3DG1nfasIhqMBBkpeLirNxXbhiwMCgFa'
				. 'W5bMGMclHQV2hUlf0JHUAH/u7OEPb+9jczEitEm77Asjsnzr+GamNdXRGydxIucJtvbHzFyxmR5dfWN0yACUvwmMzfs8dcFxjA4U'
				. 'PbFBSYEbLIst0BMbtIU6T1DwPSJr6dMOh8OXggYlufTvW1na2Yvyq1/AQ5oISSGIYsMpTXUsnj6WSQ0ZumNDbJNcoPyRVIgk64sH'
				. 'HZ2SgoKfFEbfe6WLxVu6/3drAU8IQm0YFShumdzK18YUGBUozOCEjUv2eRlGRgpC63h2V5GfvL6TNbuLBBk1fKrBTyMpBJG1EFsm'
				. 'Nma5pKPAeaPyTKzPMCLj4QkoGcf2kuaVvQM8+m4Py9/rA0eqpTAMk1qglhoWP5CopY4CqLUBtdZRALU2oNY6CqDWBtRan3kA/wEC'
				. 'L65Zc7vsSAAAAABJRU5ErkJggg=='
			,
			80 => 'iVBORw0KGgoAAAANSUhEUgAAAFAAAABQCAYAAACOEfKtAAAMl0lEQVR4nO2ae4xc1X3HP+ecO3N3Ztb7XuO1jdcPoKbGyyMJKXLs'
				. 'EDDFIYlpmhbJdmmp7UBwpSr4n7ZShdSEPxqJ0uQPGilIccIj9Usl4PwRbDcVXhcwOATF3Q1NE2N7zbK2N/uY3dmZO/eec/rHnZl9'
				. 'QhzP3t2xMl/paqV5nDnns7/f+T3OEY7rWqq6Ysn5nsDVrirAMlUFWKaqAMtUFWCZqgIsU1WAZaoKsExVAZapKsAy5cz2gLlMBqVU'
				. 'WWP4vs/Q0BA9PT38T1cXr732Gsc6O/lFVxcAwnFwXRetNdbObyUqZrsWng2AMymTyfDmm2/y73v3cvDgQQYHBnBcFyklxphZ/73L'
				. 'VcUCtNaWHiEEUo7vNud6evjXp57i6W9/G9/zqEmlCIKg7N+8Es0ZQGstWuvfPiEhSn8nQrPWliytOP6JEyf4yq5dvPP22/MGsWIt'
				. 'sKggCJBSToOptcZxHDKZDNt37GD/vn3zAnHWg8hUFV3w7NmzdHZ2IpX60I1fSkkikaC1pYVly5Zx7bXX4jjhFLXWSCkRQiCEwHEc'
				. 'tNakUin27d1LIpnk+3v2zDnEyC0wCAIcx+H5H/yAB7dtu+xxauvruf6667hn40a2bd1KR0cHEIKcOH7RrYUQfPa++3jlxz/GTSYv'
				. 'a7uYDc0ZwAMHD7J1y5aPXpy1WEKrDYIA4/sAxFyXrVu28MQTT7B0yZIZIQoh6O/v55bbbuNCXx8qFpuT6DxniXQRykc+WqO1xhiD'
				. 'Uop4IkFNKoUFvv+97/GJ22/n8JEjKKUm/ROklGitaW1t5Vvf/CZ6Dl24YiuRYtQt7mc1tbX09fVx3+c+x8uHDk2DWNwT/+xLX+JT'
				. 'GzaQz+UiyUenqmIBTlXg+7g1NQBs3bqVru5ulFKT3LQYnHY/9hjMUYVy1QCEMIDE4nEyo6M8/Mgj0/bSosVtuvdelq1YgZfLTUp/'
				. 'otBVBRDCoOSmUrx2/Dg/fOklpJQlNxdCoLUmkUhwz8aNYEwV4Iwq5JbfeeYZgGlJtgU+vWHDnEzlqgRojEEoxRsnTnDhwgWklKX9'
				. 'T0qJAG5aswYxJdBEoasSoLUWJxYjPThId3c3MDmhBmhbvJi6+nqCICi9FoWuSoAw7rZnz50DmFYe1i1YQH1dHbZqgR+toeHhGV+P'
				. 'x+O4rlvaL6NS5M2E2ZAApBAUMYRhovBehHAuRxUNUAoQCDxjIDBgbEhTCNyC7zQ2NMz4Xc/zyOZyIESkbf+KBFi0OC/QYCwtyTi3'
				. 'NtZwU10N7akYCSV5vPsSF4Dl7e3hd6ZY4nA6zfDwMCLicq7iAAoRVmFePuDm5iRfXtnEPYtqWZKI4QiBqwSnhj0Gx3poam1lzZo1'
				. 'wHhQKTYient7GU2nceLx3x8LlAICY1HA129uY9d1zaQcSSYwjPgGzxgW1cR4tidN3g/4zPr1NDc3T2pvFWF1dXVhjSGmFPkIuzMV'
				. 'A1AAxkBCCp77o2Xcv6SOS17Ab/IBSggs0BBTnM7kee69AQB27Nw5fZyCKx/r7ARgTJtIF1kxaYwAtDE8/fEl3L+kjt5s2Ex1hMBY'
				. 'S0yCEvDVn1/kwvAon7nrLjZt2lRyWQitTylFNpvlyNGjSCnZ3FZLlH2ZigCohCDva7ataGTbsgY+yPrEZZi2aAspRyIR7PhpH4fP'
				. '9FNXV8e/Pf00UxOYYtl2+PAr9Jw5Q1t9Ld/9xBLuXpgi8KNJqOcdoAACa0nEFX97fQuZwKAKbmgs1DqSX6Q97j52lv2/6iMWkzz/'
				. 'wgusXr0aPaXbUnTfJ598EoDNixfQXOOw67pmpIpmqfMOUArQgeHjTUlWL3DJaosUYIGYhH4v4IE3zvPOhUHa2hbx8qEf8YXPf37a'
				. 'uUgQBCilePHFFzl+/L9JJZNsb69nOBdwe1OCjsZENPOPZNQZVDyKdJQK/xYeKSQYy22NCVwlMIUdS1tLXUzxQk+ac0MZbvrDG3n9'
				. '9TfYdO+9Mx4qKaUYGBhg9+7dICQPr2ri1oYaMtrQGFesb0lGsq45i8JePh8eHI2MTJ6A44CAFakYE3d7gSAwlp8OeQhr+Mqjj9Le'
				. '3o7neWGNW9DELsxDDz3EmTNnWHtNI7tWNvB+NsC3lhHf0J6KR7KuyAEWLWX9unU8++yzKMcpHRhJKfnGN/6ZU6e6WBB3sFAKDALQ'
				. 'hGkIQHNzM8Ck+zITLXH7zp0cOnQIN5nkg4zH3a+ewTe2WPnh6WhiceQAixt7e3s7Dz744LT3D+zfz6mfnyI/ZX0GiysFK5IOFsGe'
				. 'PXu4/0/+lEQiPFgqwhvLZtm+fTv79u4t3UroDyx4UycSpkSzrXk7F/Y8jyAIaClY1vmxoBQ8wokJxgLDX7fXU5us4fCRo9z56fUc'
				. 'OHCAdDqNUoq3Tp5k/YYNk+ABxIQgJqc8EXVtZt0Ciy42kyZu/MWkd+3atQC8PZQruRyE0XlMG9bW17DvjmvZ+VYvb751kgceeICV'
				. 'q1bRsfYmXjlylGwmM+0+zFxeuZz1qx1+Lvc7fb67u5uOW24m4Th03r2K5ckYuUIqA2E0ro8pzo75fOf0IC/1jvDeQDp8U8WIuzGE'
				. 'CZNkY+cWHkQAcLC//7JuBFjAGoOQkvs3b+bo0aP85R+08d2PtdGXC3DkeANVW0uNkqQcSV824PWBLD+5mOH4xVH+d8RD+xqkQDmS'
				. 'mAhTITNHJGcdYFtb27QS68NUjLpj2SzpoSHy2vDUrYt57IYWPsj5gECJ8c9qa4lLQcqRKARDvubdtMerlzIc7hvh5GCWnKdBSVxH'
				. 'YqyN3CJnHWCQz//uX5KSWOEeYKANX+9YxGM3tGCAUd9gsKhCS99CCYwjBAklcZUgpw3vpj1+1DvCgZ5h3h3KgpLEVQgyKs06wCs9'
				. 'o7B2PID4vuaexXX83Y2t3N6UJC4FWW3IaYvBIhEIUWiBWUqvJZwQ6FBec6BnmK91XeRi1seNKXREEGcd4GxICYHna4QUrG9N8cWl'
				. 'ddy1sJYVqTiuEuS1JWcMfmGjKx44FWE6QtAYV/xyxOMv3ujhnYFsZBArEiCEEI21+IEBa1ngOnysKcGdrSnWtaS4sc6lxXWwWEYD'
				. 'Q97YSYmyb8Lo/Zu85o9fPc3/pfMoNfu5YMUCLCpcs8A3FqMLJ3OOZEUqzh3NSTa11XLnwloWug7DvsbasHSDEGKzq+i8NMYXjp1B'
				. 'R5BLVzzAosKTurDJoK0N3VcbEIL22jg7VjbxN9c3g4W8taUSK7DQEJM8fPJ99vXMfAhfjua9H3i5CtOYsPlqgbgSuHEHt5BkP/6z'
				. 'Xra8fq7gyhNLwvB7f760PpJ5VQxAKcJ9b+ojP8TtjA3zQm0tcSVIJmIc7hnmH071kXJUKXWRAvLGsKa+Jpp5RzLqFSgfGLx8gJfX'
				. 'E56A/GW0oYwN9zsVd/iP82nOZvLUKFmyQm2hLhbNUuf9WDNMPyx3tKZY6Dr4hXzQAnEp6B72+PWIh5TiI6sKSxg8RgJNb9ZnaSKG'
				. 'Zy0RNWFKmn+AAnRg+fvVrWxeWsdI3qBE6J4LXIdvvdvPV986T8x1CH5LHjexOjFTcEdVG887QEl4l+NXo3myvmHYD1AivHHqm4A7'
				. 'mhPE42ESXLTMmaQE+IGlvc5lVW281NEpWvKvR6+gxLys+VeIfjaURQqBFGEDwZHhrayOhgRfXtWE74UdmmJgEYynNuHhO5hAs/uG'
				. 'ZupislR1GAuuFBy7lIlk3vNugdpahJIcvZChL+eTciTajt/QGvE1X1tzDZe8gP2nB0Ofn1hRWAvGIqXk8VsW81fLGxnKj58tKwGZ'
				. 'wPD82aFI5l8RibQSAi8f8I8di/inNdfQW7iZAKELKgGulLx4fpgDPcP8cjRPOtAoIWiJK25rSLBteQPrWpIM+2a8KWEsi2ocnnlv'
				. 'kEdP9ODEZv+qW0UALC7YAV7esJwNLSkuesEkiFioi0ushbRvGNNhsKl1JLWOwjeWkQJUGK+Fz2V97vrJafq9APFhSWU5c68EgFC4'
				. '2qYtC12Hg59q55NNCS554TlHEUq4rwkcEbo3WHQhoRaEe2MxwW52Fe9nA754/CynBrPEYyqSvmDFAIRC1RBYGuKSf7llMVvaw/Jr'
				. '1DcE47d7p8kWzkKkgKSSJJTgPy+OsutkL6dHvN+vfmDxkqXRlo1tC3hkVRPrWpLUx1RYcRTKtyIPKQQxGUbivLF0Ded45vQAz50Z'
				. 'CiOwIyODBxUIEMYtLZ83IKCjMcHGRbV8sinJyto4zXFFXAoMYYR9P+vzzlCOo32j/NfFUby8xomrkktHOtdKBHg1qWIS6atVVYBl'
				. 'qgqwTFUBlqkqwDJVBVimqgDLVBVgmaoCLFNVgGWqCrBM/T8ic+MX0YagFQAAAABJRU5ErkJggg=='
			,
			96 => 'iVBORw0KGgoAAAANSUhEUgAAAGAAAABgCAYAAADimHc4AAAPwElEQVR4nO2ceZAc1X3HP++9PubY2V3tgXaXBYlDGJAwoEJCAowC'
				. 'BlTBMYGAHRIbO8Wp2OCCJFRctilXOSFxGScm5vgjCIeCmArB2CmBEwdSFhKW4g0IIYQ4BEhILIuk1R7ac6aP9/JHT89eIw5ZO70r'
				. '5lvVVdJsd0/3+/T7/d7v6BGW6xqqSkwy6Qv4pKsKIGFVASSsKoCEVQWQsKoAElYVQMKqAkhYVQAJqwogYVUBJKwqgIRlTfcXjA4N'
				. 'YVmH92tGRkbo7u5m165dvLJtGx0dHfy2o4Ptr79e2sfNZDDGoLU+rN99uCWmOxs6HQDKaWhoiBc3b2bNmjX8/Be/YOfbbwMRCK01'
				. 'xszMpO+sBWCMKW1CCKQcs6bDw8M8/vjj/Ojuu3l5yxakbWPbNmEYHvbr+F01awFMVmxujDGl7yt4Hvfffz/f+9736O/rI5XNEgTB'
				. 'tF/Lx1HiAA7FRgshEEIc9O/GGEKtsZQC4I3t27nhxht5bt26GQchcQCHKmMMYRiWzE85IPE+lmURBAE3rVrFTx58kFQmQzBDzNH0'
				. '24YPUBiGvPbaa/i+/4FPNABC4DoONTU1NDQ0kM1mJ4ANw3AKCCEElmURao1SigdXryaXy/FPd989Y2ZCIjMgdpyDg4OccNJJdO/Z'
				. 'g7CsD12pWJZFynVpaGhg/vz5LF2yhJUrV7Li/POxbRsgGmw5NbyJfYRSiq989as88vDDuNksYcIQEgdw8sKFvN/Vhe04HwogHkQd'
				. 'BDBu34Wnncaqm27ihuuvx3VdwjBEFe3/5OONMfhBwLnnncemF17ATacTXR0lHgmPX05+2AaglMJ2XdxMhlQ2i5NOs23rVm65+WbO'
				. 'Xr6cdevXo5QqO6hCCIwxuI7Dww89RCaTQRf9SFJKHMDHVQwjDEOCIEBrjZNOk8pm2bJ5MxdeeCH33ncfSqmyNj7+/NRTT+X222/H'
				. 'LxTKzpZKadYBKCetNUEQ4GYyKNvmlptv5gc//GFp9TNZSim01vzFbbfR1t6Ol88nNguOCACxYrPjZjL89e2387MnnohWQZPMkRAC'
				. 'rTW1tbXceMMN6IP4jEroiAIAYyZK2TY3rVpFV1cXUsopAZ+UEmMMX7nmGtLZ7EdbCk+DjjgAEJkk23Ho3b+fO7773ZLzHa8YwHHH'
				. 'Hcfyc84h9LwJ+aRK6YgEABAGAZbr8uijj/L2jh0luz9ece5o5cUXA1RnwOGUIQrc8iMjPPbYY8DUvFOcU1q+bBkU/UKldcQCAEpm'
				. '57+ffhpgiomJ/79gwQJq6+sT8QNHNACtNSjFa6+/Tl9/f8nuT1ZTUxOtLS2YIKgCOJyKawM9PT10dnaWPosVO2fLsmhqaip9Vkkd'
				. '0QAgMjPa9+np6QGYMgNiu19bV1fxa4NPAID4iR4ZGfnA/RzHqcTlTFGi9YDDLQEIAQKBADRjSbwPKwqZhLonZj0AASgh0Bg8bSA0'
				. 'oInS1Upi2waEoO4gJiaeIcPDw5W76HGatQAEIIWgEGr8IAQlaErZHJuxaUlbtKQsNvXl2do7TLamhpaWlui4SU42XooezEdMt2Yl'
				. 'ACUEntYYL+TonMtlbTkuaclxap1Ls2thCUHOVXxxw7ts2XeAece009baCkwEUCoMDQ2xr7sbyqQspluzDoASgoIf0piyuG3RXL40'
				. 'r562tE2gDfnQUNAGH8N7/T7rekYQQrDsnHOwbZsgDEudEjAG4L3OTrr37UN9hKrc4dasAqCEoOAFXNCa497FbZyUc+n3Q/YXAgQC'
				. 'KSDUhsaUxaO7+9k35IExXHXlVUBktsZLa42Ukq2vvIJfKJAuFuoriWDWAIgH/wvz57B6STvGGPbmAywpsIpmRRtIKcHefMCP3+pD'
				. 'hh6nLFrEZy+8EGPMQbOd69avB6CgDdqY0vkqoVkRB8RmZ0VrjtVL2vG0ZiQ02FKUnmptwGCotRV/teV9dg55aGP41re/jeM4aK0n'
				. '2n+iypjnefzPM88AsDDnMD/joLVBVojBjAcggEBrGlIW9y5ui7oaNKhxAxQasCU0uxbffHkPj3UOgZfnc5//PH969dVluyR0sUr2'
				. 'mw0b2P7GG6Acrptfzz2L29Bh5YzQjAeghCAMNF9f0MjJOZehQE8YfG0gowQjoeHa/+vkH9/sg8Iox594Aj9ZvbrkaMtJCMGP77kH'
				. 'YwzZtM3yxjSfa82xojWH51UmMJvRAATgaU1d2ubqY+oYDDTWONsQ2/x3R30uWruDR3YNQGGUY+fN45dP/ZKjjjqqrO2PZ8TzL7zA'
				. 'U2vWgLK5pDnDwroUI6HmxuPnTPXY06QZDUAKgQ4NZ81JMy/rUAjNhHGJ0g6walMX2wc88PN8ZsUK1q9fz8mf+lSpXXGy4qXmd+64'
				. 'Ax2GWLbF105sQBsYCTTLGjO011QmN5Q4AKVU2U1KGQ22NpxRn8KRUbohVmggZ0me7R5mw/5RhPb58jXX8Oyvf828Y489aHec7/tY'
				. 'lsUDq1fz9K9+hVEOV7TVsLwxQ78f4mnDHEdxZn26Ivef6DLUGMOBAwcIfZ/Q96f8PZVKgYATcy7aTLQKhmgV1NE7igh8MtkMd955'
				. 'J1JKgiAom3wLggDbtnlx82ZuvfVWhOVwSn2K+xa3kZKCJldhjCBjSxY3pPivPYPTePeREgVgWRaXXHIJvfv3Y9n22BsvUtLb28vW'
				. 'LS+BlNRbEl0mQjUGerwQo0Oam5ppbGgoNeBOVgxl5zvvcPkVVzCazyMtm7QSfHPrnqJzFxgDthS8MViowAgkBCBelWQyGX5WLJhP'
				. '1saNGzn33HPBdiPDU8YpCgFZJRFS0dvbQ19fH9lsFs/zsCyrVIKMn/ztb77JpZdeyru7duFmMoRhyKaeUTbtG556fimw1PRb6MR9'
				. 'gNZ6wub7Plrr0gCiNQOBRk4aIYHA14ZzG9MYaTE0NMy1111Hd3c3juOMNWMJgW3brF27lhUrVvD2W2+VBh/AsQQp18J1xraUY+FY'
				. 'lRmaxAFIKSdssQNubW0ll6uBMGTnsI8UTMjRKAGDgebilhr+4OgaNIpnnn6apUuXctddd7Fjx47IH/g+f3vnnVyyciV79u6dMPgQ'
				. 'LWUDYwjHbYEx6ArFYjPuFaU4cAqCgDPOPINtr2xj5bwmnlh+DAN+iJqUTrBEtCK6/vlOnuwchNADoLa2lvN/bwV79+7j+Y4OLNct'
				. '26KYtKbdBxzKa0Dxe11Lli5l2yvb6OjN8/aQx7EZm7w2pWkrAF9HTvORs49hdXMfD+zs440hn4GBIZ5a8yQA2ZosQRASzrDBhwrM'
				. 'AD+fP+RjN7/0EsvOPhsvNHzj5GZ+dHoLXfkAZ1KmLL6BelvR54Ws3TfEk12DbOzNs2vYAz8AJVBKFjOdppi8S17TPgP+9ac/PeSm'
				. 'V2MMc1tb6ers5IG3e6M8TXOW7sJECPG/er0ASwgub6/livY69uR9XurL81z3MM/tH+bl/jwFLwAhUJbELtaSK2Xvy2naZ0BQ+N3W'
				. '08pxUFLih5rWlMWT589nUW2K7kJUC5i8ejRQihkcKUgriS0FQ4Fm+2CBZ/cN85/vD9LRM0LBC8GSuCqKM5LgMO0ALKWiBfshKH4V'
				. 'CaK8kBdoWtMWDyxpZ2VLDQO+Jq+jJWq5/H0EI4qalRCklSAlJQVteHUgz390DvDv7x5gx0AeYUkcKQkrXJKcdgCHU1KAF0ZO+GsL'
				. 'GrllQSPzMw6eMYwEmsCYUmmyHHJtol4hiSBjCVJKsr8Q8G+7D/D3r+5jfz7AtVVFIcwqABBB0AYCP6QpY3PF0XVc2V7L4jlp6hyF'
				. 'rw2jocYvGnZxkNkRw7CFoN5RbB8scP3znfx23zCuY1UMwqwDEEsJQUFrCDQoyaK6FBfNreGzc7OcXp+m2VUIon1GwyjAKmeqDBBo'
				. 'Q86OTNNVG3azfu9QxWbCrAUAY81Z2hj8UEcRmZLMy9osachwXnOGZY0ZTsq5ZC3JaKAZCXXZWREYQ0ZJhgLNBWt3sGPQQ6rpr8rM'
				. 'agDjJQVIBCEGPzQQRkGX6yg+XZdiZUuOy9pqWVSfohBqRkM9IaoG8I2hybF4qmuAL27YXQVwqIpnBhTzPMXZkXYVl7XV8p2FR3Fc'
				. '1pmS2oDIN6SV4I827mZ99/T3iyaejJsOGSgl1iTgWJKUa+EbeGxnHxet3cnG/SPUlbHzhii18YX2yrwvcEQCGK84FgiKA51OWezN'
				. '+/zxxt1sHyyQVnJCJCxF1KB1VkNlSpKzAoASUfdbue3jmmlfG9K2om/U5x+27yetJONj4KgPydCWrkytala0Jha8cMLP0wDRSBmi'
				. 'ytXHLJ6ExiAsSUfPKH1+iCXEhDRElOauTF/KjAegDXz5+Dm0pW08bUpZjdhZvjZQ4Mn3BlBSfORcjilmQgeCkJFAU2erYhRdec1o'
				. 'AIIosbbqhAbOnVtD3tPEZdrQQMqSbOweZs17A2Mz4qOeGEgriSsF5dJwlcqQzmgfICMCvDHkMVQI6fZ8ugsB3YWAXi/g/RGfeRmb'
				. '+TUOYfjRG2oVAhEaTsm5NBTTF/Gh8SqoKz+1TWY6NKMBCKLEz+sDBVwlpjhjg6HZtbjx+AZ0mTV9+XNGMhiuPW7OlGdfG3CloKPn'
				. 'g9+qPFya0QA0BqTg2e5hRsp0RlhC0OeH/PkJDfz+sfWMjvolSFIwYVMlaJDP+3z95GYubc1xwJ8YEUsBnjb8/L2BitzjzAZgwLYk'
				. 'L/aM0NE7Ss6WlOscD4zhoaXtfOnERgpBSN4L8HyNFxi80OAFhoIfki8EuALuOKOV75/WMqXTOjRQY0le6B1l3d6hitzjjE9FxC9n'
				. 'XNCa46nz5nHgIOkDS0JGSdYVnfLL/Xm6vZDAGNJK0J6yWdqY4fKja1lUl6Lfjwo9488UGkODY3HZb3bx9HsDWPb0P58zHgAUq2Fe'
				. 'wN+c0cq3TjmK90d97DKFeWMgZ0clyNFgLA3tSEHGGvt8pEwiztOGtrTN3W/u5y83deHYqmw75OHWrABQeg1JG/55aTt/Nn8Oe/N+'
				. '2bRyXGhRQkT2VURgxlfDxh+jTbTUnZu2eKJzgGv+dzemCKcSAzMrAEBxmW+iOvH3T2/h5gVNFLRmOPjgmnB87GTFQFJKkrMk/7Kz'
				. 'j29s6iIApJwaeE+XZg0AGBtI39f84TF13LHwKD5dn8LTcU147Pcixu8PYyaqmL0gpQRZJdk57PF3r3bz0I5elJIVHXyYZQBg3E8U'
				. 'eCEZR3Fley1/Mq+eM+vT1NqyVGIMirPFEAFR45J3nja8Oejx+Lv9PPROP90jHo6jSoAqej+zDUAsJQS+Mehib88Z9Wk+05zhrDlp'
				. 'jq9xaHYt0koiRZQBPeBrOkc9tvTnWb9vmA09IwznA7AlbgLtKLFmLYAjRTM6EPskqAogYVUBJKwqgIRVBZCwqgASVhVAwqoCSFhV'
				. 'AAmrCiBhVQEkrCqAhFUFkLD+H/4jkT9G9ad/AAAAAElFTkSuQmCC'
			,
			128 => 'iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAYAAADDPmHLAAAVPElEQVR4nO2deZRcVZ3HP/e+rZZe6XS6E5IQQggJAkIim8CERCEM'
				. 'SgIHOHriIDLKMkbRQcZxXEZlgPHAQWU5yiDKeEYRdYZxUIkGISRhcEJC8ECMBEjSZKWTpju9VNfy3r13/nj1Kk3o6uqsXd1Vn3Ne'
				. 'Tnfe66rXfb/v3t/vd3+/Xwnb8wxVKhY50jdQZWSpCqDCqQqgwqkKoMKpCqDCqQqgwqkKoMKpCqDCqQqgwqkKoMKpCqDCqQqgwqkK'
				. 'oMKpCqDCqQqgwqkKoMKpCqDCsUf6Bvp7e3Ec5+i9XzpNV2cnu3btYvOWLaxfv551L73Ey6+8wra2tsJ1tudh2zZKKYwZu0lTYqRT'
				. 'wo62AIrR2dnJK+vXs3z5cpb+7nesXbsWHQQgJbF4fMwKoSIFYIwpHABSSoQQ77hm3bp1/PTRR/nZY4+xa8eOMSuEihTAYBhj0Fpj'
				. 'jMG2962Mezo6eOSRR7j/gQfYvnUrtuchLQut1Aje7eGjKoAiaK3RWhfE0NHRwV133813770XP5sllkwSBMEI3+WhUxVACYwxKKUK'
				. 'Qlj9wgssWbKEF9euxUskCrPGaGVUuYED1+4DPQ4WIQS2bWOMIQgCzj7rLJ5btYobb7qJbH8/In/NaGVUCUAIcdAHhNO6UoogCFBK'
				. 'obU+oPeO3MJYLMaD3/8+93znO+QymcL50cioWQIiI+1gGMzKH/i6Siksyxr2IA5cFv79xz/muuuuw/U8TP7caGLEA0GliAZnzZo1'
				. 'LF68GCcWO2AhOLZNLB6nob6eiRMncuL06Zx22mmccfrpTJkypbC+K6WGFEtENBv4vs8nrr2WbC7HTTfcgJdIoEaZd1D2AohI9fez'
				. 'adMmEAIO01NWW1/P7DPO4PLLL+eqK69k0qRJwD7RlcJxHHzf58brr2f79u3cfttto847KPslIBqMFStXMn/ePNx4/KCXgoH2gDEG'
				. '3/fRvg/AMePG8Tcf+xi3fuELTJ48uTCVl5oNBi4HH164kN/++tejaiYYVUZg5Jsf7BEZgJERaFkWXiJBLJmka+9e7rv3XmbPmcN9'
				. '999fEEspsQkhkFJijOHhH/yA8a2tBL6PlKPjTzs67vIIET29QRBg2zaxZJLOri4+d/PNXLZoEXs6OpBSlnyao2taW1r49j33oHx/'
				. '1HgFFS2AgUR+vpUXwm+eeIK5c+eyafNmLMsqKYLIRfzY4sVcMHcu2XR6WHbESFMVwH5EQojX1PCXDRu46OKL2bZtG5ZlDdv2+MbX'
				. 'v154rXKnKoAi+L5PrKaGLZs2ccWVV5JOp0tGFSORzJ83j/effz65TKbsZ4GqAIYg8H3iNTW8uGYNt9x667Bmgej8jTfcELqrZW4L'
				. 'VAVQAt/38RIJHvze91i+fHlJeyB64i/70Idobm0ll8mUtUFYFcBwEYIvfulLKKWGHFAhBEopGhsb+cD8+ZhhBpVGiqoAhoFSCi8e'
				. 'Z+0LL/Dk0qVIKQmGmAUiW+GSBQuO4l0eHFUBDJPoqf/BD38Yfj/EtdF+wjlnn43jefj5aGM5UhXAMFFKIW2bFStW8FZ7O5ZlFfUI'
				. 'IrEcP20ax02diirjyGB53lUZYozBcV16urp4/vnnAYoag1EI2XUcTpw+HYwpW0OwKoADIBrE1S+8UPLayB08Ydq0d/xsuVEVwAEQ'
				. 'TfmvvvoqMLxBnTR58hG9p0OlKoADIBLAjh07AIa1rjePG3dE7+lQqQrgIOju7sbP7/iVivfX1dUB5bsvUBXAAWDyod1MNksulxvy'
				. '2mh5cF33aNzaQVMVQIUzanICjzYi/09k5mmTf6qNIRaLlXyyoym/1Ewx0lQFkEcAUggEoIwhMGC0CXf0DEhLEkX0G+rrcRwHMwz/'
				. 'vqenJ3z9MnUDK14AUoBAkFUaosCOJalzLBpdi1pHkrAkG3uz9OtwECdOnAiEvn6pjZ723buP6P0fKhUrAJl/IHO+BmNoTbqcOy7B'
				. 'uU0JTqmPMTnhUO9YJC1JYAznPL2J3oxGALNmzgSGZ9lv37btCP4Wh05FCsASgmwQPu1/1VLDNVMbmD++holxG4nAN4acNvja4EjB'
				. 'us40b6ZyuLZNBjjnnHOAoaf1KEawecsWoHzdwIoSgBCAgWw2YE5zki+f3MxFLbU4UpAKNF05hQEkAiEgMIYGS/CH9j50YAhMjmOa'
				. 'mznvvPOA4oEgYwxSSnzf541Nmwr/V45UjACkAKVBa8M/ndrCrTObiUtBt68xGKQQWPs90a4QdGYV/7mtG8u1CTJp5l14Ic3jxg1Z'
				. 'PRQZh21tbWzduhXLdctWABURBwgH3+BJ+Mm5k/mXU1vwtWGvr5AiXBL2n8x9bWhwLR7dupdN3RncvNHwyeuuK/l+Uc+ANWvXkkun'
				. 'w8onY4bMIRgpxvwMIACjwUHw03On8OEJtbyVDrDlu5/4CGWgxpa83pvjXzfsxvUcMuk0c846iwULFpS0/qOqomXLlhX+z9fhDOBY'
				. '4nCVNh4WxvwMIITAV5p7Zk/ksgm1vJUJcOS7n/gIbcDOn7zpxR10ZALsfOnX7bfdVigDK4YxBsuy6O3t5amnn8ayLHJZn4XH1jG7'
				. 'MY7v64IHUg6MaQFYQpDLBXx0aiPXT2ukPRsUpvLBCIzBkZC0JTe+uINVu3qpSXj0p1Jcc+21XLJgQcnK4aiL2FN/+AM7t23Dy5ez'
				. '/+3xjTwweyK2qM4ARwUBBFrTlHD4+nvG0xdo5BBuW2AMDY6FMnDN6u38fHMXyaRHX28fJ82axf333ovWuuQWcJQP+OBDDwGQVZqJ'
				. 'tR6n1Mc4uynBNSccg+8r7DKJDI5ZAVhCoHzNtcc3ckKNS7/SRX9ZZQyNrsUzu1PMfWYzj7/ZRTIZI5XqZ1xzM//9+OPU19cDQ/v+'
				. 'Ucr4ipUrefqpp4jH46icz+UT65gUd+jMBXxu+jHUeHbBJhhpxqQABOAbg+daXD2pnnSgsYqs+sqE1v4vt/WwaOUWXu3JUJOMkerr'
				. 'o6WlhaVLlzJr5sxC95CS7y0Ed9x5J1optBDEHIuPT20gqw1ZZZhZF+Oi1lpUUB79A8amAAQopTmlIcZJtR5pbQY1vLSBhCXZ0J3l'
				. '02t3IKQg4Tn09fYx533vY+XKlbxvzpxhdQyJrvn9smU8tWwZ8USCbCbHFZPrOb0hTl+gCvfw4Qm1DJ1YfvQYkwKQCNCGMxriJC2J'
				. 'KmJ1GQwxS/Kd1zpI5QI826I/1c9nPvtZnlu1ihknnjiswY+8glQqxWdvvhkpJdoYEq7FLTPGkVEaiUAKQUZpzmiMURsrDw98VAlg'
				. 'uO3gIk6pDzt3DYYBXCnZmfZ5encK23PpT2e4+iMf4f777sPzvGHt9gFhXwHL4vO33MLrGzcSi8fIpnN8+sQmTq7z6A40GoMyhn6l'
				. 'afZsjk+WR3PM8pDhMIjKrXzfL1qhK4QIK3gxIAVTEi7KGAbz+o0BzxK0pXLszvg4tk1gDNd/6lPvahM7FL7v4zgO//bQQzz80EPE'
				. 'kwnSmRyXTGngrtMm4BtDq2cXkku0Ac+WzKrz2NCTPdQ/yyEzagTgOA4NjY24RdrECSHwfZ++3l4M4WxQ58gwk2eQ1zOAJaAzp9Aa'
				. 'jFZIx2HChAnDruKJBv83v/0tS5YswY3FUCp0N8d5Fv/853bS+7mfxoBrCV7vK49MobIXQDQFn3Xmmbzx+uuDumFKKSzb5n+fe46F'
				. 'CxfixuMIo7GEKLoEQCSC8PWEEBit0UoNq8NXNPhPLl3KVVddhZAS8hVBQgp+srmLgvoG3kT0vS2xyyAkWPYCiHAch6ampiGvaZ0w'
				. 'ofC1xpDVxX1/QRifPzbh4NkSIyRGKR597DG+deedqLwQ9m8cGW30OI7Dz37+cz5x7bUorbFs+x0zk+vI0BgtgjJmSHEeLUaVEVis'
				. 'EXQUfk0kEliuG+7+aMPenELK0NrfHykgrQwn1bqc0Rgjl/WJx2Pcdddd3HX33ViWVWgfGwQBWmuCIEBKiWVZ3H7HHSz+6EfRxrxr'
				. '8CF8+ANjih7lMPgwygRQzPKPntLWlhYaGhrQgQIDm/t8LIovAwaDJQRfntUMAgJtcB2Hf/ziF7lw/nz+54knyGQy2LaNlBLbtmlr'
				. 'a+OyhQv52le/Gi41Uh5048pyoOw7hQ6XyBeffeaZ/OnFF8F2WTy1gR+dOYm3c0HR2Lsy0OBKfty2l79bsx2EIOk5pPpSAMyYOZMP'
				. 'XXopixYt4uWXX+abt93G23v2jLqWsMUYMwKImj1+6oYb+NHDDyO8OFPjkufmTwt34Ib4WWUMTa7N4zt6+Py6nbSnckjPIWYJ+lP9'
				. '77zYsvA8b9S0gi3FqFoChsMlCxZgjMGzBJt7sqzc00+NXTwaCKEn0JELWDSxlmfnT+MzM5tpcQT96RzYLk4iSTwRJ5FM4LnumBl8'
				. 'KIMZINXTc9iWACEEPT09nHTyyXS0t6OFxQUtSZ68YCo9viqaARShTBgaTlqSbWmf3+/q5Vc7eljd2U9PRgEGLImTzybSmKhuZNQy'
				. '4gLw85+4cTi5/c47+dpXvkIimaQ/k+PBsybxyeMbac9nAw2FIRSCJyU1tsQ3hs19OZ7vSPHs7hSrO9O0pXIQaJACy5Z5+8KgR6EY'
				. 'RlwAc2bPPmxlU9EvkslkWL9+PZaUaG1IWoKlc4/n9IYYnTlVUgTRa+l8GDlmCeKWxAAd2YANPVlW7E7xzO4+/rQ3QyYXgAjF4ORn'
				. 'hjLZ7i/JiAsgyB6ZeLjteUBY75cLFMclXZ64YCozal3ezirsIfIC9ycSA4AjBTFL4kpBf6B5rTfHM7v7WLqrl9Vv95PNKbBkGFwa'
				. 'BUIYcQFIOVS87MAw7Iu0DvTNo0qgY+MOj5w9mXnjk3TmFIExg6aEl3oPY8JIoyXys4OUZLRhQ3eGX+/s4b+297BxbwYkeLaFLqPA'
				. 'z/6MuACOFlIIckrjCviHWeP53Iwm6myLHl/hG5Pfrz/w19V5MUgECTucHTqzimVv9fK9NzpZvSeFtCW2pCxng4oRAIThX20g8BWn'
				. 'NMa5ecY4Lp1QS3PMJqvCvXp1GMRgC0GdY5FVmkfauvj6K+30+BqvhDs6ElSUAGBfH4BsoEDDtHqPK46t47KJdZxWHyPpSHLKkFYa'
				. 'P1/NIw9wmQAKy0uTa7GuK83HV29nY3cGz7HKSgQVJ4CId/QFCDSWY3FGQ4yLW2uY31LDKfUxGp0wuSStwmrhaHYQYvgZfVGJ2e5M'
				. 'wKLn3uSVrnRZiaBiBRAhRZhDGBhDoDQog7AlJ9V6vH9cgrnNSWY3xpmccEjYkpw29Af6gOyGwBhqbYudaZ+LVmxhR7+PbYmysAkq'
				. 'XgADicSgjMFXBrQGIajzbE6t9zh/XJILxyc5vSHOMZ5FVhlSgS5kFw2FbwxNrsWzu1MsXNUGJfYnjhZVARQhEoPBkNNglM4XDkpm'
				. '1Hpc3FLDFZPqOPOYBFIwrFCzrw2tcZubXtzJwxs7sN2R/xyBqgCGQfgJ4QNmB20g0EhHMq85yRdmNjN/fA17fVW4fjA0EJdhPuC8'
				. '5ZvJlMEaMOZ2A48EYSSQQiaPawlino0lBE+/1celK7bwlVfeImHJd6UADkQSZiHNqHX5QEvNUbv/oagK4CAYKAbPsXAtyT3r21my'
				. 'bidxa+g/qQFsIbi4tSqAMUGU3BmPO/zH6x3cvXFPvsp48HlACshpw+kN8aN7o0WoCuAwYAjzCW3X4ruvvc2mvhwxKQddCgTh7DEp'
				. 'Xh6VQWNaAFa+8VOp43BsRhnAkpLejM+ze/pIDBH21QZipfzGo8SoqQs4UAyEW7PD8LalbR2Wti0CEAZe682VVRuYoRhzAgh7/ULC'
				. 'Enxt9gTqHYugSIeu6No7/rKH9nQYnTvUCK0RhMGhkffwhsWYEwDk8wKE4OpJ9UxNumT14AIwhFPxL7Z181Yqh7QEh5ruKQzUOrLk'
				. 'J8aWiz7GnACisGx/oHmz36fBtegPBu/MFRjDONdhTmOcVbt681XEBz80hnAGOKnWG7Io1RGCXZny+CzBMWkEWvkOITvTPjEpCs0g'
				. '9z8cETaCvuLYOizn0Pbqw6ZUhtqYwwXjEmFPokGmAWPAlYI/d498aTiMUQGIfIeQP+3NYMvimy5SQF+gOKspztVTGvCHmTA6GLYU'
				. 'qFzAJ6Y2ML3GI6N00RlACFi+p++g3udwMyYFEDWIeHZPKj/9D9HVWwhSgeFbp7YwvSFGOquGbCS5P4IwUTSdCZjTUsOXZ42nO1CD'
				. 'P/2AK2FPJuDJXVUBHDG0AduWvNTZz7quNDWWLLr3Lggjc8e4Fr887zhOqvdIZwI0YcjWEuESMvCwxL5zvjGk0wHntdbwi3OnELME'
				. 'Sg++/qt8XsDv2/vY2nP46yEOhjEpAAgHKAg0D27uxJVhrn4xLAF9geaEpMPvLjyeT57YRFwIMrmAbC4g52tygckfmqyv8+cUEzyb'
				. 'r763lV+dfxyNrkVaDd6RLHwfQVppHtzUWTYfITOmt4MFYXv4X10wlQ+MT9JVokOnyhtoNbZkQ0+Gp9v7WNOZ5s1+n25fofIRvCbX'
				. 'YnqNy7lNSeaOTzI57hTOFxt8XxtaYjYPvPE2f792B55rl0Va2JgWgBTgB4YTal2emTeNpC3JKjNk9k5UBBK3ws8K0kBGaXLaRPkg'
				. 'eFLiSYEmDPpktR4ypBwYQ51tsbE3ywef3UxfYBCiPGIBY1oAkC8KySkuOraWX75/Cn7+o2BKZe9EKd4Ds4ILRSeDnCtGYAwJS5IK'
				. 'NJesbGP93jSuXdwmOdqMWRsgQuVbxj61o4eP/992LCFIWLJkr145wNCLBlgPcW4wfB0++X2B5urnt7K+M52vFDocv9nhYcwLACIR'
				. '2DyxbS+XrWpjR9qnOWajDQe0Dg/XbFMmXC7Gx2xe7c3y1yu28Mc9KTy3fNLBI8b8EjAQSwiyvqI5bvPNU1pZPKUez5L0+GGd4MFW'
				. 'BMG+snJLCOry6eOPtHXxjfXtdOcUnl1+gw8VJgDIi0BrCAznjk+y5MQmLm6pKbhwGaVRZl+haWQqDNRFVCAafW0J8KQgYUv6A81T'
				. '7X18e2MHf9zdl68LlIXq4nKj4gQAA8rD8lm872mIccWkej7YUsOMWo86Rxa2ipUxeUHs+zNJRBgMykcM00rTlvJZvruPX2zrZk1H'
				. '2FfIc8q7MhgqVAARoTuYrxNUYUrXe+o8ZjfGeW9DjBNqPFpjNnVO6PZB6AGklKYjq2hL5VjfnWFNZ5qX9qbpTQcgBe4AAZU7FS2A'
				. 'KhXiBVQpTlUAFU5VABVOVQAVTlUAFU5VABVOVQAVTlUAFU5VABVOVQAVTlUAFU5VABVOVQAVTlUAFU5VABVOVQAVTlUAFc7/AzYS'
				. 'HotXTm+RAAAAAElFTkSuQmCC'
			,
		);
	}

	private static function icon( $size ) {
		header( 'Content-Type: image/png' );
		header( 'Cache-Control: public, max-age=86400' );

		/*
		 * The DesignStager logo, embedded. AppSource requires the add-in's
		 * icons to load every time (policy 1100.7), so they no longer depend
		 * on the WordPress Site Icon being set or on GD being installed.
		 * To change the logo, replace these strings with
		 * base64_encode( file_get_contents( 'icon-NN.png' ) ).
		 */
		$logo = self::logo_icons();
		if ( isset( $logo[ $size ] ) ) {
			$png = base64_decode( $logo[ $size ] ); // phpcs:ignore
			header( 'Content-Length: ' . strlen( $png ) );
			echo $png; // phpcs:ignore
			exit;
		}
		// Use the DesignStager logo set as the WordPress Site Icon (Settings > General).
		$site_icon = (int) get_option( 'site_icon' );
		$file      = $site_icon ? get_attached_file( $site_icon ) : '';
		if ( $file && file_exists( $file ) && function_exists( 'imagecreatefromstring' ) ) {
			$src = @imagecreatefromstring( file_get_contents( $file ) ); // phpcs:ignore
			if ( $src ) {
				$out = imagecreatetruecolor( $size, $size );
				imagesavealpha( $out, true );
				imagealphablending( $out, false );
				imagefill( $out, 0, 0, imagecolorallocatealpha( $out, 0, 0, 0, 127 ) );
				imagealphablending( $out, true );
				imagecopyresampled( $out, $src, 0, 0, 0, 0, $size, $size, imagesx( $src ), imagesy( $src ) );
				imagepng( $out );
				exit;
			}
		}
		if ( ! function_exists( 'imagecreatetruecolor' ) ) {
			echo base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=' ); // phpcs:ignore
			exit;
		}
		$big = $size * 4;
		$im  = imagecreatetruecolor( $big, $big );
		imagesavealpha( $im, true );
		imagefill( $im, 0, 0, imagecolorallocatealpha( $im, 0, 0, 0, 127 ) );
		$bg = imagecolorallocate( $im, 17, 17, 17 );
		$r  = (int) ( $big / 5 );
		imagefilledrectangle( $im, $r, 0, $big - $r, $big, $bg );
		imagefilledrectangle( $im, 0, $r, $big, $big - $r, $bg );
		foreach ( array( array( $r, $r ), array( $big - $r, $r ), array( $r, $big - $r ), array( $big - $r, $big - $r ) ) as $c ) {
			imagefilledellipse( $im, $c[0], $c[1], $r * 2, $r * 2, $bg );
		}
		$fg = imagecolorallocate( $im, 255, 255, 255 );
		$f  = 5;
		$tw = imagefontwidth( $f ) * 2;
		$th = imagefontheight( $f );
		$t  = imagecreatetruecolor( $tw, $th );
		imagefill( $t, 0, 0, $bg );
		imagestring( $t, $f, 0, 0, 'DS', $fg );
		$w = (int) ( $big * 0.62 );
		$h = (int) ( $w * $th / $tw );
		imagecopyresized( $im, $t, (int) ( ( $big - $w ) / 2 ), (int) ( ( $big - $h ) / 2 ), 0, 0, $w, $h, $tw, $th );
		$out = imagecreatetruecolor( $size, $size );
		imagesavealpha( $out, true );
		imagefill( $out, 0, 0, imagecolorallocatealpha( $out, 0, 0, 0, 127 ) );
		imagecopyresampled( $out, $im, 0, 0, 0, 0, $size, $size, $big, $big );
		imagepng( $out );
		exit;
	}


	/* ------------------------------------------------------------------
	 * Library API for the task pane: /ds-office/<version>/library
	 *
	 * The pane searches and inserts through this, so the add-in is a
	 * PowerPoint tool in its own right rather than a window pointed at the
	 * website - which is what Marketplace policy 1120.1 rejects. Reading is
	 * public so the pane can show real content before anyone signs in
	 * (policy 1120.1 again: no sign-in wall on first run). Files themselves
	 * still go through the proxy, where the login check, Download Monitor's
	 * permissions, the plan check and the daily limits all still run.
	 * ------------------------------------------------------------------ */
	private static function term_ids( $wanted ) {
		$ids = array();
		foreach ( $wanted as $w ) {
			$t = get_term_by( 'slug', sanitize_title( $w ), 'dlm_download_category' );
			if ( ! $t || is_wp_error( $t ) ) {
				$t = get_term_by( 'name', $w, 'dlm_download_category' );
			}
			if ( $t && ! is_wp_error( $t ) ) {
				$ids[] = (int) $t->term_id;
			}
		}
		return array_values( array_unique( $ids ) );
	}

	private static function tpl_term_ids() {
		return self::term_ids( apply_filters( 'ds_office_template_cats', array( 'powerpoint-templates' ) ) );
	}

	private static function skip_term_ids() {
		return self::term_ids( apply_filters( 'ds_office_skip_cats', array(
			'audio', 'audios', 'background-music', 'ai-music', 'ambience', 'cinematic-trailer',
			'jingles-intros', 'sound-effects', 'video', 'videos', 'motion-graphics', 'motion',
			'icons', 'icon',
		) ) );
	}

	/* The Images tab shows the same category as the website's
	   /marketplace/?type=visual&cat=images page. */
	private static function img_term_ids() {
		return self::term_ids( apply_filters( 'ds_office_image_cats', array( 'images' ) ) );
	}

	private static function item_tier( $post_id ) {
		if ( function_exists( 'ds_ppt_mkt_item_tier' ) ) {
			return ds_ppt_mkt_item_tier( $post_id );
		}
		$tags  = get_the_terms( $post_id, 'dlm_download_tag' );
		$slugs = array();
		if ( is_array( $tags ) ) {
			foreach ( $tags as $t ) {
				$slugs[] = strtolower( $t->slug );
			}
		}
		return in_array( 'pro', $slugs, true ) ? 'pro' : 'free';
	}

	private static function can_pro() {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		$tier = function_exists( 'ds_marketplace_get_user_tier' ) ? ds_marketplace_get_user_tier() : 'free';
		return in_array( $tier, array( 'premium', 'pro', 'business' ), true );
	}

	private static function item_file_url( $post_id ) {
		if ( function_exists( 'download_monitor' ) && method_exists( download_monitor(), 'service' ) ) {
			$obj = download_monitor()->service( 'download_repository' )->retrieve_single( $post_id );
			if ( $obj ) {
				$link = $obj->get_the_download_link();
				if ( $link ) {
					return $link;
				}
			}
		}
		return get_permalink( $post_id );
	}

	public static function maybe_serve_library() {
		$name = '';
		if ( ! empty( $_GET['ds_office_asset'] ) ) {
			$name = sanitize_file_name( wp_unslash( $_GET['ds_office_asset'] ) );
		} else {
			$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
			if ( $path && preg_match( '#/ds-office/(?:[0-9][0-9.]*/)?([A-Za-z0-9._-]+)$#', $path, $mm ) ) {
				$name = sanitize_file_name( $mm[1] );
			}
		}
		if ( 'library' === $name ) {
			self::library_json();
		}
	}

	public static function library_json() {
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'X-Content-Type-Options: nosniff' );

		$kind     = ( isset( $_GET['kind'] ) && 'images' === $_GET['kind'] ) ? 'images' : 'templates'; // phpcs:ignore
		$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore
		$page     = isset( $_GET['page'] ) ? max( 1, (int) $_GET['page'] ) : 1; // phpcs:ignore
		$per_page = isset( $_GET['per_page'] ) ? (int) $_GET['per_page'] : 24; // phpcs:ignore
		$per_page = min( 48, max( 6, $per_page ) );
		$tpl_ids  = self::tpl_term_ids();

		$args = array(
			'post_type'      => 'dlm_download',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			/* date + ID: many items share a date, and ordering by date alone let
			   pages repeat items and skip others (1,295 listed, 1,029 reachable). */
			'orderby'        => array( 'date' => 'DESC', 'ID' => 'DESC' ),
		);
		if ( '' !== $search ) {
			$hits = ds_office_mkt_search_ids( $search );
			$args['post__in'] = $hits ? $hits : array( 0 );
		}
		$tax     = array();
		$img_ids = 'images' === $kind ? self::img_term_ids() : array();
		if ( $img_ids ) {
			$tax[] = array(
				'taxonomy'         => 'dlm_download_category',
				'field'            => 'term_id',
				'terms'            => $img_ids,
				'include_children' => true,
				'operator'         => 'IN',
			);
		}
		if ( $tpl_ids ) {
			$tax[] = array(
				'taxonomy' => 'dlm_download_category',
				'field'    => 'term_id',
				'terms'    => $tpl_ids,
				'operator' => 'templates' === $kind ? 'IN' : 'NOT IN',
			);
		}
		$skip_ids = self::skip_term_ids();
		if ( $skip_ids ) {
			$tax[] = array(
				'taxonomy' => 'dlm_download_category',
				'field'    => 'term_id',
				'terms'    => $skip_ids,
				'operator' => 'NOT IN',
			);
		}
		if ( $tax ) {
			$args['tax_query'] = count( $tax ) > 1 ? array_merge( array( 'relation' => 'AND' ), $tax ) : $tax;
		}

		$q       = new WP_Query( $args );
		$can_pro = self::can_pro();
		$logged  = is_user_logged_in();
		$items   = array();

		foreach ( $q->posts as $post ) {
			$thumb_id = (int) get_post_thumbnail_id( $post->ID );
			if ( ! $thumb_id ) {
				$att = get_posts(
					array(
						'post_type'      => 'attachment',
						'post_mime_type' => 'image',
						'post_parent'    => $post->ID,
						'posts_per_page' => 1,
						'orderby'        => 'menu_order',
						'order'          => 'ASC',
					)
				);
				if ( $att ) {
					$thumb_id = (int) $att[0]->ID;
				}
			}
			if ( ! $thumb_id ) {
				continue;
			}

			$thumb = function_exists( 'ds_mkt_get_grid_url' ) ? ds_mkt_get_grid_url( $thumb_id ) : '';
			if ( ! $thumb ) {
				$thumb = wp_get_attachment_image_url( $thumb_id, 'medium' );
			}
			/* Marketplace images often have no smaller sizes, so 'medium' is the
			   4000-6000 px original (30-60 MB). The pane would pull hundreds of MB
			   per page. Use a small cached thumbnail instead. */
			$orig = wp_get_attachment_url( $thumb_id );
			if ( $thumb && $orig && strtok( $thumb, '?' ) === strtok( $orig, '?' ) && 'image/gif' !== get_post_mime_type( $thumb_id ) ) {
				$thumb = self::asset_url( 'thumb', array( 'id' => $thumb_id ) );
			}
			if ( ! $thumb ) {
				continue;
			}

			$tier = self::item_tier( $post->ID );

			if ( 'images' === $kind ) {
				$file = function_exists( 'ds_mkt_get_watermarked_full_url' ) ? ds_mkt_get_watermarked_full_url( $thumb_id ) : '';
				/* The item's real download file comes first. Some items' featured
				   image is only a 400 px .webp thumbnail (thumb-*.webp), which is
				   what used to be inserted: tiny, and drawn partly by PowerPoint. */
				if ( ! $file && function_exists( 'ds_office_extra_file_url' ) ) {
					$dl = ds_office_extra_local_url( ds_office_extra_file_url( $post->ID ) );
					if ( $dl && preg_match( '/\.(png|jpe?g|gif|bmp|webp|avif)$/i', (string) strtok( $dl, '?' ) ) ) {
						/* Files in Download Monitor's protected folder cannot be read
						   directly (HTTP 403); its Download link serves them, with
						   the plan checks. */
						$file = ( false !== strpos( $dl, '/dlm_uploads/' ) ) ? self::item_file_url( $post->ID ) : $dl;
					}
				}
				if ( ! $file ) {
					$file = wp_get_attachment_image_url( $thumb_id, 'full' );
				}
			} else {
				$file = self::item_file_url( $post->ID );
			}

			$items[] = array(
				'id'     => (int) $post->ID,
				'title'  => html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, 'UTF-8' ),
				'thumb'  => $thumb,
				'kind'   => $kind,
				'tier'   => $tier,
				'locked' => ( 'pro' === $tier && ! $can_pro ),
				// Only signed-in users get a file URL; guests browse, they do not download.
				'insert' => $logged ? $file : '',
			);
		}

		$who = $logged ? wp_get_current_user() : null;

		echo wp_json_encode(
			array(
				'items' => $items,
				'page'  => $page,
				'pages' => (int) $q->max_num_pages,
				'total' => (int) $q->found_posts,
				'kind'  => $kind,
				'auth'  => array(
					'loggedIn' => $logged ? 1 : 0,
					'canPro'   => $can_pro ? 1 : 0,
					'name'     => $who ? ( $who->display_name ? $who->display_name : $who->user_login ) : '',
				),
			)
		);
		exit;
	}

	/* ------------------------------------------------------------------
	 * CSS applied only inside the PowerPoint window: the site owner's own
	 * rules, plus the pricing/checkout suppression Marketplace policy
	 * 1100.3 requires while "Marketplace mode" is on.
	 * ------------------------------------------------------------------ */
	public static function office_css( $o = null ) {
		$o   = is_array( $o ) ? $o : self::opts();
		$css = (string) $o['hide_css'];
		/* Inside PowerPoint (side panel and large window): Update with AI / Preserve My Text / Browse Templates sit one
		   under another (in the two-column layout they were squeezed into thin vertical strips), and the "Paid plans"
		   label sits under the card text instead of covering the title. */
		$css .= "\n" . 'html.ds-office-mode #dsp-home.dssl #dsp-mode-row{display:flex!important;flex-direction:column!important;flex-wrap:nowrap!important;gap:10px!important}'
			. 'html.ds-office-mode #dsp-home.dssl #dsp-mode-row>*{flex:none!important;width:100%!important;max-width:none!important;min-width:0!important;box-sizing:border-box!important}'
			. 'html.ds-office-mode body.dsfp #dsp-mode-row>.dsfp-locked::after{position:static!important;display:inline-block!important;margin-top:8px!important}'
			/* Download / Insert menu never wider than the window (it was cut off on the left in the side panel), and no
			   "Upgrade" label inside PowerPoint (policy 1100.3); a locked format still explains itself when clicked. */
			. 'html.ds-office-mode #dsx-menu{max-width:calc(100vw - 16px)!important;min-width:0!important;box-sizing:border-box!important}'
			. 'html.ds-office-mode #dsx-menu .dsx-lock{display:none!important}'
			/* free-plan pop-ups taller than the window scroll, so their buttons are never cut off */
			. 'html.ds-office-mode .dsfp-ov{align-items:flex-start!important;overflow-y:auto!important;padding:10px!important}'
			. 'html.ds-office-mode .dsfp-card{margin:auto 0!important;max-height:none!important}'
			/* Review Your Slides in the narrow side panel: the whole card scrolls and Back / Generate stay at the bottom */
			. '@media (max-width:640px){html.ds-office-mode :is(#dsp-ai-rv-panel,#dsp-rv-panel,#dsdi-rv){padding:6px!important;align-items:stretch!important}html.ds-office-mode :is(#dsp-ai-rv-panel,#dsp-rv-panel,#dsdi-rv)>div{max-height:100%!important;height:auto!important;overflow-y:auto!important;border-radius:10px!important}html.ds-office-mode :is(#dsp-ai-rv-panel,#dsp-rv-panel,#dsdi-rv)>div>div{flex-shrink:0!important}html.ds-office-mode :is(#dsp-ai-rv-panel,#dsp-rv-panel,#dsdi-rv)>div>div:first-child{padding:12px!important}html.ds-office-mode :is(#dsp-ai-rv-panel,#dsp-rv-panel,#dsdi-rv)>div>div:first-child>div:first-child{font-size:16px!important;line-height:1.3!important}html.ds-office-mode :is(#dsp-ai-rv-panel,#dsp-rv-panel,#dsdi-rv)>div>div:first-child>div:last-child{font-size:12px!important;line-height:1.4!important}html.ds-office-mode :is(#dsp-ai-rv-list,#dsp-rv-list,#dsdi-rv-list){flex:none!important;overflow:visible!important;padding:10px!important}html.ds-office-mode :is(#dsp-ai-rv-list,#dsp-rv-list,#dsdi-rv-list)>div{padding:12px!important}html.ds-office-mode :is(#dsp-ai-rv-panel,#dsp-rv-panel,#dsdi-rv)>div>div:last-child{position:sticky!important;bottom:0!important;z-index:2!important;background:#161b22!important;padding:10px!important;flex-direction:column-reverse!important;align-items:stretch!important;gap:8px!important}html.ds-office-mode :is(#dsp-ai-rv-panel,#dsp-rv-panel,#dsdi-rv)>div>div:last-child>button{width:100%!important;white-space:normal!important}}';
		if ( empty( $o['hide_upsell'] ) ) {
			return $css;
		}
		$css .= "\n/* Marketplace policy 1100.3: no selling inside the add-in */\n"
			. 'a[href*="/pricing"],a[href*="/checkout"],a[href*="/plans"],a[href*="/upgrade"],a[href*="/customize"],'
			. '.ds-upgrade,.ds-upgrade-btn,.ds-upgrade-popup,.ds-upgrade-modal,.ds-pricing-cta,'
			. '.ds-plan-banner,.ds-buy,.ds-buy-btn,[data-ds-upgrade],'
			. '[onclick*="dsTab(\'plans\'"],#ds-tab-plans{display:none!important}';
		return $css;
	}

	public static function assets() {
				$a = array();
$a['taskpane.html'] = array( 'text/html; charset=utf-8', <<<'DSASSET'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DesignStager</title>
  <style>
    :root {
      --bg:#0b1020; --card:#141a2e; --line:#232b45; --text:#eef1f8; --muted:#9aa3b8;
      --brand:#f15a29; --brand2:#ff7a45; --ok:#22c55e; --warn:#f59e0b; --pad:12px;
    }
    /* No light variant: the pane uses fixed dark colours throughout, and a half-light pane
       hid text on light-mode PCs (credits, tile names, "Insert audio"). */
    * { box-sizing: border-box; }
    html, body { margin:0; height:100%; }
    html { overflow-x:hidden; }
    body {
      background:var(--bg); color:var(--text);
      font:14px/1.45 "Segoe UI", system-ui, -apple-system, sans-serif;
      display:flex; flex-direction:column; overflow-x:hidden;
    }
    button { font:inherit; color:inherit; }
    :focus-visible { outline:2px solid var(--brand); outline-offset:2px; }

    header { padding:9px var(--pad); border-bottom:1px solid var(--line); display:flex; align-items:center; gap:8px; }
    header img { width:22px; height:22px; border-radius:5px; flex:0 0 22px; }
    header b { font-size:14.5px; letter-spacing:-.2px; white-space:nowrap; }
    header b span { color:var(--brand); }

    .acct { display:flex; align-items:center; gap:6px; padding:7px var(--pad); border-bottom:1px solid var(--line); font-size:12px; }
    .acct .av { width:20px; height:20px; border-radius:50%; background:var(--brand); color:#fff; display:grid; place-items:center; font-size:10px; font-weight:700; flex:0 0 20px; }
    .acct #ds-acct-name { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--muted); }
    .acct .do { margin-left:auto; display:flex; gap:5px; flex:0 0 auto; }
    .linkbtn { background:transparent; border:1px solid var(--line); border-radius:7px; padding:4px 9px; font:600 11.5px/1 inherit; cursor:pointer; }
    .linkbtn:hover { border-color:var(--brand); color:var(--brand); }
    .linkbtn[hidden] { display:none; }
    .dsask { display:flex; align-items:center; gap:6px; flex-wrap:wrap; padding:8px var(--pad); border-bottom:1px solid var(--line); background:var(--card); font-size:12px; }
    .dsask span { flex:1 0 100%; min-width:0; font-weight:600; }
    .dsask .dsno { margin-left:auto; }
    .dsask .dsyes { border-color:#e5484d; color:#ff6b6b; }
    .dsask .dsyes:hover { background:#e5484d; color:#fff; border-color:#e5484d; }

    .tools { padding:10px var(--pad) 0; display:flex; flex-direction:column; gap:8px; }
    .search { display:flex; gap:6px; }
    .search input { flex:1 1 auto; min-width:0; background:var(--card); color:var(--text); border:1px solid var(--line); border-radius:8px; padding:8px 10px; font:inherit; }
    .search input::placeholder { color:var(--muted); }
    .search button { flex:0 0 auto; border:0; border-radius:8px; padding:0 12px; cursor:pointer; background:linear-gradient(90deg,var(--brand),var(--brand2)); color:#fff; font-weight:600; }
    .tabs { display:flex; gap:6px; flex-wrap:wrap; }
    .tab { border:1px solid var(--line); background:var(--card); border-radius:999px; padding:5px 11px; font-size:12px; cursor:pointer; }
    .tab[aria-selected="true"] { border-color:var(--brand); color:var(--brand); font-weight:600; }

    main { flex:1 1 auto; overflow-y:auto; overflow-x:hidden; padding:10px var(--pad) 14px; }
    .grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(118px,1fr)); gap:10px; }
    @media (max-width:268px) { .grid { grid-template-columns:1fr; } }

    .card { background:var(--card); border:1px solid var(--line); border-radius:10px; overflow:hidden; display:flex; flex-direction:column; }
    .card .thumb { position:relative; width:100%; aspect-ratio:4/3; background:#0f1426; overflow:hidden; }
    .card .thumb img { width:100%; height:100%; object-fit:cover; display:block; }
    .card .tier { position:absolute; top:6px; left:6px; font-size:9.5px; font-weight:700; letter-spacing:.4px; padding:2px 6px; border-radius:999px; background:rgba(0,0,0,.65); color:#fff; text-transform:uppercase; }
    .card .tier.pro { background:var(--brand); }
    .card .meta { padding:7px 8px 8px; display:flex; flex-direction:column; gap:6px; flex:1 1 auto; }
    .card .name { font-size:12px; line-height:1.3; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; word-break:break-word; }
    .card .ins { margin-top:auto; border:1px solid var(--line); background:transparent; border-radius:7px; padding:6px 8px; font-size:11.5px; cursor:pointer; width:100%; }
    .card .ins:hover:not(:disabled) { border-color:var(--brand); color:var(--brand); }
    .card .ins:disabled { opacity:.55; cursor:default; }

    .skel { background:linear-gradient(90deg,var(--card),var(--line),var(--card)); background-size:200% 100%; animation:sh 1.1s linear infinite; border-radius:10px; height:150px; }
    @keyframes sh { from { background-position:200% 0; } to { background-position:-200% 0; } }
    @media (prefers-reduced-motion:reduce) { .skel { animation:none; } }

    .note { grid-column:1/-1; border:1px solid var(--line); background:var(--card); border-radius:10px; padding:12px; font-size:12.5px; color:var(--muted); }
    .note b { color:var(--text); display:block; margin-bottom:4px; font-size:13px; }
    .note button { margin-top:9px; border:1px solid var(--line); background:transparent; border-radius:7px; padding:6px 10px; font-size:12px; cursor:pointer; }
    .note button:hover { border-color:var(--brand); color:var(--brand); }

    .more { width:100%; margin-top:10px; border:1px solid var(--line); background:var(--card); border-radius:8px; padding:9px; font-size:12.5px; cursor:pointer; }
    .more:hover { border-color:var(--brand); color:var(--brand); }

    footer { border-top:1px solid var(--line); padding:8px var(--pad); display:flex; align-items:center; gap:8px; font-size:11.5px; color:var(--muted); }
    .dot { width:8px; height:8px; border-radius:50%; background:var(--ok); flex:0 0 8px; }
    .dot.busy { background:var(--warn); }
    footer #ds-status { min-width:0; overflow:hidden; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; white-space:normal; line-height:1.35; }
    footer .dot { flex:0 0 auto; align-self:flex-start; margin-top:4px; }
    footer .full { margin-left:auto; flex:0 0 auto; background:none; border:0; color:var(--muted); text-decoration:underline; cursor:pointer; font-size:11.5px; padding:0; }
    footer .full:hover { color:var(--brand); }
    #log { display:none; }
    .sr { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0 0 0 0); white-space:nowrap; }
  .dsbill{display:flex;align-items:center;gap:8px;padding:9px 14px 0;flex-wrap:wrap}
  .dsbill[hidden]{display:none}
.dsplan{font-size:10.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;padding:4px 9px;
        border-radius:999px;background:rgba(255,107,53,.16);color:#ff9142;border:1px solid rgba(255,107,53,.35)}
.dscred{font-size:12.5px;color:#eef3ff;font-weight:600}
.dscred span{color:#8ea3c9;font-weight:500}
.dsbuy{margin-left:auto;border:0;border-radius:8px;padding:6px 12px;font:inherit;font-size:12px;
       font-weight:700;cursor:pointer;background:linear-gradient(135deg,#ff6b35,#ff9142);color:#fff}
.dsbigbtn{width:100%;margin:8px 0 0;padding:8px;border-radius:9px;border:1px solid #24365a;
          background:transparent;color:#8ea3c9;font:inherit;font-size:12px;cursor:pointer}
.dsbigbtn:hover{border-color:#ff6b35;color:#ff6b35}
/* Marketplace / AI Suites switch */
.dsmode{display:flex;gap:8px;padding:10px 14px 0}
.dsm{flex:1;padding:9px 10px;border-radius:10px;cursor:pointer;font:inherit;font-size:13px;font-weight:600;
     background:#16233d;border:1px solid #24365a;color:#8ea3c9;transition:background .15s,border-color .15s,color .15s}
.dsm:hover{border-color:#ff6b35;color:#eef3ff}
.dsm.on{background:linear-gradient(135deg,#ff6b35,#ff9142);border-color:transparent;color:#fff}
    /* DS: audio track rows, matching the marketplace audio section (added) */
.dsrow{grid-column:1/-1;background:#0a0a0a;border:1px solid #1f222b;border-radius:12px;padding:12px;}
.dsrowtop{display:flex;align-items:center;gap:8px;margin-bottom:10px;}
.dskind{font-size:9.5px;font-weight:800;letter-spacing:.03em;text-transform:uppercase;color:#2563eb;background:rgba(37,99,235,.12);padding:3px 8px;border-radius:999px;flex:0 0 auto;}
.dsrowtitle{font-size:12.5px;font-weight:700;color:#e5e7eb;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1 1 auto;min-width:0;}
.dsrowtag{font-size:9.5px;font-weight:700;padding:3px 8px;border-radius:999px;flex:0 0 auto;background:rgba(234,179,8,.15);color:#eab308;}
.dsrowtag.pro{background:rgba(34,197,94,.15);color:#22c55e;}
.dsplayer{display:flex;align-items:center;gap:10px;}
.dsdisc{position:relative;flex:0 0 auto;width:56px;height:56px;}
.dsdisc img,.dsdisc .dsfallback{width:56px;height:56px;border-radius:50%;object-fit:cover;display:block;background:#161616;border:1px solid #1f222b;}
.dsdisc .dsfallback{display:flex;align-items:center;justify-content:center;font-size:20px;color:#ff6b35;}
.dsdisc img.spin{animation:dsspin 9s linear infinite;}
@keyframes dsspin{to{transform:rotate(360deg);}}
.dsdiscplay{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:26px;height:26px;border-radius:50%;background:rgba(0,0,0,.7);border:2px solid #fff;color:#fff;display:flex;align-items:center;justify-content:center;padding:0;font-size:11px;line-height:1;cursor:pointer;}
.dswave{flex:1 1 0;display:flex;align-items:center;justify-content:space-between;height:34px;cursor:pointer;position:relative;min-width:0;}
.dswave b{display:block;width:2px;min-width:2px;border-radius:2px;background:#2a2e38;transition:background .1s;}
.dswave b.fill{background:#2563eb;}
.dswavecur{position:absolute;top:-6px;bottom:-6px;width:3px;border-radius:2px;background:#fff;pointer-events:none;left:0;box-shadow:0 0 6px rgba(255,255,255,.8);}
.dstime{font-size:10.5px;color:#6b7280;min-width:34px;text-align:right;flex:0 0 auto;}
.dsrow .ins{margin-top:10px;width:100%;border:1px solid var(--line);background:transparent;color:inherit;border-radius:7px;padding:7px 8px;font-size:11.5px;cursor:pointer;}
.dsrow .ins:hover:not(:disabled){border-color:var(--brand);color:var(--brand);}
.dsrow .ins:disabled{opacity:.55;cursor:default;}
/* DS: rows and cards always fit the pane width. Without min-width:0 a long
   title's full one-line width became the row's minimum, so on a narrow pane
   the row ran off the right edge and hid the Insert button. */
.grid>*{min-width:0;max-width:100%;box-sizing:border-box}
.dsrow{min-width:0;overflow:hidden}
.dsrowtop,.dsplayer{min-width:0}
.dswave{overflow:hidden;gap:2px}
.dswave b{flex:0 1 2px;min-width:1px}
.dsrow .ins{display:block;box-sizing:border-box;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
@media (max-width:340px){
  .dsrow{padding:10px}
  .dsdisc,.dsdisc img,.dsdisc .dsfallback{width:44px;height:44px}
  .dsdiscplay{width:22px;height:22px;font-size:10px}
  .dswave{height:28px}
  .dsplayer{gap:8px}
  .dstime{min-width:28px;font-size:10px}
}
@media (max-width:250px){
  .dskind{display:none}
  .dsdisc,.dsdisc img,.dsdisc .dsfallback{width:36px;height:36px}
  .dsdiscplay{width:20px;height:20px}
  .dstime{display:none}
}
    /* DS: media tile previews (added) */
    .thumb video { width: 100%; height: 100%; object-fit: cover; display: block; }
    .dsglyph { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
      font-size: 26px; color: #ff9142; background: linear-gradient(135deg,#16233d,#0f1a2e); }
    .dsprev { width: 100%; height: 26px; margin-top: 6px; }
    .dsnote{padding:26px 14px;text-align:center;color:#8ea3c9;font-size:13px;line-height:1.5}
/* main is the only scroller; contain stops its overflow leaking to the document root */
main{contain:paint}
header{display:flex;align-items:center;gap:8px}
#ds-browser{margin-left:auto;background:rgba(255,255,255,.06);border:1px solid #24365a;color:inherit;border-radius:8px;padding:6px 10px;font-size:12px;cursor:pointer;white-space:nowrap}
#ds-browser:hover{border-color:#ff6b35;color:#ff6b35}
header{padding-right:calc(var(--pad) + 16px)}
header b{flex:0 0 auto}
#ds-browser{flex:0 0 auto;padding:5px 9px}
/* narrow pane (the user can drag it narrower than the default 320px) */
/* the small DS logo (transparent) in the header; a narrow pane shows the logo alone */
header img{width:auto;height:22px;flex:0 0 auto;border-radius:0;display:block}
@media (max-width:275px){
  header img{height:26px}
  header b{display:none}
  .acct{flex-wrap:wrap;row-gap:6px}
  .acct #ds-acct-name{flex:1 1 calc(100% - 30px)}
}
/* audio rows: two lines of title, so rows that start with the same words can be told apart */
.dsrowtop{align-items:flex-start}
.dsrowtitle{white-space:normal;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;line-height:1.3}
.dslede{font-size:11px;letter-spacing:.9px;text-transform:uppercase;color:#8ea3c9;font-weight:700;margin:2px 0 8px}
.dstiles{display:grid;grid-template-columns:repeat(2,1fr);gap:9px;padding:0 0 6px}
@media (max-width:320px){.dstiles{grid-template-columns:1fr}}
.dstile{background:#16233d;border:1px solid #24365a;border-radius:12px;overflow:hidden;cursor:pointer;padding:0;color:inherit;font:inherit;text-align:left;display:flex;flex-direction:column;transition:transform .12s,border-color .12s}
.dstile:hover{transform:translateY(-2px);border-color:#ff6b35}
.dsthumb{aspect-ratio:16/10;max-height:104px;display:grid;place-items:center}
.dsthumb svg{width:28px;height:28px;stroke:#fff;fill:none;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
.dsmeta{padding:8px 9px 10px}
.dsmeta b{display:block;font-size:12.5px;font-weight:650}
.dsmeta i{display:block;font-size:10.5px;color:#8ea3c9;font-style:normal;margin-top:2px}
.dsc1{background:linear-gradient(145deg,#ff6b35,#e0341f)}
.dsc2{background:linear-gradient(145deg,#f0a92b,#d97706)}
.dsc3{background:linear-gradient(145deg,#3b82f6,#1d4ed8)}
.dsc4{background:linear-gradient(145deg,#ec4899,#be1358)}
.dsc5{background:linear-gradient(145deg,#14b8a6,#0b7a6e)}
.dsc6{background:linear-gradient(145deg,#64748b,#3b475c)}
.dsc7{background:linear-gradient(145deg,#8b5cf6,#5b2bd4)}
.dsc8{background:linear-gradient(145deg,#22c55e,#128a3c)}
.dsthumb{position:relative;overflow:hidden}
.dsthumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0;transition:opacity .3s}
.dsthumb img.on{opacity:1}
/* Safari: give the tile picture the full tile width (Safari otherwise sized it from max-height and aspect-ratio, leaving a blank strip on the right). */
.dsthumb{width:100%;align-self:stretch;flex:0 0 auto}
.dsthumb:after{content:'';position:absolute;inset:0;opacity:0;transition:opacity .3s;background:linear-gradient(180deg,rgba(11,16,32,.10),rgba(11,16,32,.62))}
.dsthumb.hasimg:after{opacity:1}
.dsthumb svg{position:relative;z-index:2;filter:drop-shadow(0 1px 3px rgba(0,0,0,.6))}
/* DS: focus view - after a category is picked, the results get the whole
   pane; a back arrow returns to the front page exactly as it was. */
.dsfocus{display:none;align-items:center;gap:8px;padding:8px var(--pad);border-bottom:1px solid var(--line)}
.dsfocus .dsback{flex:0 0 30px;width:30px;height:30px;border-radius:50%;border:1px solid var(--line);background:var(--card);color:var(--text);display:grid;place-items:center;cursor:pointer;padding:0}
.dsfocus .dsback:hover{border-color:var(--brand);color:var(--brand)}
.dsfocus .dsback svg{width:16px;height:16px}
.dsfocus .dschip{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;border:1px solid var(--line);background:var(--card);border-radius:999px;padding:4px 12px;font-size:12px;font-weight:600}
body.dsfocused .dsfocus{display:flex}
.dsfsearch{display:none;gap:6px;padding:0 var(--pad) 8px;border-bottom:1px solid var(--line)}
body.dsfocused .dsfsearch{display:flex}
.dsfsearch input{flex:1 1 auto;min-width:0;background:var(--card);color:var(--text);border:1px solid var(--line);border-radius:8px;padding:7px 10px;font:inherit}
.dsfsearch input::placeholder{color:var(--muted)}
.dsfsearch button{flex:0 0 36px;width:36px;border:0;border-radius:8px;cursor:pointer;display:grid;place-items:center;padding:0;background:linear-gradient(90deg,var(--brand),var(--brand2));color:#fff}
.dsfsearch button svg{width:17px;height:17px}
body.dsfocused .dsfocus{border-bottom:0}
body.dsfocused #ds-acct,body.dsfocused #ds-bill,body.dsfocused #ds-mode,body.dsfocused .tools,
body.dsfocused #ds-bigwin,body.dsfocused #ds-lede-lib,body.dsfocused #ds-lede-ai,body.dsfocused #ds-tiles{display:none!important}
</style>
</head>
<body>
<script>
/* The pane runs as a cross-site iframe inside office.com, so the login
   cookie never reaches it. Every call to this site carries the token the
   big window handed us instead. */
(function () {
  var KEY = 'ds_office_token';
  window.dsOfficeToken = function () { try { return localStorage.getItem(KEY) || ''; } catch (e) { return ''; } };
  window.dsOfficeSetToken = function (t) {
    try { if (t) { localStorage.setItem(KEY, t); } else { localStorage.removeItem(KEY); } } catch (e) {}
  };
  var nativeFetch = window.fetch ? window.fetch.bind(window) : null;
  if (!nativeFetch) { return; }
  window.fetch = function (input, init) {
    try {
      var t = window.dsOfficeToken();
      var url = (typeof input === 'string') ? input : (input && input.url) || '';
      var mine = url && (url.indexOf('//') === -1 || url.indexOf(location.origin) === 0);
      if (t && mine && url.indexOf('ds_t=') === -1) {
        var next = url + (url.indexOf('?') === -1 ? '?' : '&') + 'ds_t=' + encodeURIComponent(t);
        input = (typeof input === 'string') ? next : new Request(next, input);
      }
    } catch (e) {}
    return nativeFetch(input, init);
  };
})();
</script>
  <header>
    <img src="data:image/png;base64,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" alt="DesignStager" width="72" height="104">
    <b aria-hidden="true">Design<span>stager</span></b>
<button type="button" id="ds-browser" title="Open designstager.com in your browser">Browser</button>
  </header>

  <div class="dsfocus" id="ds-focus">
    <button type="button" class="dsback" id="ds-back" aria-label="Back to all sections" title="Back"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></button>
    <span class="dschip" id="ds-focus-label">Templates</span>
  </div>
  <form class="dsfsearch" id="ds-fsearch" role="search">
    <input id="ds-fq" type="search" placeholder="Search Templates…" aria-label="Search this section" autocomplete="off">
    <button type="submit" aria-label="Search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></button>
  </form>

  <div class="acct" id="ds-acct">
    <span class="av" id="ds-acct-av">?</span>
    <span id="ds-acct-name">Checking your account…</span>
    <span class="do">
      <button class="linkbtn" type="button" id="ds-signin" data-path="/login/">Sign in</button>
      <button class="linkbtn" type="button" id="ds-signup" data-path="/register/">Sign up</button>
      <button class="linkbtn" type="button" id="ds-account" data-path="/my-account/" hidden>My account</button>
      <button class="linkbtn" type="button" id="ds-signout" data-path="/login/" hidden>Sign out</button>
    </span>
  </div>

  <div class="dsbill" id="ds-bill" hidden>
<span class="dsplan" id="ds-plan">Free</span>
<span class="dscred" id="ds-cred"></span>
<button type="button" class="dsbuy" id="ds-buy">Buy / Upgrade</button>
</div>

<div class="dsmode" id="ds-mode" role="tablist" aria-label="Section">
<button type="button" class="dsm" data-mode="ai">AI Suites</button>
<button type="button" class="dsm on" data-mode="market">Marketplace</button>
</div>

<div class="tools">
    <form class="search" id="ds-form" role="search">
      <input id="ds-q" type="search" placeholder="Search the library…" aria-label="Search the DesignStager library" autocomplete="off">
      <button type="submit">Search</button>
    </form>
    <div class="tabs" role="tablist" aria-label="Library type">
<button class="tab" role="tab" data-kind="templates" aria-selected="true">Templates</button>
<button class="tab" role="tab" data-kind="images" aria-selected="false">Images</button>
<button class="tab" role="tab" data-kind="videos" aria-selected="false">Videos</button>
<button class="tab" role="tab" data-kind="icons" aria-selected="false">Icons</button>
<button class="tab" role="tab" data-kind="audios" aria-selected="false">Audios</button>
</div>
  </div>

  <main>
<p class="dslede" id="ds-lede-ai">AI Suites</p>
<div class="dstiles" id="ds-tiles"></div>
<button type="button" class="dsbigbtn" id="ds-bigwin">Open this section in a big window</button>
<p class="dslede" id="ds-lede-lib">Marketplace</p>
    <div class="grid" id="ds-grid" aria-busy="true">
      <div class="skel"></div><div class="skel"></div><div class="skel"></div><div class="skel"></div>
    </div>
    <p class="sr" id="ds-live" role="status" aria-live="polite">Loading the DesignStager library.</p>
  </main>

  <footer>
    <span class="dot" id="ds-dot"></span>
    <span id="ds-status">Starting…</span>
    <button class="full" type="button" id="ds-open-full">Open full library</button>
  </footer>

  <div id="log" aria-hidden="true"></div>

  <script src="https://appsforoffice.microsoft.com/lib/1/hosted/office.js"></script>
  <script src="{{COMMANDS_URL}}"></script>
  <script>
  (function () {
    'use strict';

    var SITE  = '{{SITE_URL}}';
    var state = { kind:'templates', q:'', page:1, pages:1, loading:false, auth:null };

    var grid   = document.getElementById('ds-grid');
    var live   = document.getElementById('ds-live');
    var status = document.getElementById('ds-status');
    var dot    = document.getElementById('ds-dot');

    function setStatus(msg, busy) {
      status.textContent = msg;
      status.title = msg;
      dot.className = busy ? 'dot busy' : 'dot';
      live.textContent = msg;
    }
    window.DSPaneStatus = setStatus;

    /* The shell is plain HTML, so the pane is never blank while office.js
       loads: only inserting waits for Office, browsing does not. */
    var officeReady = false;
    try {
      if (window.Office && Office.onReady) {
        Office.onReady(function () { officeReady = true; setStatus('Ready'); });
      }
    } catch (e) {}
    setTimeout(function () { if (!officeReady) setStatus('Connecting to PowerPoint…', true); }, 4000);
    setTimeout(function () { if (!officeReady) setStatus('Still connecting — browsing works, inserting may not.', true); }, 12000);

    /* ------------------------------------------------------ account bar */
    var nameEl = document.getElementById('ds-acct-name');
    var avEl   = document.getElementById('ds-acct-av');
    var inBtn  = document.getElementById('ds-signin');
    var upBtn  = document.getElementById('ds-signup');
    var outBtn = document.getElementById('ds-signout');
    var acctBtn = document.getElementById('ds-account');
    var wasIn  = null;

    function applyWho(d) {
      var on = !!(d && d.loggedIn);
      var label = (d && d.name) ? String(d.name) : '';
      nameEl.textContent = on ? (label || 'Signed in') : 'Not signed in';
      avEl.textContent = on && label ? label.trim().charAt(0).toUpperCase() : '?';
      inBtn.hidden = on; upBtn.hidden = on; outBtn.hidden = !on;
      /* plan + credits belong to the signed-in account: hide them the moment it is gone */
      var billRow = document.getElementById('ds-bill');
      if (!on) { if (billRow) { billRow.hidden = true; } }
      else if (wasIn !== null && wasIn !== on && window.dsPaintBilling) { window.dsPaintBilling(); }
      if (acctBtn) { acctBtn.hidden = !on; }
      if (d) {
        if (d.signInPath) inBtn.setAttribute('data-path', d.signInPath);
        if (d.signUpPath) upBtn.setAttribute('data-path', d.signUpPath);
        if (d.signOutPath) outBtn.setAttribute('data-path', d.signOutPath);
          if (d.accountPath && acctBtn) acctBtn.setAttribute('data-path', d.accountPath);
      }
      // Sign-in state changed while the library window was open: refresh the grid.
      if (wasIn !== null && wasIn !== on) { state.page = 1; load(false); }
      wasIn = on;
    }

    /* The big window just handed us a token (or cleared it): re-check. */
    window.addEventListener('ds-token', function () { who(); });

    function who() {
      fetch('whoami', { credentials:'include', cache:'no-store' })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(applyWho)
        .catch(function () { applyWho(null); });
    }

    /* Sign out right here in the panel: the log-out link is fetched with the
       panel's own cookies (same site), so no window has to open. */
    function signOut(btn) {
      try { window.dsOfficeSetToken(''); } catch (e) {}
      var path = btn.getAttribute('data-path') || '';
      if (!path || path === '/login/') { who(); return; }
      btn.disabled = true;
      setStatus('Signing out\u2026', true);
      fetch(SITE + path, { credentials:'include', cache:'no-store', redirect:'follow' })
        .catch(function () {})
        .then(function () {
          btn.disabled = false;
          who();
          setStatus('Signed out');
        });
    }

    /* Ask first. Office add-ins cannot use window.confirm(), so the question
       is a small bar under the account row. */
    var outAsk = null;
    function askSignOut(btn) {
      if (outAsk) { outAsk.querySelector('.dsno').focus(); return; }
      outAsk = document.createElement('div');
      outAsk.className = 'dsask';
      outAsk.setAttribute('role', 'alertdialog');
      outAsk.setAttribute('aria-label', 'Confirm sign out');
      outAsk.innerHTML = '<span>Sign out of DesignStager?</span>' +
        '<button type="button" class="linkbtn dsno">Cancel</button>' +
        '<button type="button" class="linkbtn dsyes">Sign out</button>';
      var acct = document.getElementById('ds-acct');
      acct.parentNode.insertBefore(outAsk, acct.nextSibling);
      function close() { if (outAsk && outAsk.parentNode) { outAsk.parentNode.removeChild(outAsk); } outAsk = null; btn.focus(); }
      outAsk.querySelector('.dsno').addEventListener('click', close);
      outAsk.querySelector('.dsyes').addEventListener('click', function () { close(); signOut(btn); });
      outAsk.addEventListener('keydown', function (ev) { if (ev.key === 'Escape') { ev.stopPropagation(); close(); } });
      outAsk.querySelector('.dsno').focus();
    }

    document.addEventListener('click', function (e) {
      var b = e.target.closest && e.target.closest('[data-path]');
      if (!b) return;
      if (b.id === 'ds-signout') { e.preventDefault(); askSignOut(b); return; }
      openFull(b.getAttribute('data-path'));
    });

    /* ---------------------------------------------------------- library */
    function esc(s) { return String(s == null ? '' : s); }

    function skeleton(n) {
      var h = '';
      for (var i = 0; i < (n || 4); i++) h += '<div class="skel"></div>';
      grid.innerHTML = h;
      grid.setAttribute('aria-busy', 'true');
    }

    function note(title, body, btnLabel, onClick) {
      var w = document.createElement('div');
      w.className = 'note';
      var b = document.createElement('b'); b.textContent = title; w.appendChild(b);
      w.appendChild(document.createTextNode(body));
      if (btnLabel) {
        var btn = document.createElement('button');
        btn.type = 'button'; btn.textContent = btnLabel;
        btn.addEventListener('click', onClick);
        w.appendChild(btn);
      }
      return w;
    }

    function load(append) {
      if (state.loading) return;
      state.loading = true;
      if (!append) { skeleton(4); setStatus('Loading library…', true); }

      fetch('library?kind=' + encodeURIComponent(state.kind) +
            '&s=' + encodeURIComponent(state.q) +
            '&page=' + state.page + '&per_page=24',
            { credentials:'include', cache:'no-store' })
        .then(function (r) {
          if (!r.ok) throw new Error('The library returned HTTP ' + r.status + '.');
          return r.json();
        })
        .then(function (data) {
          state.pages = data.pages || 1;
          state.auth = data.auth || {};
          render(data, append);
          setStatus(state.auth.loggedIn ? 'Ready' : 'Browsing as a guest — sign in to insert');
        })
        .catch(function (err) {
          grid.setAttribute('aria-busy', 'false');
          if (!append) grid.innerHTML = '';
          var why = (err && err.message) ? err.message : 'No connection.';
          if (/failed to fetch|networkerror|load failed|network request/i.test(why)) why = 'The site could not be reached.';
          if (why.slice(-1) !== '.') why += '.';
          grid.appendChild(note(
            'Could not reach DesignStager',
            ' ' + why + ' Check your internet connection, then try again.',
            'Retry', function () { load(false); }
          ));
          setStatus('Offline or unreachable');
        })
        .then(function () { state.loading = false; });
    }

    function render(data, append) {
      var items = data.items || [];
      grid.setAttribute('aria-busy', 'false');
      if (!append) grid.innerHTML = '';
      var old = document.getElementById('ds-more');
      if (old) old.remove();

      if (!items.length && !append) {
        grid.appendChild(note(
          'Nothing found',
          state.q ? 'Nothing matches "' + esc(state.q) + '" here. Try another word or the other tab.'
                  : 'This part of the library is empty right now.',
          state.q ? 'Clear search' : null,
          function () { document.getElementById('ds-q').value = ''; var fqc = document.getElementById('ds-fq'); if (fqc) { fqc.value = ''; } state.q = ''; state.page = 1; load(false); }
        ));
        return;
      }

      items.forEach(function (it) { grid.appendChild(card(it)); });

      if (state.page < state.pages) {
        var more = document.createElement('button');
        more.id = 'ds-more'; more.type = 'button'; more.className = 'more';
        more.textContent = 'Load more';
        more.addEventListener('click', function () { more.textContent = 'Loading…'; state.page++; load(true); });
        grid.parentNode.appendChild(more);
      }
      live.textContent = items.length + ' items shown.';
    }

          /* DS: audio rows and video thumbnails, same shapes as the website (added) */
      window.DS_DISC_ART = (typeof SITE === 'string' ? SITE : '') + '/wp-content/uploads/2026/08/image-1.png';
      function dsPreviewUrl(u) { return 'preview?url=' + encodeURIComponent(u); }

      function dsClock(sec) {
        if (!isFinite(sec) || sec < 0) return '--:--';
        var m = Math.floor(sec / 60), s2 = Math.floor(sec % 60);
        return m + ':' + (s2 < 10 ? '0' : '') + s2;
      }

      /* Stable bar heights per item, so a track always draws the same wave. */
      function dsBars(seed, n) {
        var h = [], x = (parseInt(seed, 10) || 1) * 2654435761 % 2147483647;
        for (var i = 0; i < n; i++) {
          x = (x * 1103515245 + 12345) % 2147483647;
          h.push(6 + (x % 26));
        }
        return h;
      }

      var dsNowPlaying = null;

      function audioRow(it) {
        var el = document.createElement('div');
        el.className = 'dsrow';

        var top = document.createElement('div');
        top.className = 'dsrowtop';
        var kind = document.createElement('span');
        kind.className = 'dskind'; kind.textContent = 'Audio';
        var ttl = document.createElement('div');
        ttl.className = 'dsrowtitle'; ttl.textContent = it.title || 'Untitled'; ttl.title = it.title || '';
        ttl.title = it.title || '';
        var tag = document.createElement('span');
        tag.className = 'dsrowtag' + (it.tier === 'pro' ? ' pro' : '');
        tag.textContent = it.tier === 'pro' ? 'Pro' : 'Free';
        top.appendChild(kind); top.appendChild(ttl); top.appendChild(tag);
        el.appendChild(top);

        var player = document.createElement('div');
        player.className = 'dsplayer';

        var disc = document.createElement('div');
        disc.className = 'dsdisc';
        var art = document.createElement('img');
        art.alt = ''; art.src = (window.DS_DISC_ART || '');
        art.addEventListener('error', function () {
          var fb = document.createElement('div');
          fb.className = 'dsfallback'; fb.textContent = '\u266A';
          if (art.parentNode) art.parentNode.replaceChild(fb, art);
        });
        var pb = document.createElement('button');
        pb.type = 'button'; pb.className = 'dsdiscplay'; pb.textContent = '\u25B6';
        pb.setAttribute('aria-label', 'Play preview');
        disc.appendChild(art); disc.appendChild(pb);

        var wave = document.createElement('div');
        wave.className = 'dswave';
        var bars = dsBars(it.id, 46), bs = [];
        for (var i = 0; i < bars.length; i++) {
          var b = document.createElement('b');
          b.style.height = bars[i] + 'px';
          wave.appendChild(b); bs.push(b);
        }
        var cur = document.createElement('div');
        cur.className = 'dswavecur'; cur.style.display = 'none';
        wave.appendChild(cur);

        var time = document.createElement('span');
        time.className = 'dstime'; time.textContent = '--:--';

        player.appendChild(disc); player.appendChild(wave); player.appendChild(time);
        el.appendChild(player);

        var au = null;
        function paint() {
          var d = au && au.duration;
          var p = (au && isFinite(d) && d > 0) ? (au.currentTime / d) : 0;
          for (var k = 0; k < bs.length; k++) {
            bs[k].className = (k / bs.length) <= p ? 'fill' : '';
          }
          cur.style.display = p > 0 ? 'block' : 'none';
          cur.style.left = (p * 100) + '%';
          time.textContent = au ? dsClock(au.currentTime > 0 ? au.currentTime : d) : '--:--';
        }
        function build() {
          if (au) return au;
          au = new Audio();
          au.preload = 'metadata';
          au.src = dsPreviewUrl(it.insert);
          au.addEventListener('loadedmetadata', paint);
          au.addEventListener('timeupdate', paint);
          au.addEventListener('ended', function () { pb.textContent = '\u25B6'; art.classList.remove('spin'); paint(); });
          au.addEventListener('error', function () { time.textContent = '--:--'; setStatus('That preview could not be played.'); });
          return au;
        }
        pb.addEventListener('click', function () {
          build();
          if (au.paused) {
            if (dsNowPlaying && dsNowPlaying !== au) { try { dsNowPlaying.pause(); } catch (e) {} }
            dsNowPlaying = au;
            au.play().then(function () { pb.textContent = '\u2016'; art.classList.add('spin'); })
                     .catch(function () { setStatus('That preview could not be played.'); });
          } else {
            au.pause(); pb.textContent = '\u25B6'; art.classList.remove('spin');
          }
        });
        wave.addEventListener('click', function (ev) {
          build();
          var r = wave.getBoundingClientRect();
          var p = Math.min(1, Math.max(0, (ev.clientX - r.left) / r.width));
          if (isFinite(au.duration) && au.duration > 0) { au.currentTime = p * au.duration; paint(); }
        });

        var btn = document.createElement('button');
        btn.className = 'ins'; btn.type = 'button';
        if (!state.auth || !state.auth.loggedIn) {
          btn.textContent = 'Sign in to insert';
          btn.addEventListener('click', function () { openFull(inBtn.getAttribute('data-path')); });
        } else if (it.locked) {
          btn.textContent = 'Not in your plan'; btn.disabled = true;
        } else {
          btn.textContent = 'Insert audio';
          btn.addEventListener('click', function () { doInsert(it, btn); });
        }
        el.appendChild(btn);
        return el;
      }
      function card(it) {
        if (it.kind === 'audios') { return audioRow(it); }
      var el = document.createElement('div');
      el.className = 'card';

      var t = document.createElement('div');
      t.className = 'thumb';
      if (it.thumb) {
        var img = document.createElement('img');
        img.loading = 'lazy'; img.alt = it.title || ''; img.src = it.thumb;
        /* The first view of an image makes its small thumbnail on the server;
           a busy server can refuse a few at once, so try again once. */
        img.addEventListener('error', function () {
          if (img.dsRetried) return;
          img.dsRetried = true;
          setTimeout(function () { img.src = it.thumb + (it.thumb.indexOf('?') < 0 ? '?' : '&') + 'r=1'; }, 1000 + Math.random() * 3000);
        });
        t.appendChild(img);
      }
      /* DS: media previews for videos / audios (added) */
      else if (it.kind === 'videos' && it.insert) {
        /* Same as the marketplace card: the clip itself, parked on its first
           frame, playing on hover. Streamed through the preview route, so it
           is not a download. */
        var vd = document.createElement('video');
        vd.src = 'preview?url=' + encodeURIComponent(it.insert) + '#t=0.1';
        vd.muted = true; vd.loop = true; vd.playsInline = true; vd.preload = 'metadata';
        vd.addEventListener('mouseenter', function () { try { vd.play(); } catch (e) {} });
        vd.addEventListener('mouseleave', function () { try { vd.pause(); vd.currentTime = 0.1; } catch (e) {} });
        vd.addEventListener('error', function () {
          /* A busy server can refuse a few previews at once; try once more. */
          if (!vd.dsRetried) {
            vd.dsRetried = true;
            setTimeout(function () { vd.src = 'preview?url=' + encodeURIComponent(it.insert) + '#t=0.1'; vd.load(); }, 800 + Math.random() * 2200);
            return;
          }
          var g = document.createElement('div');
          g.className = 'dsglyph'; g.textContent = '\u25B6';
          if (vd.parentNode) vd.parentNode.replaceChild(g, vd);
        });
        t.appendChild(vd);
      }
      var tier = document.createElement('span');
      tier.className = 'tier' + (it.tier === 'pro' ? ' pro' : '');
      tier.textContent = it.tier === 'pro' ? 'Pro' : 'Free';
      t.appendChild(tier);
      el.appendChild(t);

      var meta = document.createElement('div');
      meta.className = 'meta';
      var name = document.createElement('div');
      name.className = 'name';
      name.textContent = it.title || 'Untitled';
      meta.appendChild(name);

      /* DS: listen before you insert - streams through the preview route,
         which does not count as a download. */
      if (it.kind === 'audios' && it.insert) {
        var ap = document.createElement('audio');
        ap.className = 'dsprev';
        ap.controls = true;
        ap.preload = 'none';
        ap.src = 'preview?url=' + encodeURIComponent(it.insert);
        ap.addEventListener('error', function () { ap.style.display = 'none'; });
        meta.appendChild(ap);
      }

      

      var btn = document.createElement('button');
      btn.className = 'ins';
      btn.type = 'button';

      if (!state.auth || !state.auth.loggedIn) {
        btn.textContent = 'Sign in to insert';
        btn.addEventListener('click', function () { openFull(inBtn.getAttribute('data-path')); });
      } else if (it.locked) {
        btn.textContent = 'Not in your plan';
        btn.disabled = true;
        btn.title = 'This item is part of a higher DesignStager plan.';
      } else {
        var dsIsDeck = /\.pptx?$/i.test(String(it.insert || '').split('?')[0]);
        btn.textContent = (it.kind === 'templates' || dsIsDeck) ? 'Insert slides' : (it.kind === 'videos' ? 'Insert video' : (it.kind === 'audios' ? 'Insert audio' : 'Insert picture'));
        btn.addEventListener('click', function () { doInsert(it, btn); });
      }
      meta.appendChild(btn);
      el.appendChild(meta);
      return el;
    }

    /* A message box inside the pane (alert() does not show in every PowerPoint). */
    function dsPaneNotice(title, text) {
      var old = document.getElementById('ds-pane-notice');
      if (old && old.parentNode) old.parentNode.removeChild(old);
      function el(tag, css, txt) { var e = document.createElement(tag); e.style.cssText = css; if (txt) e.textContent = txt; return e; }
      var wrap = el('div', 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:2147483647;display:flex;align-items:center;justify-content:center;padding:14px;background:rgba(4,7,13,.7)');
      wrap.id = 'ds-pane-notice';
      wrap.setAttribute('role', 'alertdialog');
      wrap.setAttribute('aria-modal', 'true');
      wrap.setAttribute('aria-labelledby', 'ds-pane-notice-t');
      wrap.setAttribute('aria-describedby', 'ds-pane-notice-d');
      var card = el('div', 'box-sizing:border-box;width:100%;max-width:340px;background:#141a2e;color:#eef1f8;border:1px solid #2f3a5c;border-radius:12px;padding:18px 16px 14px;box-shadow:0 18px 50px rgba(0,0,0,.55);text-align:left');
      var h = el('div', 'font-weight:700;font-size:15px;line-height:1.35;margin:0 0 8px', title);
      h.id = 'ds-pane-notice-t';
      var d = el('div', 'font-size:13px;line-height:1.55;color:#c9d1e3;word-break:break-word', text);
      d.id = 'ds-pane-notice-d';
      var row = el('div', 'display:flex;justify-content:flex-end;margin-top:14px');
      var ok = el('button', 'font:600 13px/1 inherit;padding:9px 18px;border-radius:8px;cursor:pointer;background:#f15a29;color:#fff;border:1px solid #f15a29', 'OK');
      ok.type = 'button';
      function close() { if (wrap.parentNode) wrap.parentNode.removeChild(wrap); }
      ok.addEventListener('click', close);
      wrap.addEventListener('click', function (e) { if (e.target === wrap) close(); });
      wrap.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
      row.appendChild(ok);
      card.appendChild(h); card.appendChild(d); card.appendChild(row);
      wrap.appendChild(card);
      document.body.appendChild(wrap);
      try { ok.focus(); } catch (e) {}
    }
    window.dsPaneNotice = dsPaneNotice;

    function doInsert(it, btn) {
      if (!window.DSAddin || !DSAddin.insertFromUrl) {
        setStatus('PowerPoint is not ready yet — try again in a moment.', true);
        return;
      }
      var label = btn.textContent;
      btn.disabled = true;
      btn.textContent = 'Inserting…';
      setStatus('Inserting ' + (it.title || 'item') + '…', true);

      (function () {
        /* DS: audio and video need a slide wrapper, not image coercion (added) */
        if (it.kind === 'videos' || it.kind === 'audios') {
          if (DSAddin.insertMedia) return DSAddin.insertMedia(it.insert, it.kind, it.title, it.thumb);
          return Promise.reject(new Error('Inserting audio and video is being set up — you can still preview and download from the browser.'));
        }
        return DSAddin.insertFromUrl(it.insert, it.kind === 'templates' ? 'pptx' : 'image');
      })()
        .then(function () {
          btn.textContent = 'Inserted ✓';
          setStatus('Inserted ' + (it.title || 'item'));
          setTimeout(function () { btn.textContent = label; btn.disabled = false; }, 2500);
        })
        .catch(function (err) {
          /* A plan limit is not a failure: say so in a message, and do not
             invite another try that cannot work. */
          var lim = err && err.dsLimit;
          if (lim) {
            var head = lim === 'plan' ? 'Not included in your plan' : (lim === 'month' ? 'Monthly download limit reached' : 'Daily download limit reached');
            btn.textContent = lim === 'plan' ? 'Not in your plan' : 'Limit reached';
            btn.disabled = false;
            setStatus(head + '.');
            dsPaneNotice(head, err.message || '');
            return;
          }
          btn.textContent = 'Try again';
          btn.disabled = false;
          setStatus((err && err.message) ? err.message : 'Insert failed.');
        });
    }

    function openFull(path) {
      if (window.DSAddin && DSAddin.openDialog) DSAddin.openDialog(path || '');
      else window.open(SITE + (path || '/'), '_blank');
    }

    /* Search inside the section that is open (used by the full-panel view). */
    window.dsPaneSearch = function (q) {
      state.q = String(q || '').trim();
      state.page = 1;
      var fq = document.getElementById('ds-q');
      if (fq) { fq.value = state.q; }
      load(false);
    };
    window.dsPaneQuery = function () { return state.q; };

    document.getElementById('ds-form').addEventListener('submit', function (e) {
      e.preventDefault();
      state.q = document.getElementById('ds-q').value.trim();
      state.page = 1;
      load(false);
    });

    Array.prototype.forEach.call(document.querySelectorAll('.tab'), function (tab) {
      tab.addEventListener('click', function () {
        Array.prototype.forEach.call(document.querySelectorAll('.tab'), function (t) {
          t.setAttribute('aria-selected', String(t === tab));
        });
        state.kind = tab.getAttribute('data-kind');
        state.page = 1;
        /* Each section starts with a clear search; searching happens inside it. */
        state.q = '';
        var fq0 = document.getElementById('ds-q'); if (fq0) { fq0.value = ''; }
        load(false);
      });
    });

    document.getElementById('ds-open-full').addEventListener('click', function () { openFull(''); });

    who();
    load(false);
    window.addEventListener('focus', who);
    window.setInterval(who, 60000);
  })();
  
/* --- DesignStager launcher tiles ------------------------------------- */
/* openFull() and SITE live inside the page's own IIFE and are NOT global, */
/* so this block talks to window.DSAddin directly.                        */
(function () {
  var SITE_URL = '{{SITE_URL}}';

  function dsOpen(path) {
    if (window.DSAddin && DSAddin.openDialog) { DSAddin.openDialog(path || '/'); return; }
    var u = SITE_URL + (path || '/');
    window.open(u + (u.indexOf('?') === -1 ? '?' : '&') + 'ds_office=1', '_blank');
  }
  window.dsOpenSection = dsOpen;

  /* Tile pictures come from Settings > DesignStager Office > AI Suites tile
     pictures; tiles left empty there get an automatic marketplace picture. */
  var TILE_THUMBS = {{TILE_THUMBS}};
  var TILES = [
    { k:'presentation', p:'/ai-presentation/', c:'dsc1', t:'AI Presentation', d:'Prompt to full deck' },
    { k:'matcher', p:'/ai-presentation/browse-templates/', c:'dsc7', t:'Template Matcher', d:'Find the right layout' },
    { k:'marketplace', p:'/marketplace/', c:'dsc2', t:'Marketplace', d:'Templates and graphics' },
    { k:'image', p:'/ai-studio/', c:'dsc3', t:'AI Image', d:'Generate visuals' },
    { k:'video', p:'/ai-video/', c:'dsc4', t:'AI Video', d:'Clips and motion' },
    { k:'audio', p:'/ai-audio/', c:'dsc5', t:'AI Audio', d:'Voice and soundtrack' },
    { k:'generations', p:'/my-creations/', c:'dsc6', t:'My Generations', d:'Everything you made' },
    { k:'account', p:'/my-account/', c:'dsc6', t:'My Account', d:'Your plan and profile' }
  ];

  var g = document.getElementById('ds-tiles');
  if (g) {
    TILES.forEach(function (x) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'dstile';
      b.setAttribute('data-k', x.k);
      b.innerHTML = '<span class="dsthumb ' + x.c + '"></span>' +
                    '<span class="dsmeta"><b>' + x.t + '</b><i>' + x.d + '</i></span>';
      b.addEventListener('click', function () { dsOpen(x.p); });
      g.appendChild(b);
    });
  }

  /* window.open is blocked inside an Office task pane; openBrowserWindow is
     the supported way to reach the user's real browser. */
  function dsExternal(url) {
    try {
      if (window.Office && Office.context && Office.context.ui && Office.context.ui.openBrowserWindow) {
        Office.context.ui.openBrowserWindow(url);
        return true;
      }
    } catch (e) {}
    try { var w = window.open(url, '_blank'); if (w) { return true; } } catch (e) {}
    try { if (window.DSAddin && DSAddin.openDialog) { DSAddin.openDialog(url.replace(SITE_URL, '') || '/'); return true; } } catch (e) {}
    return false;
  }
  window.dsExternal = dsExternal;

  var hb = document.getElementById('ds-browser');
  if (hb) { hb.addEventListener('click', function () { dsExternal(SITE_URL + '/'); }); }

  /* real website thumbnails on the tiles */
  function paint(tile, url) {
    if (!tile || !url) { return; }
    var holder = tile.querySelector('.dsthumb');
    if (!holder) { return; }
    var img = document.createElement('img');
    img.alt = '';
    img.addEventListener('load', function () { img.className = 'on'; holder.className += ' hasimg'; });
    img.addEventListener('error', function () { if (img.parentNode) { img.parentNode.removeChild(img); } });
    img.src = url;
    holder.insertBefore(img, holder.firstChild);
  }
  function grab(kind) {
    return fetch('library' + String.fromCharCode(63) + 'kind=' + kind + '&page=1', { credentials: 'include' })
      .then(function (r) { return r.json(); })
      .then(function (j) { return (j && j.items ? j.items : []).map(function (x) { return x.thumb; }).filter(Boolean); })
      .catch(function () { return []; });
  }
  var tiles = document.querySelectorAll('.dstile');
  if (tiles.length) {
    var need = [];
    Array.prototype.forEach.call(tiles, function (tile) {
      var k = tile.getAttribute('data-k');
      if (TILE_THUMBS && TILE_THUMBS[k]) { paint(tile, TILE_THUMBS[k]); } else { need.push(tile); }
    });
    if (need.length) {
      Promise.all([grab('templates'), grab('images')]).then(function (res) {
        var tpl = res[0] || [], img = res[1] || [], a = 0, b = 0;
        need.forEach(function (tile) {
          var k = tile.getAttribute('data-k');
          var fromTpl = (k === 'presentation' || k === 'matcher' || k === 'marketplace');
          paint(tile, fromTpl ? (tpl[a++] || img[b++]) : (img[b++] || tpl[a++]));
        });
      });
    }
  }
})();

/* --- Marketplace / AI Suites switching -------------------------------- */
(function () {
  var tools   = document.querySelector('.tools');
  var tiles   = document.getElementById('ds-tiles');
  var ledeAi  = document.getElementById('ds-lede-ai');
  var ledeLib = document.getElementById('ds-lede-lib');
  var grid    = document.getElementById('ds-grid');
  var more    = document.getElementById('ds-more');
  var live    = document.getElementById('ds-live');
  if (!tools || !tiles || !grid) { return; }

  function show(el, on) { if (el) { el.style.display = on ? '' : 'none'; } }

  function setMode(mode) {
    var market = (mode !== 'ai');
    show(tools, market);
    show(ledeLib, market);
    show(grid, market);
    show(more, market);
    show(live, market);
    show(ledeAi, !market);
    show(tiles, !market);
    Array.prototype.forEach.call(document.querySelectorAll('.dsm'), function (b) {
      b.className = 'dsm' + (b.getAttribute('data-mode') === (market ? 'market' : 'ai') ? ' on' : '');
    });
    try { sessionStorage.setItem('ds_mode', market ? 'market' : 'ai'); } catch (e) {}
  }

  Array.prototype.forEach.call(document.querySelectorAll('.dsm'), function (b) {
    b.addEventListener('click', function () { setMode(b.getAttribute('data-mode')); });
  });

  /* The library endpoint only serves templates and images. For the other
     kinds, open the marketplace page filtered the way the website does. */
  var SOON = {
    videos: { label: 'Videos', path: '/marketplace/?type=visual&cat=videos' },
    audios: { label: 'Audios', path: '/marketplace/?type=audio' },
    icons:  { label: 'Icons',  path: '/marketplace/?type=visual&cat=icons' }
  };
  Array.prototype.forEach.call(document.querySelectorAll('.tab[data-soon="1"]'), function (b) {
    b.addEventListener('click', function (ev) {
      ev.preventDefault();
      ev.stopImmediatePropagation();
      var k = SOON[b.getAttribute('data-kind')] || { label: '', path: '' };
      Array.prototype.forEach.call(document.querySelectorAll('.tab'), function (x) {
        x.setAttribute('aria-selected', x === b ? 'true' : 'false');
      });
      if (more) { more.hidden = true; }
      if (k.path) {
        grid.innerHTML = '<p class="dsnote">Opening ' + k.label + ' in the full window\u2026</p>';
        if (window.dsOpenSection) { window.dsOpenSection(k.path); }
      } else {
        grid.innerHTML = '<p class="dsnote">' + k.label + ' are not set up on the website yet.<br>Create the category and tell me its slug, and this tab will fill.</p>';
      }
    }, true);
  });

  var saved = 'market';
  try { saved = sessionStorage.getItem('ds_mode') || 'market'; } catch (e) {}
  setMode(saved);
})();

/* --- plan, credits, buy, and open-in-big-window ----------------------- */
(function () {
  var bill = document.getElementById('ds-bill');
  var planEl = document.getElementById('ds-plan');
  var credEl = document.getElementById('ds-cred');
  var buyEl = document.getElementById('ds-buy');
  var bigEl = document.getElementById('ds-bigwin');

  function go(path) {
    /* Prefer the PowerPoint window; fall back to the real browser. */
    if (window.DSAddin && DSAddin.openDialog) { DSAddin.openDialog(path); return; }
    if (window.dsExternal) { dsExternal(('{{SITE_URL}}') + path); return; }
    window.open(('{{SITE_URL}}') + path, '_blank');
  }

  function paintBilling() {
    fetch('whoami', { credentials: 'include', cache: 'no-store' })
      .then(function (r) { return r.json(); })
      .then(function (w) {
        if (!w || !w.loggedIn || !w.billing || w.billing.error) {
          if (bill) { bill.hidden = true; }
          return;
        }
        var b = w.billing;
        if (planEl) { planEl.textContent = b.plan_label || b.plan || 'Free'; }
        if (credEl) {
          /* Say what the date really means. It used to read "renews" always,
             even after the date had passed or the plan was cancelled. */
          var endWord = 'renews';
          if (b.end_date) {
            var st = String(b.status || 'active').toLowerCase();
            var isPast = false;
            if (/^\d{4}-\d{2}-\d{2}/.test(String(b.end_date))) {
              var now = new Date();
              var today = now.getFullYear() + '-' + ('0' + (now.getMonth() + 1)).slice(-2) + '-' + ('0' + now.getDate()).slice(-2);
              isPast = String(b.end_date).slice(0, 10) < today;
            }
            if (st === 'expired' || st === 'refunded') { endWord = 'expired'; }
            else if (st === 'cancelled') { endWord = isPast ? 'ended' : 'ends'; }
            else if (isPast) { endWord = 'renewal due since'; }
          }
          credEl.innerHTML = (b.credits === undefined ? '' : b.credits) + ' <span>credits</span>' +
            (b.end_date ? ' <span>&middot; ' + endWord + ' ' + b.end_date + '</span>' : '');
        }
        if (bill) { bill.hidden = false; }
      })
      .catch(function () { if (bill) { bill.hidden = true; } });
  }

  if (buyEl) { buyEl.addEventListener('click', function () { go('/pricing/'); }); }

  if (bigEl) {
    bigEl.addEventListener('click', function () {
      var sel = document.querySelector('.tab[aria-selected="true"]');
      var kind = sel ? sel.getAttribute('data-kind') : 'templates';
      var MAP = {
        templates: '/marketplace/',
        images:    '/marketplace/?type=visual&cat=images',
        videos:    '/marketplace/?type=visual&cat=videos',
        audios:    '/marketplace/?type=audio',
        icons:     '/marketplace/?type=visual&cat=icons'
      };
      go(MAP[kind] || '/marketplace/');
    });
  }

  paintBilling();
  window.dsPaintBilling = paintBilling;
  window.addEventListener('focus', paintBilling);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) { paintBilling(); } });
})();

/* --- focus view: picked category fills the pane, back returns ---------- */
(function () {
  var body  = document.body;
  var label = document.getElementById('ds-focus-label');
  var back  = document.getElementById('ds-back');
  var main  = document.querySelector('main');
  var frontScroll = 0;
  if (!label || !back) { return; }

  var fform = document.getElementById('ds-fsearch');
  var fq = document.getElementById('ds-fq');
  function enter(text) {
    if (!body.classList.contains('dsfocused')) { frontScroll = main ? main.scrollTop : 0; }
    label.textContent = text;
    if (fq) {
      fq.placeholder = 'Search ' + kindLabel() + '\u2026';
      fq.value = window.dsPaneQuery ? window.dsPaneQuery() : '';
    }
    body.classList.add('dsfocused');
    if (main) { main.scrollTop = 0; }
    try { back.focus({ preventScroll: true }); } catch (e) {}
  }
  function leave() {
    body.classList.remove('dsfocused');
    if (main) { main.scrollTop = frontScroll; }
  }
  function kindLabel() {
    var t = document.querySelector('.tab[aria-selected="true"]');
    return t ? t.textContent.trim() : 'Marketplace';
  }

  /* Bubbling listener: runs after the tab's own handler has started loading. */
  Array.prototype.forEach.call(document.querySelectorAll('.tab'), function (tab) {
    tab.addEventListener('click', function () { enter(tab.textContent.trim()); });
  });
  var form = document.getElementById('ds-form');
  if (form) {
    form.addEventListener('submit', function () {
      enter(kindLabel());
    });
  }
  back.addEventListener('click', leave);
  if (fform) {
    fform.addEventListener('submit', function (e) {
      e.preventDefault();
      if (window.dsPaneSearch) { window.dsPaneSearch(fq.value); }
    });
  }
  document.addEventListener('keydown', function (e) {
    if ((e.key === 'Escape' || e.key === 'Backspace' && !/^(INPUT|TEXTAREA)$/.test((e.target || {}).tagName || '')) && body.classList.contains('dsfocused')) {
      e.preventDefault(); leave();
    }
  });
})();
</script>
</body>
</html>
DSASSET
 );
$a['commands.js'] = array( 'application/javascript; charset=utf-8', <<<'DSASSET'
/*
 * DesignStager PowerPoint add-in — runs inside PowerPoint (shared runtime).
 * Opens the website in a large dialog window and inserts whatever the
 * website sends back (slides, pictures, SVG) into the presentation.
 */
(function () {
  'use strict';

  var params = new URLSearchParams(location.search);
  var SITE = (params.get('site') || location.origin).replace(/\/+$/, '');
  // Set by PHP so the pane's own fetches hit this build's asset URLs.
  var ADDIN_VERSION = '{{ADDIN_VERSION}}';
  var SITE_ORIGIN = new URL(SITE).origin;

  var config = {
    startUrl: SITE + '/?ds_office=1',
    dialogWidth: 95,
    dialogHeight: 95,
    formatting: 'KeepSourceFormatting'
  };
  var configLoaded = fetch(SITE + '/?ds_office_asset=config.json', { credentials: 'omit', cache: 'no-store' })
    .then(function (r) { return r.ok ? r.json() : null; })
    .then(function (c) { if (c && c.startUrl) Object.assign(config, c); })
    .catch(function () { /* keep defaults */ });

  var dialog = null;
  var incoming = {};

  function log(msg) {
    try {
      var el = document.getElementById('log');
      if (el) el.textContent = new Date().toLocaleTimeString() + '  ' + msg + '\n' + el.textContent;
    } catch (e) {}
    console.log('[DesignStager] ' + msg);
  }

  function reply(obj) {
    try { if (dialog && typeof dialog.messageChild === 'function') dialog.messageChild(JSON.stringify(obj)); } catch (e) {}
    try { if (dsIn && dsIn.fr && dsIn.fr.contentWindow) dsIn.fr.contentWindow.postMessage(JSON.stringify(obj), SITE_ORIGIN); } catch (e) {}
  }

  /* -------------------------------------------------- open window */
  // Must run synchronously inside the click, otherwise PowerPoint on the web
  // treats it as a pop-up and asks "wants to display a new window – Allow / Ignore".
  function dialogUrl(path) {
    if (!path) return config.startUrl;
    var u = SITE + (path.charAt(0) === '/' ? path : '/' + path);
    return u + (u.indexOf('?') === -1 ? '?' : '&') + 'ds_office=1';
  }

  /* Safari (and other browsers) only let a page open a new window straight after a click. When the large window
   has to open without a click (for example right after signing in), PowerPoint first shows its own small
   "Allow" prompt; that click opens the window, so the browser does not block it. */
var dsGestT = 0;
['pointerdown', 'click', 'keydown', 'touchend'].forEach(function (t) { try { document.addEventListener(t, function () { dsGestT = Date.now(); }, true); } catch (e) {} });
function dsNeedPrompt() {
  var web = false; try { web = !!(window.Office && Office.context && Office.PlatformType && Office.context.platform === Office.PlatformType.OfficeOnline); } catch (e) {}
  if (!web) return false;
  try { if (navigator.userActivation && typeof navigator.userActivation.isActive === 'boolean') return !navigator.userActivation.isActive; } catch (e) {}
  return Date.now() - dsGestT > 1500;
}
function openDialog(path) {
    /* No PowerPoint here (this page was opened in a normal window): open the tool in this same window. */
    if (dsNoPane()) { location.href = dialogUrl(path); return; }
    if (dialog) { try { dialog.close(); } catch (e) {} dialog = null; }
    setStatus('Opening DesignStager…');
    Office.context.ui.displayDialogAsync(
      dialogUrl(path),
      { width: config.dialogWidth, height: config.dialogHeight, displayInIframe: false, promptBeforeOpen: dsNeedPrompt() },
      function (res) {
        if (res.status === Office.AsyncResultStatus.Failed) {
          var c = res.error.code;
          if (c === 12007) setStatus('The DesignStager window is already open.');
          else if (c === 12009) setStatus('The window did not open. Click the button again, or allow pop-ups for PowerPoint in your browser.');
          else setStatus('Could not open: ' + res.error.message + ' (' + c + ')');
          log('Open failed: ' + res.error.message + ' (' + c + ')');
          return;
        }
        dialog = res.value;
        dialog.addEventHandler(Office.EventType.DialogMessageReceived, onMessage);
        dialog.addEventHandler(Office.EventType.DialogEventReceived, function (ev) {
          dialog = null; incoming = {};
          setStatus('Ready');
          if (ev.error !== 12006) log('Window closed (' + ev.error + ').');
        });
        setStatus('Library open — click ⇤ on any item to insert it.');
        log('Library opened.');
      }
    );
  }

  function setStatus(msg) {
    try { var el = document.getElementById('ds-status'); if (el) el.textContent = msg; } catch (e) {}
  }

  /* --------------------------------------------- receive the file */
  function onMessage(arg) {
    if (arg.origin && arg.origin !== SITE_ORIGIN) return; // only trust our own website
    var m;
    try { m = JSON.parse(arg.message); } catch (e) { return; }

    if (m.t !== 'hello' && m.t !== 'token' && m.t !== 'answer' && m.t !== 'signin') dsAskSrc = arg; // where a question about this insert goes
    switch (m.t) {
      case 'hello':
          reply({ t: 'hello-ack' });
          break;
      case 'answer':
        if (dsAskWait[m.id]) { var dsAf = dsAskWait[m.id]; delete dsAskWait[m.id]; dsAf(String(m.k || '')); }
        break;
      case 'signin':
        /* "Continue with Google / Microsoft" in the side panel: those sign-in pages refuse to open inside
           another page, so the sign-in runs in the large PowerPoint window (the user clicked the button). */
        if (arg && arg.inline) {
          var sp = String(m.path || '');
          if (sp.charAt(0) === '/' && sp.charAt(1) !== '/') { dsIn.signin = sp; openDialog(sp); }
        }
        break;
      case 'open':
        /* "Open in browser" on a page in the PowerPoint window. Our own site only. */
        try {
          var ou = String(m.url || '');
          if (ou.indexOf(SITE_ORIGIN + '/') !== 0) break;
          if (Office.context && Office.context.ui && Office.context.ui.openBrowserWindow) Office.context.ui.openBrowserWindow(ou);
          else window.open(ou, '_blank');
        } catch (e) { log('Open in browser failed: ' + ((e && e.message) || e)); }
        break;
        case 'token':
          /* The big window is signed in (or just signed out) - follow it. */
          if (arg && arg.inline && !m.token && window.dsOfficeToken && window.dsOfficeToken() && dsIsWeb()) { dsInlineNeedWindow(); break; }
          try { window.dsOfficeSetToken(m.token || ''); } catch (e) {}
          try { window.dispatchEvent(new Event('ds-token')); } catch (e) {}
          if (m.token && !(arg && arg.inline) && dsIn.signin) { dsInlineAfterSignin(); }
          break;
      case 'begin':
        incoming[m.id] = { kind: m.kind, name: m.name, total: m.total, parts: new Array(m.total), got: 0 };
        break;
      case 'chunk':
        var f = incoming[m.id];
        if (f && f.parts[m.i] === undefined) { f.parts[m.i] = m.d; f.got++; }
        break;
      case 'end':
        var file = incoming[m.id];
        delete incoming[m.id];
        if (!file) return;
        if (file.got !== file.total) { reply({ t: 'done', id: m.id, ok: false, error: 'Transfer incomplete, please try again.' }); return; }
        log('Inserting ' + file.name + ' …');
        insert(file.kind, file.parts.join(''))
          .then(function () { log('Inserted ' + file.name); setStatus('Inserted ' + file.name + ' ✓'); reply({ t: 'done', id: m.id, ok: true }); })
          .catch(function (err) {
            var msg = (err && err.message) || String(err);
            log('Failed: ' + msg);
            reply({ t: 'done', id: m.id, ok: false, error: msg });
          });
        break;
    }
  }

  /* ------------------------------------------- insert into slides */
  function setSelectedData(data, coercionType) {
    return new Promise(function (resolve, reject) {
      Office.context.document.setSelectedDataAsync(data, { coercionType: coercionType }, function (r) {
        if (r.status === Office.AsyncResultStatus.Succeeded) resolve();
        else reject(new Error(r.error.message + ' — select a slide (not the slide sorter) and try again.'));
      });
    });
  }

  /* Add-in only: the "Don't love this presentation?" offer slide that the site
     appends to generated decks is left out when slides go into PowerPoint.
     Website downloads are untouched. Returns the sourceSlideIds to keep, or
     null when there is nothing to leave out (or the deck cannot be read). */
  async function dsOfferSlideKeep(base64) {
    if (typeof DecompressionStream === 'undefined' || typeof Response === 'undefined') return null;
    var bin = atob(base64), bytes = new Uint8Array(bin.length), n;
    for (n = 0; n < bin.length; n++) bytes[n] = bin.charCodeAt(n);
    var dv = new DataView(bytes.buffer), eocd = -1;
    for (n = bytes.length - 22; n >= 0 && n >= bytes.length - 66000; n--) {
      if (dv.getUint32(n, true) === 0x06054b50) { eocd = n; break; }
    }
    if (eocd < 0) return null;
    var count = dv.getUint16(eocd + 10, true), p = dv.getUint32(eocd + 16, true), files = {};
    var dec = new TextDecoder('utf-8');
    for (n = 0; n < count; n++) {
      if (p + 46 > bytes.length || dv.getUint32(p, true) !== 0x02014b50) return null;
      var nameLen = dv.getUint16(p + 28, true);
      files[dec.decode(bytes.subarray(p + 46, p + 46 + nameLen))] = {
        method: dv.getUint16(p + 10, true), size: dv.getUint32(p + 20, true), at: dv.getUint32(p + 42, true)
      };
      p += 46 + nameLen + dv.getUint16(p + 30, true) + dv.getUint16(p + 32, true);
    }
    async function text(name) {
      var f = files[name];
      if (!f || dv.getUint32(f.at, true) !== 0x04034b50) return null;
      var start = f.at + 30 + dv.getUint16(f.at + 26, true) + dv.getUint16(f.at + 28, true);
      var raw = bytes.subarray(start, start + f.size);
      if (f.method === 0) return dec.decode(raw);
      if (f.method !== 8) return null;
      var out = new Blob([raw]).stream().pipeThrough(new DecompressionStream('deflate-raw'));
      return dec.decode(await new Response(out).arrayBuffer());
    }
    function attr(tag, name) {
      var m = new RegExp('(?:^|\\s)' + name + '="([^"]*)"').exec(tag);
      return m ? m[1] : '';
    }
    var pres = await text('ppt/presentation.xml');
    var rels = await text('ppt/_rels/presentation.xml.rels');
    if (!pres || !rels) return null;
    var target = {}, m, re = /<Relationship\b[^>]*>/g;
    while ((m = re.exec(rels))) target[attr(m[0], 'Id')] = attr(m[0], 'Target');
    var ids = [], keep = [];
    re = /<p:sldId\b[^>]*>/g;
    while ((m = re.exec(pres))) ids.push({ id: attr(m[0], 'id'), rel: attr(m[0], 'r:id') });
    if (ids.length < 2) return null;
    for (n = 0; n < ids.length; n++) {
      var t = target[ids[n].rel] || '';
      t = t.charAt(0) === '/' ? t.slice(1) : 'ppt/' + t;
      var xml = await text(t), words = '';
      if (xml) {
        re = /<a:t>([^<]*)<\/a:t>/g;
        while ((m = re.exec(xml))) words += ' ' + m[1];
        words = words.replace(/\s+/g, ' ').toLowerCase();
      }
      var offer = /love this ?presentation/.test(words) && /customi[sz]e/.test(words);
      if (!offer) keep.push(ids[n].id + '#');
    }
    if (!keep.length || keep.length === ids.length) return null;
    return keep;
  }

  /* ---------- slide size: a 9:16 deck going into a 16:9 presentation (or the other way round) ----------
     The deck's size is read from its ppt/presentation.xml. If it differs from the open presentation,
     the page that sent the slides asks the user (change the presentation / keep it); an empty
     presentation is changed without asking. Needs PowerPointApi 1.10 (presentation.pageSetup);
     older PowerPoint inserts exactly as before. */
  var dsAskSrc = null, dsAskN = 0, dsAskWait = {};
  function dsAsk(text, choices) {
    return new Promise(function (resolve) {
      var id = 'q' + (++dsAskN), msg = JSON.stringify({ t: 'ask', id: id, text: text, choices: choices }), sent = false;
      try {
        if (dsAskSrc && dsAskSrc.inline && dsIn.fr && dsIn.fr.contentWindow) { dsIn.fr.contentWindow.postMessage(msg, SITE_ORIGIN); sent = true; }
        else if (dialog && typeof dialog.messageChild === 'function') { dialog.messageChild(msg); sent = true; }
      } catch (e) { sent = false; }
      if (!sent) { resolve(''); return; }
      dsAskWait[id] = resolve;
      setTimeout(function () { if (dsAskWait[id]) { delete dsAskWait[id]; resolve(''); } }, 120000);
    });
  }
  function dsRatioName(w, h) {
    var r = w / h, L = [[16 / 9, '16:9'], [4 / 3, '4:3'], [9 / 16, '9:16'], [3 / 4, '3:4'], [1, '1:1']];
    for (var i = 0; i < L.length; i++) { if (Math.abs(r - L[i][0]) < 0.03) return L[i][1]; }
    return (w >= h ? 'landscape' : 'portrait');
  }
  async function dsDeckSize(base64) {
    if (typeof DecompressionStream === 'undefined' || typeof Response === 'undefined') return null;
    var bin = atob(base64), bytes = new Uint8Array(bin.length), n;
    for (n = 0; n < bin.length; n++) bytes[n] = bin.charCodeAt(n);
    var dv = new DataView(bytes.buffer), eocd = -1;
    for (n = bytes.length - 22; n >= 0 && n >= bytes.length - 66000; n--) { if (dv.getUint32(n, true) === 0x06054b50) { eocd = n; break; } }
    if (eocd < 0) return null;
    var count = dv.getUint16(eocd + 10, true), p = dv.getUint32(eocd + 16, true), dec = new TextDecoder('utf-8'), f = null;
    for (n = 0; n < count; n++) {
      if (p + 46 > bytes.length || dv.getUint32(p, true) !== 0x02014b50) return null;
      var nl = dv.getUint16(p + 28, true);
      if (dec.decode(bytes.subarray(p + 46, p + 46 + nl)) === 'ppt/presentation.xml') { f = { method: dv.getUint16(p + 10, true), size: dv.getUint32(p + 20, true), at: dv.getUint32(p + 42, true) }; break; }
      p += 46 + nl + dv.getUint16(p + 30, true) + dv.getUint16(p + 32, true);
    }
    if (!f || dv.getUint32(f.at, true) !== 0x04034b50) return null;
    var st = f.at + 30 + dv.getUint16(f.at + 26, true) + dv.getUint16(f.at + 28, true), raw = bytes.subarray(st, st + f.size), xml;
    if (f.method === 0) xml = dec.decode(raw);
    else if (f.method === 8) xml = dec.decode(await new Response(new Blob([raw]).stream().pipeThrough(new DecompressionStream('deflate-raw'))).arrayBuffer());
    else return null;
    var m = /<p:sldSz\b[^>]*>/.exec(xml || ''); if (!m) return null;
    var cx = /\bcx="(\d+)"/.exec(m[0]), cy = /\bcy="(\d+)"/.exec(m[0]);
    if (!cx || !cy) return null;
    return { w: parseInt(cx[1], 10) / 12700, h: parseInt(cy[1], 10) / 12700 };
  }
  async function dsFitSlideSize(base64) {
    if (!Office.context.requirements.isSetSupported('PowerPointApi', '1.10')) return;
    var deck = await dsDeckSize(base64); if (!deck || !deck.w || !deck.h) return;
    var cur = null, count = 0;
    await PowerPoint.run(async function (ctx) {
      var ps = ctx.presentation.pageSetup; ps.load('slideWidth,slideHeight');
      var sl = ctx.presentation.slides; sl.load('items');
      await ctx.sync();
      cur = { w: ps.slideWidth, h: ps.slideHeight }; count = sl.items.length;
    });
    if (!cur || !cur.w || !cur.h) return;
    var rd = deck.w / deck.h, rc = cur.w / cur.h;
    if (Math.abs(rd - rc) / rc < 0.02) return;
    var dn = dsRatioName(deck.w, deck.h), cn = dsRatioName(cur.w, cur.h), change = count === 0;
    if (!change) {
      change = (await dsAsk('These slides are ' + dn + ' but this PowerPoint presentation is ' + cn + '. Change the presentation to ' + dn + ' so they fit? Slides already in it change shape too.',
        [{ k: 'change', label: 'Change to ' + dn }, { k: 'keep', label: 'Keep ' + cn }])) === 'change';
    }
    if (!change) return;
    await PowerPoint.run(async function (ctx) {
      var ps = ctx.presentation.pageSetup;
      ps.slideWidth = deck.w; ps.slideHeight = deck.h;
      await ctx.sync();
    });
    setStatus('Presentation changed to ' + dn + '.');
  }

  async function insertSlides(base64) {
    if (!Office.context.requirements.isSetSupported('PowerPointApi', '1.2')) {
      throw new Error('This version of PowerPoint cannot insert slides. Please update Microsoft 365.');
    }
    try { await dsFitSlideSize(base64); } catch (e) { log('Slide size check skipped: ' + ((e && e.message) || e)); }
    var target = null;
    if (Office.context.requirements.isSetSupported('PowerPointApi', '1.5')) {
      try {
        await PowerPoint.run(async function (ctx) {
          var sel = ctx.presentation.getSelectedSlides();
          sel.load('items/id');
          await ctx.sync();
          if (sel.items.length) target = sel.items[sel.items.length - 1].id;
        });
      } catch (e) { target = null; }
    }
    var attempts = [
      { formatting: config.formatting, targetSlideId: target },
      { formatting: config.formatting },
      {}
    ];
    /* Add-in only: leave the site's offer slide out (see dsOfferSlideKeep). */
    var keep = null;
    try { keep = await dsOfferSlideKeep(base64); }
    catch (e) { keep = null; log('Offer slide check skipped: ' + ((e && e.message) || e)); }
    if (keep) {
      attempts = attempts.map(function (a) {
        var o = {}, k;
        for (k in a) o[k] = a[k];
        o.sourceSlideIds = keep;
        return o;
      }).concat(attempts);
    }
    var lastErr;
    for (var i = 0; i < attempts.length; i++) {
      var opts = attempts[i];
      if (!opts.targetSlideId) delete opts.targetSlideId;
      if (i === 0 && !target) continue;
      try {
        await PowerPoint.run(async function (ctx) {
          ctx.presentation.insertSlidesFromBase64(base64, opts);
          await ctx.sync();
        });
        return;
      } catch (e) { lastErr = e; log('Insert attempt ' + (i + 1) + ' failed: ' + (e.code || e.message)); }
    }
    throw lastErr;
  }

  function imageSize(b64) {
    var mime = b64.indexOf('iVBOR') === 0 ? 'image/png' : b64.indexOf('/9j/') === 0 ? 'image/jpeg' : b64.indexOf('R0lG') === 0 ? 'image/gif' : b64.indexOf('Qk') === 0 ? 'image/bmp' : 'image/png';
    return new Promise(function (resolve) {
      var img = new Image();
      img.onload = function () { resolve({ w: img.naturalWidth || 800, h: img.naturalHeight || 600 }); };
      img.onerror = function () { resolve({ w: 800, h: 600 }); };
      img.src = 'data:' + mime + ';base64,' + b64;
    });
  }

  // Places the picture in the middle of the current slide (PowerPointApi 1.8+), else uses the classic method.
  async function insertImage(b64) {
    if (Office.context.requirements.isSetSupported('PowerPointApi', '1.8')) {
      try {
        var dim = await imageSize(b64);
        await PowerPoint.run(async function (ctx) {
          var sel = ctx.presentation.getSelectedSlides();
          sel.load('items');
          await ctx.sync();
          var slide = sel.items.length ? sel.items[0] : ctx.presentation.slides.getItemAt(0);
          var sw = 960, sh = 540;
          if (Office.context.requirements.isSetSupported('PowerPointApi', '1.10')) {
            var ps = ctx.presentation.pageSetup;
            ps.load('slideWidth,slideHeight');
            await ctx.sync();
            sw = ps.slideWidth || sw; sh = ps.slideHeight || sh;
          }
          var k = Math.min((sw * 0.8) / dim.w, (sh * 0.8) / dim.h, 1);
          var w = dim.w * k, h = dim.h * k;
          var shp = slide.shapes.addGeometricShape(PowerPoint.GeometricShapeType.rectangle, { left: (sw - w) / 2, top: (sh - h) / 2, width: w, height: h });
          shp.name = 'DesignStager picture';
          shp.fill.setImage(b64);
          shp.lineFormat.visible = false;
          await ctx.sync();
        });
        return;
      } catch (e) { log('Picture insert (shape) failed, trying classic: ' + (e.code || e.message)); }
    }
    return setSelectedData(b64, Office.CoercionType.Image);
  }

  function insert(kind, data) {
    if (kind === 'pptx') return insertSlides(data);
    if (kind === 'image') return insertImage(data);
    if (kind === 'svg') {
      if (!Office.context.requirements.isSetSupported('ImageCoercion', '1.2')) {
        return Promise.reject(new Error('This version of PowerPoint cannot insert SVG. Please update Microsoft 365.'));
      }
      return setSelectedData(data, Office.CoercionType.XmlSvg);
    }
    return Promise.reject(new Error('Unsupported file type.'));
  }

  /* ------------------------------------ insert straight from the task pane */
  // Everything goes through the site's own proxy, so the login check,
  // Download Monitor's permissions, the plan check and the daily download
  // limits all still apply exactly as they do on the website.
  function proxyFor(url) {
    return SITE + '/ds-office/' + ADDIN_VERSION + '/fetch?url=' + encodeURIComponent(url);
  }

  function blobToBase64(blob) {
    return new Promise(function (resolve, reject) {
      var fr = new FileReader();
      fr.onload = function () {
        var out = String(fr.result);
        var comma = out.indexOf(',');
        resolve(comma === -1 ? out : out.slice(comma + 1));
      };
      fr.onerror = function () { reject(new Error('Could not read the downloaded file.')); };
      fr.readAsDataURL(blob);
    });
  }

  function kindOfFile(type, url, hint) {
    var t = String(type || '').toLowerCase();
    var p = String(url || '').split('?')[0].toLowerCase();
    if (t.indexOf('presentationml') !== -1 || /\.pptx?$/.test(p)) return 'pptx';
    if (t.indexOf('svg') !== -1 || /\.svg$/.test(p)) return 'svg';
    if (t.indexOf('image/') === 0 || /\.(png|jpe?g|gif|bmp|webp)$/.test(p)) return 'image';
    return hint || 'image';
  }

  /* Marketplace originals can be 5000px and tens of megabytes. PowerPoint
     chokes on those, so anything larger than 3840x2160 (4K) is scaled down
     first. 1920x1080 was too small: a full-slide picture looked soft on large
     or high-DPI screens and when presented.
     The watermark is part of the pixels, so it survives the resize.       */
  /* PowerPoint silently cuts off a picture whose data is larger than about
     3.6 MB (roughly 5 million base64 characters): only the top part is drawn
     and the rest of the frame stays empty. Tested in PowerPoint for Windows:
     3.4 MB complete, 4.2 MB bottom strip missing, 5.9 MB only the top 60%.
     So every picture is kept at or under DS_IMG_MAX_BYTES before inserting. */
  var DS_IMG_MAX_BYTES = 3 * 1024 * 1024;

  function dsCanvasBlob(cv, type, q) {
    return new Promise(function (done) { cv.toBlob(done, type, q); });
  }

  async function dsFitImage(blob, maxW, maxH) {
    try {
      if (typeof createImageBitmap !== 'function' || !blob || !blob.size) return blob;
      var bmp = await createImageBitmap(blob);
      var w = bmp.width, h = bmp.height;
      /* PowerPoint only takes PNG, JPEG, GIF and BMP pictures reliably: a
         .webp or .avif is re-encoded even when it is small enough. */
      var native = /^image\/(png|jpe?g|gif|bmp)$/i.test(blob.type || '');
      if (!w || !h || (w <= maxW && h <= maxH && native && blob.size <= DS_IMG_MAX_BYTES)) {
        if (bmp.close) bmp.close();
        return blob;
      }
      var sc = Math.min(1, maxW / w, maxH / h);
      var out = null, nw = 0, nh = 0;
      for (var pass = 0; pass < 8; pass++) {
        nw = Math.max(1, Math.round(w * sc)); nh = Math.max(1, Math.round(h * sc));
        var cv = document.createElement('canvas');
        cv.width = nw; cv.height = nh;
        var cx = cv.getContext('2d');
        cx.imageSmoothingEnabled = true; cx.imageSmoothingQuality = 'high';
        cx.drawImage(bmp, 0, 0, nw, nh);
        /* PNG first so flat art and transparency stay exact; a photo that is
           still too big becomes JPEG, which looks the same at a fraction of
           the size. Only if that is still too big is it made smaller. */
        out = await dsCanvasBlob(cv, 'image/png');
        if (!out || out.size > DS_IMG_MAX_BYTES) {
          var jpg = await dsCanvasBlob(cv, 'image/jpeg', 0.92);
          if (jpg && jpg.size) { out = jpg; }
        }
        if (out && out.size <= DS_IMG_MAX_BYTES) { break; }
        sc = sc * 0.85;
      }
      if (bmp.close) bmp.close();
      if (!out || !out.size) return blob;
      log('Prepared ' + w + 'x' + h + ' as ' + nw + 'x' + nh + ' ' + out.type + ' (' + Math.round(out.size / 1024) + ' KB)');
      return out;
    } catch (e) { return blob; }
  }

  async function insertFromUrl(url, hint) {
    if (!url) throw new Error('Sign in to DesignStager to insert this item.');
    var res;
    try {
      res = await fetch(proxyFor(url), { credentials: 'include', cache: 'no-store' });
    } catch (e) {
      throw new Error('No connection to DesignStager. Check your internet and try again.');
    }
    if (!res.ok) {
      var detail = '';
      try { detail = (await res.text()).slice(0, 480); } catch (e) {}
      var dsLim = ''; try { dsLim = res.headers.get('x-ds-limit') || ''; } catch (e) {}
      if (res.status === 401) throw new Error('Please sign in to DesignStager first.');
      if (res.status === 403) { var e403 = new Error(detail || 'This item is not included in your plan.'); if (dsLim) e403.dsLimit = dsLim; throw e403; }
      if (res.status === 429) { var e429 = new Error(detail || 'You have reached your download limit for now. This is a plan limit, not an error.'); e429.dsLimit = dsLim || 'day'; throw e429; }
      throw new Error(detail || ('Could not download the file (HTTP ' + res.status + ').'));
    }
    var type = res.headers.get('content-type') || '';
    if (type.indexOf('text/html') === 0) throw new Error('Please sign in to DesignStager first.');
    /* response.blob() fails ("Failed to fetch") on large files (a 28 MB PNG) in
       the Office web view, while reading the bytes works. */
    var blob;
    try { blob = new Blob([await res.arrayBuffer()], { type: type.split(';')[0] }); }
    catch (e) { throw new Error('The file could not be downloaded. Check your connection and try again.'); }
    if (!blob.size) throw new Error('That file came back empty. Please try again.');
    var kind = kindOfFile(type, url, hint);
    if (kind === 'image') { blob = await dsFitImage(blob, 3840, 2160); }
    if (kind === 'svg') return insert('svg', await blob.text());
    log('Inserting ' + kind + ' (' + Math.round(blob.size / 1024) + ' KB) …');
    return insert(kind, await blobToBase64(blob));
  }

  /* ---------------------------------------------- ribbon command */
  function openLibrary(event) {
    try { openDialog(); } finally { if (event && event.completed) event.completed(); }
  }

/* ---------------------------------------------------------------- media slide
   PowerPoint's JavaScript API cannot place audio or video on a slide directly.
   The supported route is to hand it a one-slide .pptx that already contains the
   media, and insert that. This builds one in the browser: a small pre-made deck
   (theme, master, blank layout) has five parts appended to it - the slide, its
   relationships, the content types, the media file and a poster image.         */

var DS_MEDIA_SHELL_B64 = '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';
var DS_POSTER_VIDEO_B64 = 'iVBORw0KGgoAAAANSUhEUgAAAoAAAAFoAgMAAAC6XKLCAAAACVBMVEX/////azUUIDgVK9vOAAACJElEQVR42u3aMW7CQBQE0DUyvQsOxBEoOGVEwRF8IAr3IDlFEiQEaZK/0kg8V1uhp1n99azF8NGyn00DBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBHxj4Fj7c+upbQ/BCa6n1q7nYOCltdauucDr3Fpr7RQLXL43OhW4zt+Lcyjw9rQIAy73KEOB84tVEnB9kWUU8PZyGQRcXoYZBJx/WacA11/S1Af/MCOVU7LpMSOVU/JmlX9NBs4PPWYO3eJbboLrYyVcUofkkn4vXudwYGXTqgX+jMe1eFrqD+pz+BYX39t7vOrmdOAlHVh70nQADvtw4C59i/fhwG36MXMIBY6PAY6xCU7hbab2jOkA3LVU4PAY4BSa4NhiE/w6nw/lp3VtgkP6l4WWDJz6RLnpMx+jLf7ngEyJCe67dMJC4NTluCkEjl1eKIXAoUvnqpzifY9rSSVw6vHGqwSOPTpNJfBeBg+hwJ89Lu00pcDtV4TH3Lq1q7+5FxfWY6v+79bw8TZtBhAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBDw+fkEf6s+JV4jXgkAAAAASUVORK5CYII=';
var DS_POSTER_AUDIO_B64 = 'iVBORw0KGgoAAAANSUhEUgAAAWgAAAFoAQMAAABAK/OWAAAABlBMVEX/azUUIDjOHctcAAACR0lEQVR42u2aPW7bQBCF31KCqSIQWboIHB3BB0iho+QILtOFBly49BFyFMLwAXwEJVXKVSrKkDQpGNk0d5eeoWU4CN4WFER+eJidP+0u5QSGkYE0adKkSZMmTZo0adKkSZMeRTcmurTQP559c8M7XcnyRq99C4P2bgqD9h0M2psZDNrXMGivSxi0S0vk15Y8kdJC/4zcS85yN20/dbO8g0H7IK3TvoJBezODQXtmqcs1exVp0v8w7S30J4u2W1noM4vdk5WF/mzxSV5b6K+Rn5fY8ACQi4j4vHs/U65jXrKksETeeQt9ZsmqfsyH6TnrkjRp0qRJkyZNmjTp19I7V+n2JIWIyAqFek8CLPBbb8kOkEpNbwHUavoXgHs1vTJ5sFbv6gqR9kxN60ExxXL/hnmyNdHNG1ri3712LsuBiEX28t88CpFaE/kb4EatvQcwUWs/tCWmnGUDAJWWvgeA71q6ToQxPsvDmYVqltLtPUDv/Dl7uWAG6K2JbsbmYNm5Jmg/VnvW8UyC7vS+9h3Ccnzt9GNZPcVyHz4f0HaPF5UlVfg8nlQopE2rXGsJFv2kGrTkFMC5mp723R28gzn4oPAAHNxeo537NrBzQx/8iIvQtuSYyFF7bIWwdt+n2y8RVuOxtBcIq/FY2iXC2h2r3c/Bvy8ARac9RVi7aTobtC+LtYVkZmaxtpBKqpBeDgQ+pM8B4IvSg60LRat9AmCizm9XAR9s6xNJjMg6tipSsDj+l580adKkSZMmTZo0adKkSZMm/R/QfwBLGcLYWBRLHQAAAABJRU5ErkJggg==';

var DS_CRC_TABLE = (function () {
  var t = new Int32Array(256), c, n, k;
  for (n = 0; n < 256; n++) {
    c = n;
    for (k = 0; k < 8; k++) c = (c & 1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1);
    t[n] = c;
  }
  return t;
})();
function dsCrc32(buf) {
  var c = -1, i;
  for (i = 0; i < buf.length; i++) c = DS_CRC_TABLE[(c ^ buf[i]) & 0xFF] ^ (c >>> 8);
  return (c ^ -1) >>> 0;
}
function dsB64ToBytes(b64) {
  var bin = atob(b64), out = new Uint8Array(bin.length), i;
  for (i = 0; i < bin.length; i++) out[i] = bin.charCodeAt(i);
  return out;
}
function dsBytesToB64(bytes) {
  var s = '', CH = 0x8000, i;
  for (i = 0; i < bytes.length; i += CH) {
    s += String.fromCharCode.apply(null, bytes.subarray(i, i + CH));
  }
  return btoa(s);
}
function dsUtf8(str) {
  if (typeof TextEncoder !== 'undefined') return new TextEncoder().encode(str);
  var esc = unescape(encodeURIComponent(str)), out = new Uint8Array(esc.length), i;
  for (i = 0; i < esc.length; i++) out[i] = esc.charCodeAt(i);
  return out;
}

/* Append STORED entries to an existing zip and rewrite the central directory.
   Existing entries keep their bytes and offsets, so nothing has to be inflated. */
function dsZipAppend(baseBytes, entries) {
  var i, eocd = -1;
  for (i = baseBytes.length - 22; i >= 0; i--) {
    if (baseBytes[i] === 0x50 && baseBytes[i+1] === 0x4B && baseBytes[i+2] === 0x05 && baseBytes[i+3] === 0x06) { eocd = i; break; }
  }
  if (eocd < 0) throw new Error('The slide template is damaged.');
  var dv = new DataView(baseBytes.buffer, baseBytes.byteOffset, baseBytes.byteLength);
  var oldCount = dv.getUint16(eocd + 10, true);
  var cdSize   = dv.getUint32(eocd + 12, true);
  var cdOff    = dv.getUint32(eocd + 16, true);

  var parts = [], offset = cdOff, added = [];
  parts.push(baseBytes.subarray(0, cdOff));            // existing local entries

  for (i = 0; i < entries.length; i++) {
    var nameB = dsUtf8(entries[i].name);
    var data  = entries[i].data;
    var crc   = dsCrc32(data);
    var lh    = new Uint8Array(30 + nameB.length);
    var lv    = new DataView(lh.buffer);
    lv.setUint32(0, 0x04034B50, true);
    lv.setUint16(4, 20, true); lv.setUint16(6, 0x0800, true); lv.setUint16(8, 0, true);
    lv.setUint16(10, 0, true); lv.setUint16(12, 0x21, true);
    lv.setUint32(14, crc, true);
    lv.setUint32(18, data.length, true); lv.setUint32(22, data.length, true);
    lv.setUint16(26, nameB.length, true); lv.setUint16(28, 0, true);
    lh.set(nameB, 30);
    parts.push(lh, data);
    added.push({ nameB: nameB, crc: crc, size: data.length, off: offset });
    offset += lh.length + data.length;
  }

  var cdOld = baseBytes.subarray(cdOff, cdOff + cdSize);
  parts.push(cdOld);
  var cdBytes = cdSize;
  for (i = 0; i < added.length; i++) {
    var a = added[i];
    var ch = new Uint8Array(46 + a.nameB.length);
    var cv = new DataView(ch.buffer);
    cv.setUint32(0, 0x02014B50, true);
    cv.setUint16(4, 20, true); cv.setUint16(6, 20, true);
    cv.setUint16(8, 0x0800, true); cv.setUint16(10, 0, true);
    cv.setUint16(12, 0, true); cv.setUint16(14, 0x21, true);
    cv.setUint32(16, a.crc, true);
    cv.setUint32(20, a.size, true); cv.setUint32(24, a.size, true);
    cv.setUint16(28, a.nameB.length, true);
    cv.setUint32(42, a.off, true);
    ch.set(a.nameB, 46);
    parts.push(ch);
    cdBytes += ch.length;
  }

  var end = new Uint8Array(22), ev = new DataView(end.buffer);
  ev.setUint32(0, 0x06054B50, true);
  ev.setUint16(8, oldCount + added.length, true);
  ev.setUint16(10, oldCount + added.length, true);
  ev.setUint32(12, cdBytes, true);
  ev.setUint32(16, offset, true);
  parts.push(end);

  var total = 0;
  for (i = 0; i < parts.length; i++) total += parts[i].length;
  var out = new Uint8Array(total), p = 0;
  for (i = 0; i < parts.length; i++) { out.set(parts[i], p); p += parts[i].length; }
  return out;
}

function dsXmlEscape(s) {
  return String(s == null ? '' : s)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/\u0000-\u0008/g, '');
}

var DS_MEDIA_MIME = {
  mp4: 'video/mp4', m4v: 'video/x-m4v', mov: 'video/quicktime', webm: 'video/webm',
  mp3: 'audio/mpeg', m4a: 'audio/mp4', wav: 'audio/wav', aac: 'audio/aac', ogg: 'audio/ogg'
};

function dsMediaDeck(mediaBytes, ext, title, isAudio, posterBytes) {
  var XD = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
  var OR = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/';
  var mime = DS_MEDIA_MIME[ext];
  if (!mime) throw new Error('PowerPoint cannot use a .' + ext + ' file on a slide.');

  var SW = 12192000, SH = 6858000, cx, cy;
  if (isAudio) { cx = 1143000; cy = 1143000; } else { cx = 9144000; cy = 5143500; }
  var x = Math.round((SW - cx) / 2), y = Math.round((SH - cy) / 2) - 400000;

  var ct = XD + '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    + '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    + '<Default Extension="xml" ContentType="application/xml"/>'
    + '<Default Extension="png" ContentType="image/png"/>'
    + '<Default Extension="' + ext + '" ContentType="' + mime + '"/>'
    + '<Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/>'
    + '<Override PartName="/ppt/slideMasters/slideMaster1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideMaster+xml"/>'
    + '<Override PartName="/ppt/slideLayouts/slideLayout1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideLayout+xml"/>'
    + '<Override PartName="/ppt/slides/slide1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>'
    + '<Override PartName="/ppt/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/>'
    + '</Types>';

  var rels = XD + '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    + '<Relationship Id="rId1" Type="' + OR + 'slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'
    + '<Relationship Id="rId2" Type="http://schemas.microsoft.com/office/2007/relationships/media" Target="../media/media1.' + ext + '"/>'
    + '<Relationship Id="rId3" Type="' + OR + (isAudio ? 'audio' : 'video') + '" Target="../media/media1.' + ext + '"/>'
    + '<Relationship Id="rId4" Type="' + OR + 'image" Target="../media/image1.png"/>'
    + '</Relationships>';

  var tag = isAudio ? 'audioFile' : 'videoFile';
  var tnode = isAudio ? 'audio' : 'video';
  var caption = '';
  if (title) {
    caption = '<p:sp><p:nvSpPr><p:cNvPr id="3" name="Caption"/><p:cNvSpPr txBox="1"/><p:nvPr/></p:nvSpPr>'
      + '<p:spPr><a:xfrm><a:off x="914400" y="' + (y + cy + 260000) + '"/><a:ext cx="' + (SW - 1828800) + '" cy="600000"/></a:xfrm>'
      + '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom><a:noFill/></p:spPr>'
      + '<p:txBody><a:bodyPr wrap="square" rtlCol="0" anchor="t"><a:spAutoFit/></a:bodyPr><a:lstStyle/>'
      + '<a:p><a:pPr algn="ctr"/><a:r><a:rPr lang="en-US" sz="1800" b="1" dirty="0">'
      + '<a:solidFill><a:srgbClr val="142038"/></a:solidFill></a:rPr>'
      + '<a:t>' + dsXmlEscape(title) + '</a:t></a:r></a:p></p:txBody></p:sp>';
  }

  var slide = XD
    + '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'
    + ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"'
    + ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
    + '<p:cSld><p:spTree><p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr><p:grpSpPr/>'
    + '<p:pic><p:nvPicPr><p:cNvPr id="2" name="' + dsXmlEscape(title || 'Media') + '">'
    + '<a:hlinkClick r:id="" action="ppaction://media"/></p:cNvPr>'
    + '<p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr>'
    + '<p:nvPr><a:' + tag + ' r:link="rId3"/><p:extLst>'
    + '<p:ext uri="{DAA4B4D4-6D71-4841-9C94-3DE7FCFB9230}">'
    + '<p14:media xmlns:p14="http://schemas.microsoft.com/office/powerpoint/2010/main" r:embed="rId2"/>'
    + '</p:ext></p:extLst></p:nvPr></p:nvPicPr>'
    + '<p:blipFill><a:blip r:embed="rId4"/><a:stretch><a:fillRect/></a:stretch></p:blipFill>'
    + '<p:spPr><a:xfrm><a:off x="' + x + '" y="' + y + '"/><a:ext cx="' + cx + '" cy="' + cy + '"/></a:xfrm>'
    + '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic>'
    + caption
    + '</p:spTree></p:cSld><p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr>'
    + '<p:timing><p:tnLst><p:par><p:cTn id="1" dur="indefinite" restart="never" nodeType="tmRoot"><p:childTnLst>'
    + '<p:' + tnode + '><p:cMediaNode vol="80000"><p:cTn id="2" fill="hold" display="0">'
    + '<p:stCondLst><p:cond delay="indefinite"/></p:stCondLst></p:cTn>'
    + '<p:tgtEl><p:spTgt spid="2"/></p:tgtEl></p:cMediaNode></p:' + tnode + '>'
    + '</p:childTnLst></p:cTn></p:par></p:tnLst></p:timing></p:sld>';

  var poster = (posterBytes && posterBytes.length) ? posterBytes : dsB64ToBytes(isAudio ? DS_POSTER_AUDIO_B64 : DS_POSTER_VIDEO_B64);
  return dsZipAppend(dsB64ToBytes(DS_MEDIA_SHELL_B64), [
    { name: '[Content_Types].xml',             data: dsUtf8(ct) },
    { name: 'ppt/slides/slide1.xml',           data: dsUtf8(slide) },
    { name: 'ppt/slides/_rels/slide1.xml.rels', data: dsUtf8(rels) },
    { name: 'ppt/media/media1.' + ext,          data: mediaBytes },
    { name: 'ppt/media/image1.png',             data: poster }
  ]);
}

  /* A real picture for the video on the slide, instead of the generic play
     placeholder: a frame from the clip itself (about 1 s in), or failing that
     the marketplace poster. Always PNG, because the slide part is image1.png. */
  function dsCanvasPngBytes(c) {
    return new Promise(function (done) {
      c.toBlob(function (b) {
        if (!b) { done(null); return; }
        b.arrayBuffer().then(function (ab) { done(new Uint8Array(ab)); }, function () { done(null); });
      }, 'image/png');
    });
  }
  function dsDrawToPng(src, w, h) {
    var k = Math.min(1, 1600 / (w || 1600));
    var c = document.createElement('canvas');
    c.width = Math.max(1, Math.round((w || 1600) * k)); c.height = Math.max(1, Math.round((h || 900) * k));
    var g = c.getContext('2d'); g.imageSmoothingEnabled = true; g.imageSmoothingQuality = 'high';
    g.drawImage(src, 0, 0, c.width, c.height);
    return dsCanvasPngBytes(c);
  }
  function dsVideoFrame(bytes, ext) {
    return new Promise(function (resolve) {
      var done = false, v = document.createElement('video'), u;
      function finish(r) { if (done) return; done = true; try { URL.revokeObjectURL(u); } catch (e) {} resolve(r); }
      try { u = URL.createObjectURL(new Blob([bytes], { type: DS_MEDIA_MIME[ext] || 'video/mp4' })); } catch (e) { resolve(null); return; }
      function finish2(r) { try { v.pause(); v.removeAttribute('src'); v.load(); if (v.parentNode) v.parentNode.removeChild(v); } catch (e) {} finish(r); }
      setTimeout(function () { finish2(null); }, 8000);
      v.muted = true; v.playsInline = true; v.preload = 'auto';
      /* Off-screen but in the page, and nudged with a muted play(): some
         WebView builds do not decode a detached, never-played video. */
      v.style.cssText = 'position:fixed;left:-10000px;top:0;width:4px;height:4px;opacity:0;pointer-events:none';
      try { document.body.appendChild(v); } catch (e) {}
      var seeking = false;
      v.onerror = function () { finish2(null); };
      v.onloadeddata = function () {
        if (seeking) return; seeking = true;
        try { v.pause(); v.currentTime = Math.min(1, (isFinite(v.duration) && v.duration > 0 ? v.duration : 2) / 2); } catch (e) { finish2(null); }
      };
      v.onseeked = function () {
        try { dsDrawToPng(v, v.videoWidth, v.videoHeight).then(finish2, function () { finish2(null); }); } catch (e) { finish2(null); }
      };
      v.src = u;
      try { var pp = v.play(); if (pp && pp.catch) pp.catch(function () {}); } catch (e) {}
    });
  }
  function dsImageUrlToPng(url) {
    return new Promise(function (resolve) {
      if (!url) { resolve(null); return; }
      var im = new Image(), done = false;
      function finish(r) { if (!done) { done = true; resolve(r); } }
      setTimeout(function () { finish(null); }, 8000);
      im.onload = function () { try { dsDrawToPng(im, im.naturalWidth, im.naturalHeight).then(finish, function () { finish(null); }); } catch (e) { finish(null); } };
      im.onerror = function () { finish(null); };
      im.src = url;
    });
  }

  /* Fetch through the site's own proxy - sign-in, plan and download limits all
     still apply - then wrap the media in a one-slide deck and insert that.    */
  async function insertMedia(url, kind, title, thumb) {
    if (!url) throw new Error('Sign in to DesignStager to insert this item.');
    var isAudio = (kind === 'audios' || kind === 'audio');
    var res;
    try {
      res = await fetch(proxyFor(url), { credentials: 'include', cache: 'no-store' });
    } catch (e) {
      throw new Error('No connection to DesignStager. Check your internet and try again.');
    }
    if (!res.ok) {
      var detail = '';
      try { detail = (await res.text()).slice(0, 480); } catch (e) {}
      var dsLim = ''; try { dsLim = res.headers.get('x-ds-limit') || ''; } catch (e) {}
      if (res.status === 401) throw new Error('Please sign in to DesignStager first.');
      if (res.status === 403) { var e403 = new Error(detail || 'This item is not included in your plan.'); if (dsLim) e403.dsLimit = dsLim; throw e403; }
      if (res.status === 429) { var e429 = new Error(detail || 'You have reached your download limit for now. This is a plan limit, not an error.'); e429.dsLimit = dsLim || 'day'; throw e429; }
      throw new Error(detail || ('Could not download the file (HTTP ' + res.status + ').'));
    }
    var type = res.headers.get('content-type') || '';
    if (type.indexOf('text/html') === 0) throw new Error('Please sign in to DesignStager first.');
    var bytes = new Uint8Array(await res.arrayBuffer());
    if (!bytes.length) throw new Error('That file came back empty. Please try again.');
    if (bytes.length > 45 * 1024 * 1024) {
      throw new Error('This file is ' + Math.round(bytes.length / 1048576) + ' MB - too large to place on a slide. Open it in the browser and add it from your computer instead.');
    }
    var clean = String(url).split('?')[0].split('#')[0];
    var ext = (clean.split('.').pop() || '').toLowerCase();
    if (!DS_MEDIA_MIME[ext]) ext = isAudio ? 'mp3' : 'mp4';
    log('Building a slide for ' + (title || 'media') + ' (' + Math.round(bytes.length / 1024) + ' KB) ...');
    var posterBytes = null;
    if (!isAudio) {
      posterBytes = await dsVideoFrame(bytes, ext);
      if (!posterBytes) { posterBytes = await dsImageUrlToPng(thumb); }
    }
    return insertSlides(dsBytesToB64(dsMediaDeck(bytes, ext, title, isAudio, posterBytes)));
  }



  /* ---------------------------------------------- inline view (in the pane)
     AI tools, the library and site pages open INSIDE the task pane first,
     showing the real website page (its mobile layout). The bar on top can
     zoom the page, move it to the large PowerPoint window, or open it in the
     browser - every one of those only on an explicit click (AppSource 1100.4
     / 1100.5). The page talks to us with postMessage using the same messages
     the large window sends, so Insert works the same way. */
  var dsIn = { el: null, fr: null, ttl: null, zl: null, nt: null, zoom: 100, url: '', signin: '', last: '' };
  /* No zoom buttons: pages always show at 100 % (an old saved zoom is ignored). */

  function dsIsWeb() {
    try { return !!(window.Office && Office.context && Office.PlatformType && Office.context.platform === Office.PlatformType.OfficeOnline); } catch (e) { return false; }
  }

  /* The add-in page loaded outside PowerPoint's side panel - e.g. a website page sent the large window
     back to it. PowerPoint cannot open windows from there, so its buttons open pages in this window. */
  var dsHostSeen = false;
  try { if (window.Office && Office.onReady) Office.onReady(function () { dsHostSeen = true; }); } catch (e) {}
  function dsNoPane() {
    if (!dsHostSeen) return false;
    try { return !(Office.context && Office.context.host); } catch (e) { return true; }
  }

  function dsInlineIcon(d) {
    return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + d + '</svg>';
  }

  function dsInlineBuild() {
    if (dsIn.el) return;
    var st = document.createElement('style');
    st.id = 'ds-inline-style';
    st.textContent =
      '#ds-inline{position:fixed;top:0;right:0;bottom:0;left:0;z-index:99999;display:none;flex-direction:column;background:var(--bg,#0b1020);color:var(--text,#eef1f8)}' +
      '#ds-inline.on{display:flex}' +
      '#ds-inline .ib{position:relative;display:flex;align-items:center;gap:4px;padding:6px;border-bottom:1px solid var(--line,#232b45);background:var(--card,#141a2e);flex:0 0 auto}' +
      '#ds-inline .ib button{flex:0 0 auto;min-width:34px;height:34px;padding:0 6px;border-radius:8px;border:1px solid var(--line,#232b45);background:var(--bg,#0b1020);color:inherit;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font:inherit;font-size:12px}' +
      '#ds-inline .ib button:hover,#ds-inline .ib button:focus-visible{border-color:var(--brand2,#ff7a45);outline:none}' +
      '#ds-inline .ib .t{flex:1 1 auto;min-width:0;padding:0 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;font-size:13px}' +
      '#ds-inline .ib .zl{min-width:44px;color:var(--muted,#9aa3b8)}' +
      '#ds-inline .ib .sep{width:1px;height:22px;background:var(--line,#232b45);margin:0 2px}' +
      '#ds-inline .ld{position:absolute;left:0;right:0;bottom:-1px;height:2px;background:linear-gradient(90deg,var(--brand,#f15a29),var(--brand2,#ff7a45));opacity:0;transition:opacity .2s}' +
      '#ds-inline.loading .ld{opacity:1;animation:dsInlPulse 1s ease-in-out infinite}' +
      '@keyframes dsInlPulse{0%,100%{opacity:.35}50%{opacity:1}}' +
      '#ds-inline .nt{display:none;align-items:center;gap:8px;padding:8px 10px;background:#3a2a10;color:#ffd79a;font-size:12.5px;line-height:1.4}' +
      '#ds-inline .nt.on{display:flex}' +
      '#ds-inline .nt button{flex:0 0 auto;height:30px;padding:0 10px;border-radius:7px;border:0;background:var(--brand,#f15a29);color:#fff;cursor:pointer;font:inherit;font-size:12px}' +
      '#ds-inline iframe{flex:1 1 auto;width:100%;min-height:0;border:0;background:var(--bg,#0b1020)}' +
      /* "Drag this edge to extend" tip at the panel's left edge (PowerPoint lets the user drag that edge) */
      '#ds-inline .xh{position:absolute;left:0;top:50%;z-index:6;display:none;align-items:center;gap:8px;padding:9px 10px 9px 6px;background:#fff;color:#111;border-radius:0 999px 999px 0;box-shadow:0 8px 24px rgba(0,0,0,.45);transform:translate(-110%,-50%);transition:transform .35s ease,opacity .35s ease;opacity:0;pointer-events:none;font-size:12px;line-height:1.2}' +
      '#ds-inline .xh.on{display:flex}' +
      '#ds-inline .xh.in{transform:translate(0,-50%);opacity:1}' +
      '#ds-inline .xh .xa{display:inline-flex;animation:dsXhArrow 1.1s ease-in-out infinite}' +
      '#ds-inline .xh .xb{width:7px;height:30px;border-radius:3px;background:#111;animation:dsXhBar 1.1s ease-in-out infinite}' +
      '#ds-inline .xh .xt{font-weight:600;white-space:nowrap}' +
      '#ds-inline .xh button{pointer-events:auto;min-width:0;width:22px;height:22px;padding:0;border-radius:50%;border:1px solid #bbb;background:#fff;color:#333;font-size:13px;line-height:1;cursor:pointer}' +
      '@keyframes dsXhArrow{0%,100%{transform:translateX(0)}50%{transform:translateX(-7px)}}' +
      '@keyframes dsXhBar{0%,100%{box-shadow:0 0 0 0 rgba(241,90,41,0)}50%{box-shadow:0 0 0 5px rgba(241,90,41,.45)}}' +
      '@media (prefers-reduced-motion:reduce){#ds-inline .xh .xa,#ds-inline .xh .xb{animation:none}#ds-inline .xh{transition:none}}';
    document.head.appendChild(st);

    var el = document.createElement('div');
    el.id = 'ds-inline';
    el.setAttribute('role', 'region');
    el.setAttribute('aria-label', 'DesignStager page');
    el.innerHTML =
      '<div class="ib">' +
        '<button type="button" data-a="back" title="Back" aria-label="Back to DesignStager home">' + dsInlineIcon('<path d="M15 18l-6-6 6-6"/>') + '</button>' +
        '<span class="t" aria-live="polite">DesignStager</span>' +
        '<button type="button" data-a="popup" title="Open in a large PowerPoint window" aria-label="Open in a large PowerPoint window">' + dsInlineIcon('<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18"/><path d="M10 14h6v4"/>') + '</button>' +
        '<button type="button" data-a="browser" title="Open in your browser" aria-label="Open in your browser">' + dsInlineIcon('<path d="M14 4h6v6"/><path d="M20 4l-9 9"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>') + '</button>' +
        '<span class="ld" aria-hidden="true"></span>' +
      '</div>' +
      '<div class="nt" role="status"><span>This browser keeps your sign-in out of the side panel. Open this page in the large PowerPoint window to continue.</span><button type="button" data-a="popup">Open window</button></div>' +
      '<iframe title="DesignStager" allow="clipboard-read; clipboard-write; fullscreen; autoplay"></iframe>' +
      '<div class="xh" role="note"><span class="xa" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M11 6l-6 6 6 6"/></svg></span><span class="xb" aria-hidden="true"></span><span class="xt">Drag this edge to extend</span><button type="button" data-a="xhide" title="Hide this tip" aria-label="Hide this tip">&times;</button></div>';
    document.body.appendChild(el);

    dsIn.el = el;
    dsIn.fr = el.querySelector('iframe');
    dsIn.ttl = el.querySelector('.t');
    dsIn.zl = el.querySelector('.zl');
    dsIn.nt = el.querySelector('.nt');

    el.addEventListener('click', function (ev) {
      var b = ev.target && ev.target.closest ? ev.target.closest('button[data-a]') : null;
      if (!b) return;
      var a = b.getAttribute('data-a');
      if (a === 'back') dsInlineClose();
      else if (a === 'popup') { var p = dsInlinePath(); dsInlineClose(); openDialog(p); }
      else if (a === 'browser') dsInlineBrowser();
      else if (a === 'xhide') dsHintDone();
      else if (a === 'retry') { if (dsIn.nt) dsIn.nt.classList.remove('on'); el.classList.add('loading'); dsIn.ttl.textContent = 'Loading\u2026'; try { dsIn.fr.contentWindow.location.reload(); } catch (e) { dsIn.fr.setAttribute('src', dsIn.url); } }
    });

    dsIn.fr.addEventListener('load', function () {
      el.classList.remove('loading');
      dsInlineApplyZoom();
      /* Remember the last page of our own site; a page from another site (e.g. a Google or Microsoft
         sign-in that refuses to open inside the panel) gets the "Open window" notice instead. */
      var dsSame = false;
      try { dsSame = !!(dsIn.fr.contentDocument && dsIn.fr.contentWindow.location.origin === SITE_ORIGIN); } catch (e) {}
      if (dsSame) {
        dsHintMaybe(); /* the tip also shows after a page change inside the panel (e.g. after sign-in) */
        try { var lu = new URL(dsIn.fr.contentWindow.location.href); lu.searchParams.delete('ds_office'); dsIn.last = lu.pathname + lu.search + lu.hash; } catch (e) {}
      } else if (dsIn.fr.getAttribute('src')) {
        dsIn.ttl.textContent = 'Opens in the large window';
        dsInlineNeedWindow('This page cannot open inside the side panel. Open it in the large PowerPoint window.', 'Open window', 'popup');
      }
      try {
        var t = String(dsIn.fr.contentDocument.title || '').split(/\s[\u2013\u2014|-]\s/)[0].trim();
        if (t) dsIn.ttl.textContent = t;
        /* Host busy page (503 etc.): say so and offer one manual retry - no automatic retries, so a busy server is not hit again and again. */
        if (/^(50[0-4])\b|service unavailable|bad gateway|gateway time-?out/i.test(String(dsIn.fr.contentDocument.title || ''))) {
          dsIn.ttl.textContent = 'Website busy';
          dsInlineNeedWindow('The website is busy right now. Please wait a few seconds and try again.', 'Try again', 'retry');
        }
      } catch (e) {}
      try {
        /* show the bar's loading line again whenever the page navigates */
        dsIn.fr.contentWindow.addEventListener('beforeunload', function () { el.classList.add('loading'); });
      } catch (e) {}
    });
  }

  function dsInlinePath() {
    try {
      var u = new URL(dsIn.fr.contentWindow.location.href);
      if (u.origin === SITE_ORIGIN) {
        u.searchParams.delete('ds_office');
        return (u.pathname + u.search + u.hash) || '/';
      }
    } catch (e) {}
    if (dsIn.last) return dsIn.last;
    try { var v = new URL(dsIn.url); v.searchParams.delete('ds_office'); return v.pathname + v.search + v.hash; } catch (e) {}
    return '/';
  }

  function dsInlineBrowser() {
    var href = SITE_ORIGIN + dsInlinePath();
    try {
      if (window.Office && Office.context && Office.context.ui && Office.context.ui.openBrowserWindow) { Office.context.ui.openBrowserWindow(href); return; }
    } catch (e) {}
    try { window.open(href, '_blank'); } catch (e) {}
  }

  function dsInlineApplyZoom() {
    if (dsIn.zl) dsIn.zl.textContent = dsIn.zoom + '%';
    try {
      var d = dsIn.fr && dsIn.fr.contentDocument;
      if (d && d.documentElement) d.documentElement.style.zoom = dsIn.zoom === 100 ? '' : String(dsIn.zoom / 100);
    } catch (e) {}
  }

  function dsInlineZoom(z) {
    dsIn.zoom = Math.max(50, Math.min(150, Math.round(z / 10) * 10));
    try { localStorage.setItem('ds_inline_zoom', String(dsIn.zoom)); } catch (e) {}
    dsInlineApplyZoom();
  }

  var DS_NT_DEFAULT = ['This browser keeps your sign-in out of the side panel. Open this page in the large PowerPoint window to continue.', 'Open window', 'popup'];
  function dsInlineNeedWindow(msg, label, act) {
    if (!dsIn.nt) return;
    var sp = dsIn.nt.querySelector('span'), bt = dsIn.nt.querySelector('button');
    if (sp) sp.textContent = msg || DS_NT_DEFAULT[0];
    if (bt) { bt.textContent = label || DS_NT_DEFAULT[1]; bt.setAttribute('data-a', act || DS_NT_DEFAULT[2]); }
    dsIn.nt.classList.add('on');
  }

  function dsInlineClose() {
    if (!dsIn.el) return;
    dsIn.el.classList.remove('on');
    if (dsIn.nt) dsIn.nt.classList.remove('on');
    setStatus('');
  }

  /* "Drag this edge to extend" tip: shown while the side panel is narrow, until the user widens it once
     or closes the tip; it also fades out by itself after a few seconds. */
  var DS_HINT_W = 640, dsHintT = 0, DS_HINT_KEY = 'ds_xhint_done2';
  function dsHintEl() { return dsIn.el ? dsIn.el.querySelector('.xh') : null; }
  function dsHintOff() { try { return localStorage.getItem(DS_HINT_KEY) === '1'; } catch (e) { return false; } }
  function dsHintHide() {
    var h = dsHintEl(); if (!h) return;
    clearTimeout(dsHintT);
    h.classList.remove('in');
    dsHintT = setTimeout(function () { h.classList.remove('on'); }, 400);
  }
  function dsHintDone() {
    try { localStorage.setItem(DS_HINT_KEY, '1'); } catch (e) {}
    dsHintHide();
  }
  function dsHintMaybe() {
    var h = dsHintEl(); if (!h || dsHintOff()) return;
    if (window.innerWidth >= DS_HINT_W) { dsHintDone(); return; }
    clearTimeout(dsHintT);
    h.classList.add('on');
    setTimeout(function () { h.classList.add('in'); }, 30);
    dsHintT = setTimeout(dsHintHide, 9000);
  }
  window.addEventListener('resize', function () {
    if (window.innerWidth >= DS_HINT_W && !dsHintOff() && dsIn.el) dsHintDone();
  });

  /* Signed in through the large window (Google / Microsoft): close it and show the page the sign-in was for. */
  function dsInlineAfterSignin() {
    var back = '';
    try { back = new URL(dsIn.signin, SITE_ORIGIN).searchParams.get('redirect_to') || ''; } catch (e) {}
    dsIn.signin = '';
    try { if (dialog) { dialog.close(); } } catch (e) {}
    dialog = null;
    var p = '';
    try {
      var bu = new URL(back, SITE_ORIGIN);
      if (back && bu.origin === SITE_ORIGIN && !/^\/(login|register|lostpassword|wp-login\.php|ds-sso)(\/|$)/i.test(bu.pathname)) p = bu.pathname + bu.search;
    } catch (e) {}
    dsIn.url = '';
    if (p) { openInline(p); } else { dsInlineClose(); }
    setStatus('Signed in.');
  }

  function openInline(path) {
    if (dsNoPane()) { openDialog(path); return; }
    /* PowerPoint for the web: tools always open in the large window. There the browser keeps the
       website's sign-in out of the side panel, so pages inside the panel would show as signed out. */
    if (dsIsWeb()) { openDialog(path); return; }
    if (!document.body) { openDialog(path); return; }
    dsInlineBuild();
    var url = dialogUrl(path);
    var same = false;
    try { same = dsIn.url && new URL(dsIn.url).href === new URL(url).href; } catch (e) {}
    dsIn.el.classList.add('on');
    dsHintMaybe();
    if (!same || !dsIn.fr.getAttribute('src')) {
      dsIn.url = url;
      dsIn.ttl.textContent = 'Loading\u2026';
      if (dsIn.nt) dsIn.nt.classList.remove('on');
      dsIn.el.classList.add('loading');
      dsIn.fr.setAttribute('src', url);
    }
    try { dsIn.el.querySelector('button[data-a="back"]').focus(); } catch (e) {}
  }

  /* Messages from the page shown inside the pane - same protocol as the
     large window, only from our own website and only from that frame. */
  window.addEventListener('message', function (e) {
    if (!dsIn.fr || e.source !== dsIn.fr.contentWindow) return;
    if (e.origin !== SITE_ORIGIN || typeof e.data !== 'string') return;
    onMessage({ origin: e.origin, message: e.data, inline: true });
  });

  window.DSAddin = { openDialog: openInline, openPopup: openDialog, openInline: openInline, insertFromUrl: insertFromUrl, insert: insert, setStatus: setStatus, insertMedia: insertMedia };
  window.openLibrary = openLibrary;
  try {
    if (window.Office && Office.onReady) {
      Office.onReady(function () {
        try { if (Office.actions) Office.actions.associate('openLibrary', openLibrary); } catch (e) {}
      });
    }
  } catch (e) { /* office.js unreachable: browsing still works, inserting reports it */ }
})();

DSASSET
 );
$a['bridge.js'] = array( 'application/javascript; charset=utf-8', <<<'DSASSET'
/*!
 * DesignStager Office Bridge — runs ONLY on website pages opened inside the
 * PowerPoint add-in window. It turns "Download" into "Insert into PowerPoint".
 *
 * Supported: .pptx/.potx/.ppsx (inserted as slides), .png/.jpg/.gif/.bmp
 * (inserted as pictures), .svg (inserted as editable vector), .webp/.avif
 * (converted to PNG), .zip (first .pptx inside, otherwise its images).
 *
 * For custom JS download flows your theme can call:
 *   window.DSOffice.insertUrl(url, fileName)
 *   window.DSOffice.insertBlob(blob, fileName)
 */
(function () {
  'use strict';

  var CFG = window.DSOfficeConfig || {};
  var CHUNK = 30000;            // characters per message to PowerPoint (kept small on purpose)
  var ZIP_MAX_IMAGES = 10;
  var FILE_RE = /\.(pptx|potx|ppsx|png|jpe?g|gif|bmp|svg|webp|avif|zip|mp4|m4v|mov|webm|mp3|wav|m4a|aac|ogg)(?:[?#]|$)/i;
  /* My Generations / AI tools: files kept in cloud storage are served by
     /wp-json/ds-hub/v1/file/ID (a redirect to a short-lived link, no file
     name), and some Download buttons go through /wp-json/ds/v1/download?url=. */
  var HUB_RE = /\/ds-hub\/v1\/file\/\d+/i;
  var RESTDL_RE = /\/wp-json\/ds\/v1\/download\?/i;
  function isAppFile(u) { return typeof u === 'string' && (HUB_RE.test(u) || RESTDL_RE.test(u)); }
  function innerRestUrl(u) {
    try { var x = new URL(u, location.href); if (RESTDL_RE.test(x.href) && x.searchParams.get('url')) { return new URL(x.searchParams.get('url'), location.href).href; } } catch (e) {}
    return u;
  }

  var INLINE = (function () { try { return window.parent !== window && !!window.parent.DSAddin; } catch (e) { return false; } })(); // shown inside the task pane
  var active = false;           // true once connected to PowerPoint
  var canAck = false;           // PowerPoint can report success/failure back (DialogApi 1.2)
  var busy = false;
  var waiters = {};

  /* ---------------------------------------------------------------- UI */
  var toastEl, toastTimer;
  function toast(msg, type, sticky) {
    if (!document.body) { document.addEventListener('DOMContentLoaded', function () { toast(msg, type, sticky); }); return; }
    if (!toastEl) {
      toastEl = document.createElement('div');
      toastEl.id = 'ds-office-toast';
      toastEl.setAttribute('role', 'status');
      toastEl.style.cssText = 'position:fixed;left:50%;bottom:24px;transform:translateX(-50%);z-index:2147483647;' +
        'max-width:min(560px,90vw);padding:12px 18px;border-radius:10px;font:500 14px/1.4 system-ui,-apple-system,Segoe UI,sans-serif;' +
        'box-shadow:0 8px 30px rgba(0,0,0,.25);color:#fff;transition:opacity .2s;pointer-events:none';
      document.body.appendChild(toastEl);
    }
    toastEl.style.background = type === 'error' ? '#c62828' : type === 'ok' ? '#2e7d32' : '#263238';
    toastEl.textContent = msg;
    toastEl.style.opacity = '1';
    clearTimeout(toastTimer);
    if (!sticky) toastTimer = setTimeout(function () { toastEl.style.opacity = '0'; }, type === 'error' ? 7000 : 3500);
  }

  /* ------------------------------------------------------ connection */
  function deactivate() {
    dsGuardOff = true;
    try { var n0 = window.__dsNative; if (n0) { if (n0.alert) window.alert = n0.alert; if (n0.confirm) window.confirm = n0.confirm; } } catch (e) {}
    try { sessionStorage.removeItem('ds_office'); } catch (e) {}
    document.documentElement.classList.remove('ds-office-mode');
    var st = document.getElementById('ds-office-css');
    if (st) st.remove();
  }

  function post(obj) {
    if (INLINE) { window.parent.postMessage(JSON.stringify(obj), location.origin); return; }
    Office.context.ui.messageParent(JSON.stringify(obj));
  }

  /* A question from PowerPoint about the insert (e.g. slide size): two buttons, the answer goes back. */
  function dsAskShow(m) {
    if (!document.body) return;
    var o = document.createElement('div');
    o.setAttribute('role', 'dialog'); o.setAttribute('aria-modal', 'true');
    o.style.cssText = 'position:fixed;inset:0;z-index:2147483646;background:rgba(5,8,16,.62);display:flex;align-items:center;justify-content:center;padding:16px;font-family:-apple-system,"Segoe UI",Roboto,Arial,sans-serif';
    var box = document.createElement('div');
    box.style.cssText = 'max-width:420px;width:100%;box-sizing:border-box;background:#141a2e;color:#eef1f8;border:1px solid #2a3550;border-radius:14px;padding:20px;box-shadow:0 20px 60px rgba(0,0,0,.5)';
    var p = document.createElement('p');
    p.textContent = String(m.text || '');
    p.style.cssText = 'margin:0 0 16px;font-size:14px;line-height:1.5';
    box.appendChild(p);
    var row = document.createElement('div');
    row.style.cssText = 'display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end';
    (m.choices || []).forEach(function (c, i) {
      var b = document.createElement('button');
      b.type = 'button'; b.textContent = String(c.label || c.k);
      b.style.cssText = 'padding:10px 16px;border-radius:9px;font-size:13px;font-weight:700;line-height:1.2;cursor:pointer;color:#fff;border:1px solid ' + (i === 0 ? '#f15a29' : '#3a4560') + ';background:' + (i === 0 ? '#f15a29' : 'transparent');
      b.onclick = function () { if (o.parentNode) o.parentNode.removeChild(o); try { post({ t: 'answer', id: m.id, k: c.k }); } catch (e) {} };
      row.appendChild(b);
    });
    box.appendChild(row); o.appendChild(box); document.body.appendChild(o);
    try { if (row.firstChild) row.firstChild.focus(); } catch (e) {}
  }

  function onParentMessage(arg) {
    var m;
    try { m = JSON.parse(arg.message); } catch (e) { return; }
    if (m.t === 'hello-ack') { canAck = true; return; }
    if (m.t === 'ask') { dsAskShow(m); return; }
    if (m.t === 'done' && waiters[m.id]) { waiters[m.id](m); delete waiters[m.id]; }
  }

  /* ------------------------------------------- messages inside PowerPoint
     alert() does not show in every PowerPoint window (PowerPoint on the web
     blocks it), so messages are shown on the page instead. Plan and credit
     messages get an "Open in browser" button. In Marketplace mode the site's
     own upgrade pop-ups and pricing links are replaced by the same message. */
  var DS_ACCT_RE = /\bcredits?\b|\bupgrade\b|\bpro\b|\bpremium\b|\bpaid plans?\b|\bsubscri|\bplan\b|\blimit\b|\bpricing\b/i;
  var DS_SHOP_RE = /^\/(pricing|checkout|plans|upgrade|cart)(\/|$)/i;
  var DS_UPSELL_RE = /\bupgrade\b|\bpro feature\b|\bpremium feature\b|\bpaid plans?\b|\bsee plans\b/i;
  var dsGuardOff = false, dsNoticeEl = null;

  function dsOpenInBrowser(url) {
    var u = url || (location.origin + location.pathname);
    var web = false;
    try { web = !!(window.Office && Office.context && Office.PlatformType && Office.context.platform === Office.PlatformType.OfficeOnline); } catch (e) {}
    if (active && !web) { try { post({ t: 'open', url: u }); return; } catch (e) {} }
    try { window.open(u, '_blank'); } catch (e) {}
  }

  function dsNoticeClose() {
    if (dsNoticeEl && dsNoticeEl.parentNode) dsNoticeEl.parentNode.removeChild(dsNoticeEl);
    dsNoticeEl = null;
  }

  function dsNotice(msg, acct, url) {
    if (!document.body) { document.addEventListener('DOMContentLoaded', function () { dsNotice(msg, acct, url); }); return; }
    var text = String(msg == null ? '' : msg).replace(/<[^>]*>/g, '').replace(/\s*Go to pricing\?\s*$/i, '').trim();
    if (acct === undefined) acct = DS_ACCT_RE.test(text);
    dsNoticeClose();
    function el(tag, css, txt) { var e = document.createElement(tag); e.style.cssText = css; if (txt) e.textContent = txt; return e; }
    var wrap = el('div', 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:2147483647;display:flex;align-items:center;justify-content:center;' +
      'padding:16px;background:rgba(4,7,13,.62);font:400 14px/1.5 system-ui,-apple-system,Segoe UI,sans-serif;text-align:left');
    wrap.id = 'ds-office-notice';
    wrap.setAttribute('role', 'alertdialog');
    wrap.setAttribute('aria-modal', 'true');
    wrap.setAttribute('aria-labelledby', 'ds-office-notice-t');
    var card = el('div', 'box-sizing:border-box;width:100%;max-width:420px;background:#111827;color:#f9fafb;border:1px solid #374151;' +
      'border-radius:14px;padding:22px 22px 18px;box-shadow:0 20px 60px rgba(0,0,0,.5)');
    var title = el('div', 'font-weight:700;font-size:16px;line-height:1.35;margin:0 0 8px;color:#f9fafb',
      acct ? 'You can’t use this feature right now' : 'DesignStager');
    title.id = 'ds-office-notice-t';
    card.appendChild(title);
    card.appendChild(el('div', 'font-size:14px;color:#d1d5db;margin:0 0 6px;white-space:pre-line;word-break:break-word',
      text || 'This isn’t included in your current plan or credit balance.'));
    if (acct) card.appendChild(el('div', 'font-size:13px;color:#9ca3af;margin:0', 'Open DesignStager in your browser to manage your account.'));
    var row = el('div', 'display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;margin-top:18px');
    var BTN = 'font:600 13px/1 system-ui,-apple-system,Segoe UI,sans-serif;padding:10px 16px;border-radius:8px;cursor:pointer;text-transform:none!important;letter-spacing:normal!important;';
    var ok = el('button', BTN + 'background:transparent;color:#e5e7eb;border:1px solid #4b5563', acct ? 'Close' : 'OK');
    ok.type = 'button';
    ok.addEventListener('click', dsNoticeClose);
    var go = null;
    if (acct) {
      go = el('button', BTN + 'background:#2563eb;color:#fff;border:1px solid #2563eb', 'Open in browser');
      go.type = 'button';
      go.addEventListener('click', function () { dsOpenInBrowser(url); dsNoticeClose(); });
      row.appendChild(ok); row.appendChild(go);
    } else {
      row.appendChild(ok);
    }
    card.appendChild(row);
    wrap.appendChild(card);
    wrap.addEventListener('click', function (e) { if (e.target === wrap) dsNoticeClose(); });
    wrap.addEventListener('keydown', function (e) { if (e.key === 'Escape') dsNoticeClose(); });
    document.body.appendChild(wrap);
    dsNoticeEl = wrap;
    try { (go || ok).focus(); } catch (e) {}
  }

  function dsAlert(msg) { dsNotice(msg); }
  function dsConfirm(msg) {
    var s = String(msg == null ? '' : msg), nat = window.__dsNative;
    if (CFG.noUpsell && /\bpricing\b|\bupgrade\b/i.test(s)) { dsNotice(s, true, location.origin + '/pricing/'); return false; }
    return (nat && nat.confirm) ? nat.confirm.call(window, s) : false;
  }

  function dsShopUrl(href) {
    try { var u = new URL(href, location.href); return (u.origin === location.origin && DS_SHOP_RE.test(u.pathname)) ? u.origin + u.pathname : ''; }
    catch (e) { return ''; }
  }

  function dsPendingNotice() {
    var p = '';
    try { p = sessionStorage.getItem('ds_office_acct') || ''; if (p) sessionStorage.removeItem('ds_office_acct'); } catch (e) {}
    if (p) dsNotice('', true, p);
  }

  /* A site pop-up that only sells an upgrade: full-screen, short, at most a
     few links, with a pricing link (or the builder's own upgrade button). */
  function dsScanUpsell() {
    var marks = document.querySelectorAll('a[href*="/pricing"],a[href*="/plans"],a[href*="/checkout"],a[href*="/upgrade"],#dsp-upg-go');
    for (var i = 0; i < marks.length; i++) {
      var mk = marks[i], isA = mk.tagName === 'A', dest = isA ? dsShopUrl(mk.href) : location.origin + '/pricing/';
      if (!dest) continue;
      var box = null, n = mk.parentElement;
      while (n && n !== document.body && n !== document.documentElement) {
        var cs = getComputedStyle(n);
        if (cs.display === 'none' || cs.visibility === 'hidden' || parseFloat(cs.opacity) < 0.05 || cs.pointerEvents === 'none') { box = null; break; }
        if (cs.position === 'fixed') {
          var r = n.getBoundingClientRect();
          if (r.width >= window.innerWidth * 0.7 && r.height >= window.innerHeight * 0.7 && r.left < window.innerWidth && r.top < window.innerHeight && r.right > 0 && r.bottom > 0) box = n;
        }
        n = n.parentElement;
      }
      if (!box || box.id === 'ds-office-notice') continue;
      var t = (box.textContent || '').replace(/\s+/g, ' ').trim();
      if (t.length > 800 || box.querySelectorAll('a[href]').length > 3 || !DS_UPSELL_RE.test(t)) continue;
      box.style.setProperty('display', 'none', 'important');
      dsNotice('', true, dest);
      return;
    }
  }

  function dsAccountGuard() {
    if (!CFG.noUpsell) return;
    /* Landed on a pricing / checkout page inside PowerPoint: step back and say why. */
    if (DS_SHOP_RE.test(location.pathname)) {
      var last = 0;
      try {
        last = parseInt(sessionStorage.getItem('ds_office_acct_t') || '0', 10) || 0;
        sessionStorage.setItem('ds_office_acct', location.origin + location.pathname);
        sessionStorage.setItem('ds_office_acct_t', String(Date.now()));
      } catch (e) {}
      var ref = document.referrer || '';
      if (ref.indexOf(location.origin + '/') === 0 && !dsShopUrl(ref) && history.length > 1 && Date.now() - last > 5000) history.back();
      else location.replace('/?ds_office=1');
      return;
    }
    dsPendingNotice();
    window.addEventListener('pageshow', dsPendingNotice);
    document.addEventListener('click', function (e) {
      var a = e.target && e.target.closest && e.target.closest('a[href]');
      var dest = a ? dsShopUrl(a.href) : '';
      if (!dest) return;
      e.preventDefault();
      e.stopPropagation();
      dsNotice('', true, dest);
    }, true);
    var timer = null;
    function later() { if (!timer) timer = setTimeout(function () { timer = null; try { dsScanUpsell(); } catch (e) {} }, 80); }
    try {
      new MutationObserver(later).observe(document.documentElement, { childList: true, subtree: true, attributes: true, attributeFilter: ['style', 'class', 'hidden', 'open'] });
    } catch (e) {}
    later();
  }

  // Office's script switches off alert()/confirm()/prompt() inside its windows, which breaks
  // pages that use them (e.g. the AI Presentation generator). Put the browser's own versions back.
  function restoreDialogs() {
    var nat = window.__dsNative;
    if (!nat) return;
    try {
      if (dsGuardOff) { if (nat.alert) window.alert = nat.alert; } else if (window.alert !== dsAlert) window.alert = dsAlert;
      if (dsGuardOff) { if (nat.confirm) window.confirm = nat.confirm; } else if (window.confirm !== dsConfirm) window.confirm = dsConfirm;
      if (nat.prompt && window.prompt !== nat.prompt) window.prompt = nat.prompt;
    } catch (e) {}
  }
  restoreDialogs();

  function start() {
    restoreDialogs();
    setTimeout(restoreDialogs, 0);
    setTimeout(restoreDialogs, 1000);
    var ui = window.Office && Office.context && Office.context.ui;
    if (!INLINE && (!ui || typeof ui.messageParent !== 'function')) { deactivate(); return; } // opened in a normal browser
    if (INLINE && !start.__l) {
      start.__l = true;
      /* Editor in the side panel: the Layouts strip opens from a LAYOUTS button (closed again after a pick),
         and the slide thumbnails are kept about 60px tall whatever the slide shape, so the slide itself has room. */
      (function () {
        var R = document.documentElement, MQ = window.matchMedia ? window.matchMedia('(max-width:1024px)') : null;
        function layClose() { R.classList.remove('dsx-lay-open'); var b = document.getElementById('dsx-lay-btn'); if (b) b.setAttribute('aria-expanded', 'false'); }
        function layTick() {
          var fs = document.getElementById('dsp-editor-fullscreen');
          if (!fs || !fs.classList.contains('dsp-open') || (MQ && !MQ.matches)) { if (R.classList.contains('dsx-lay-open')) layClose(); return; }
          var ms = document.getElementById('dsp-ppt-main-slide');
          if (ms && ms.offsetWidth && ms.offsetHeight) {
            var cw = Math.max(40, Math.min(120, Math.round(60 * ms.offsetWidth / ms.offsetHeight))) + 'px';
            if (fs.style.getPropertyValue('--dsmb-cw') !== cw) fs.style.setProperty('--dsmb-cw', cw);
          }
          var bar = document.getElementById('dsp-ppt-bottom-bar');
          if (bar && !document.getElementById('dsx-lay-btn')) {
            var b = document.createElement('button');
            b.type = 'button'; b.id = 'dsx-lay-btn'; b.textContent = 'LAYOUTS';
            b.title = 'Choose a layout for this slide';
            b.setAttribute('aria-expanded', 'false');
            b.addEventListener('click', function (e) {
              e.preventDefault(); e.stopPropagation();
              if (R.classList.contains('dsx-lay-open')) { layClose(); return; }
              R.classList.add('dsx-lay-open'); b.setAttribute('aria-expanded', 'true');
            });
            bar.appendChild(b);
          }
        }
        document.addEventListener('click', function (e) {
          if (!R.classList.contains('dsx-lay-open')) return;
          var c = e.target && e.target.closest ? e.target.closest('#dsp-vp-panel .vp-card') : null;
          if (c) setTimeout(layClose, 350);
        }, true);
        setInterval(layTick, 500);
      })();
      /* Google / Microsoft sign-in refuse to open inside the side panel: hand the click to the panel,
         which runs the sign-in in the large PowerPoint window. */
      document.addEventListener('click', function (e) {
        var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
        if (!a) return;
        var u;
        try { u = new URL(a.getAttribute('href'), location.href); } catch (x) { return; }
        if (u.origin !== location.origin || !/^\/ds-sso\//i.test(u.pathname)) return;
        e.preventDefault();
        e.stopPropagation();
        u.searchParams.delete('ds_office');
        try { post({ t: 'signin', path: u.pathname + u.search }); } catch (x) {}
      }, true);
      window.addEventListener('message', function (e) {
        if (e.source === window.parent && e.origin === location.origin && typeof e.data === 'string') onParentMessage({ message: e.data });
      });
    }
    active = true;
    try { dsAccountGuard(); } catch (e) {}
    try {
      if (ui.addHandlerAsync && Office.EventType && Office.EventType.DialogParentMessageReceived) {
        ui.addHandlerAsync(Office.EventType.DialogParentMessageReceived, onParentMessage);
      }
    } catch (e) {}
    try {
      fetch('/ds-office/token', { credentials: 'include', cache: 'no-store' })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (d) { post({ t: 'token', token: (d && d.token) || '' }); })
        .catch(function () {});
    } catch (e) {}
    try { post({ t: 'hello', page: location.href }); } catch (e) { active = false; deactivate(); return; }
    /* No "connected" toast: bridge.js re-activates on every page load inside the
       PowerPoint window, so this fired again on every single navigation. The
       relabelled Insert buttons already show the user the add-in is live. */
    relabel();
  }

  /* ------------------------- show "Insert" instead of "Download" in PowerPoint */
  function isAudioView() { return false; } // audio & video are inserted too (as a new slide)
  function relabel() {
    if (!document.body) { document.addEventListener('DOMContentLoaded', relabel); return; }
    var st = document.createElement('style');
    st.id = 'ds-office-insert-style';
    st.textContent = 'html.ds-office-mode:not(.ds-office-audio) .ds-mkt-dl-btn svg{transform:rotate(90deg);transition:transform .15s}' +
      // Label is swapped visually only: the real text stays "Download …" because the site's own
      // scripts look for it (e.g. the AI Presentation editor shows its Theme button only then).
      'html.ds-office-mode .ds-ins-label>*{display:none!important}' +
      'html.ds-office-mode .ds-ins-label::after{content:attr(data-ds-label)}';
    document.head.appendChild(st);
    var fix = function () {
      document.documentElement.classList.toggle('ds-office-audio', isAudioView());
      /* the editor's export menu inserts here, it does not download */
      var mh = document.querySelector('#dsx-menu .dsx-h');
      if (mh && mh.textContent !== 'Insert as') mh.textContent = 'Insert as';
      var md = document.querySelector('#dsx-menu button[data-k="pptx"] .dsx-d');
      if (md && /Keynote/.test(md.textContent)) md.textContent = 'Editable slides, added to this presentation';
      if (isAudioView()) return;
      var els = document.querySelectorAll('.ds-mkt-dl-btn, a[download], button, a[href]');
      for (var i = 0; i < els.length; i++) {
        var el = els[i];
        if (el.classList.contains('ds-mkt-dl-btn')) {
          if (el.getAttribute('title') !== 'Insert into PowerPoint') { el.setAttribute('title', 'Insert into PowerPoint'); el.setAttribute('aria-label', 'Insert into PowerPoint'); }
          continue;
        }
        var tx = (el.textContent || '').trim();
        /* the export button keeps a hidden "Download PPTX" for other scripts: not part of the label */
        var lg = el.querySelector ? el.querySelector('.dsx-legacy') : null;
        if (lg) tx = tx.replace((lg.textContent || '').trim(), '').trim();
        if (tx.length > 40 || !/^[^A-Za-z0-9]{0,3}download\b(?!s)/i.test(tx) || el.children.length > 3) continue;
        var label = tx.replace(/\u2B07\uFE0F?|\u2193/g, '\u21E4').replace(/Download/g, 'Insert').replace(/download/g, 'insert');
        if (!/^[\u21E4]/.test(label)) label = '\u21E4 ' + label;
        // wrap loose text so CSS can hide it (textContent stays exactly the same)
        for (var c = el.firstChild; c; c = c.nextSibling) {
          if (c.nodeType === 3 && c.nodeValue.trim()) {
            var sp = document.createElement('span');
            sp.className = 'ds-orig';
            el.replaceChild(sp, c);
            sp.appendChild(c);
            c = sp;
          }
        }
        if (el.getAttribute('data-ds-label') !== label) {
          el.setAttribute('data-ds-label', label);
          el.classList.add('ds-ins-label');
          el.setAttribute('title', 'Insert into PowerPoint');
        }
      }
    };
    fix();
    var pending = false;
    new MutationObserver(function () {
      if (pending) return;
      pending = true;
      setTimeout(function () { pending = false; fix(); }, 150);
    }).observe(document.body, { childList: true, subtree: true, characterData: true });
    window.addEventListener('popstate', fix);
  }

  if (INLINE) start(); else if (window.Office && Office.onReady) Office.onReady(start); else deactivate();

  /* ------------------------------------------------------- helpers */
  function sleep(ms) { return new Promise(function (r) { setTimeout(r, ms); }); }

  function extOf(name) { var m = /\.([a-z0-9]+)$/i.exec((name || '').split(/[?#]/)[0]); return m ? m[1].toLowerCase() : ''; }

  function nameFromUrl(url) {
    try { var p = new URL(url, location.href).pathname; return decodeURIComponent(p.substring(p.lastIndexOf('/') + 1)); } catch (e) { return ''; }
  }

  function nameFromDisposition(h) {
    if (!h) return '';
    var m = /filename\*=(?:UTF-8'')?([^;]+)/i.exec(h) || /filename="?([^";]+)"?/i.exec(h);
    try { return m ? decodeURIComponent(m[1].trim()) : ''; } catch (e) { return m ? m[1].trim() : ''; }
  }

  var MIME_EXT = {
    'image/png': 'png', 'image/jpeg': 'jpg', 'image/gif': 'gif', 'image/bmp': 'bmp', 'image/svg+xml': 'svg',
    'image/webp': 'webp', 'image/avif': 'avif', 'application/zip': 'zip', 'application/x-zip-compressed': 'zip',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'pptx',
    'application/vnd.openxmlformats-officedocument.presentationml.template': 'potx',
    'application/vnd.openxmlformats-officedocument.presentationml.slideshow': 'ppsx',
    'video/mp4': 'mp4', 'video/webm': 'webm', 'video/quicktime': 'mov',
    'audio/mpeg': 'mp3', 'audio/mp3': 'mp3', 'audio/wav': 'wav', 'audio/x-wav': 'wav', 'audio/wave': 'wav',
    'audio/mp4': 'm4a', 'audio/x-m4a': 'm4a', 'audio/aac': 'aac', 'audio/ogg': 'ogg'
  };

  function kindOf(ext) {
    if (/^(pptx|potx|ppsx)$/.test(ext)) return 'pptx';
    if (/^(png|jpe?g|gif|bmp)$/.test(ext)) return 'image';
    if (ext === 'svg') return 'svg';
    if (/^(webp|avif)$/.test(ext)) return 'convert';
    if (ext === 'zip') return 'zip';
    if (MEDIA[ext]) return 'media';
    return '';
  }

  /* ------------------------------------------------ audio / video -> new slide */
  var MEDIA = {
    mp4: ['video', 'video/mp4'], m4v: ['video', 'video/mp4'], mov: ['video', 'video/quicktime'],
    webm: ['video', 'video/webm'], wmv: ['video', 'video/x-ms-wmv'], avi: ['video', 'video/x-msvideo'],
    mp3: ['audio', 'audio/mpeg'], wav: ['audio', 'audio/wav'], m4a: ['audio', 'audio/mp4'],
    aac: ['audio', 'audio/aac'], ogg: ['audio', 'audio/ogg'], wma: ['audio', 'audio/x-ms-wma']
  };
  var MAX_MEDIA_BYTES = 60 * 1024 * 1024;

  function xmlEsc(s) { return String(s).replace(/[<>&"']/g, function (c) { return { '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;', "'": '&apos;' }[c]; }); }

  function canvasPng(c) { var s = c.toDataURL('image/png'); return s.substring(s.indexOf(',') + 1); }

  function audioPoster() {
    var c = document.createElement('canvas'); c.width = c.height = 512;
    var g = c.getContext('2d');
    g.fillStyle = '#1f2937'; g.beginPath(); g.arc(256, 256, 250, 0, Math.PI * 2); g.fill();
    g.fillStyle = '#ffffff';
    g.beginPath(); g.moveTo(130, 210); g.lineTo(190, 210); g.lineTo(265, 140); g.lineTo(265, 372); g.lineTo(190, 302); g.lineTo(130, 302); g.closePath(); g.fill();
    g.strokeStyle = '#ffffff'; g.lineWidth = 22; g.lineCap = 'round';
    g.beginPath(); g.arc(270, 256, 60, -0.8, 0.8); g.stroke();
    g.beginPath(); g.arc(270, 256, 110, -0.8, 0.8); g.stroke();
    return Promise.resolve({ b64: canvasPng(c), w: 512, h: 512 });
  }

  function videoPoster(blob) {
    return new Promise(function (resolve) {
      var done = false, url = URL.createObjectURL(blob), v = document.createElement('video');
      var fallback = function () {
        if (done) return; done = true; try { URL.revokeObjectURL(url); } catch (e) {}
        var c = document.createElement('canvas'); c.width = 1280; c.height = 720;
        var g = c.getContext('2d'); g.fillStyle = '#000'; g.fillRect(0, 0, 1280, 720);
        g.fillStyle = 'rgba(255,255,255,.85)'; g.beginPath(); g.moveTo(590, 300); g.lineTo(590, 420); g.lineTo(700, 360); g.closePath(); g.fill();
        resolve({ b64: canvasPng(c), w: 1280, h: 720 });
      };
      setTimeout(fallback, 8000);
      v.muted = true; v.preload = 'auto'; v.playsInline = true;
      v.onerror = fallback;
      v.onloadeddata = function () { try { v.currentTime = Math.min(1, (v.duration || 2) / 2); } catch (e) { fallback(); } };
      v.onseeked = function () {
        if (done) return;
        try {
          var w = v.videoWidth || 1280, h = v.videoHeight || 720, k = Math.min(1, 1280 / w);
          var c = document.createElement('canvas'); c.width = Math.round(w * k); c.height = Math.round(h * k);
          c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
          done = true; URL.revokeObjectURL(url);
          resolve({ b64: canvasPng(c), w: c.width, h: c.height });
        } catch (e) { fallback(); }
      };
      v.src = url;
    });
  }

  // Builds a one-slide presentation that contains the audio/video, so PowerPoint can insert it as a new slide.
  async function mediaSlideBase64(blob, name, ext) {
    if (blob.size > MAX_MEDIA_BYTES) throw new Error('This file is larger than 60 MB — too big to insert through the add-in.');
    if (!CFG.mediaTemplate) throw new Error('Media template missing — please update the DesignStager snippet.');
    var JSZip = await loadJSZip();
    var tr = await fetch(CFG.mediaTemplate, { credentials: 'omit' });
    if (!tr.ok) throw new Error('Could not load the media slide template.');
    var zip = await JSZip.loadAsync(await tr.arrayBuffer());
    var info = MEDIA[ext], isAudio = info[0] === 'audio';
    var poster = isAudio ? await audioPoster() : await videoPoster(blob);
    var mediaName = 'media1.' + ext;
    zip.file('ppt/media/' + mediaName, blob, { createFolders: false });
    zip.file('ppt/media/image1.png', poster.b64, { base64: true, createFolders: false });
    var ct = await zip.file('[Content_Types].xml').async('string');
    if (!new RegExp('Extension="' + ext + '"', 'i').test(ct)) {
      ct = ct.replace('<Default Extension="png"', '<Default Extension="' + ext + '" ContentType="' + info[1] + '"/><Default Extension="png"');
    }
    zip.file('[Content_Types].xml', ct, { createFolders: false });
    var rels = (await zip.file('ppt/slides/_rels/slide1.xml.rels').async('string')).replace(/media1\.mp4/g, mediaName);
    if (isAudio) rels = rels.replace('/officeDocument/2006/relationships/video"', '/officeDocument/2006/relationships/audio"');
    zip.file('ppt/slides/_rels/slide1.xml.rels', rels, { createFolders: false });
    var sx = await zip.file('ppt/slides/slide1.xml').async('string');
    sx = sx.replace('name="tiny.mp4"', 'name="' + xmlEsc(name || mediaName) + '"');
    if (isAudio) sx = sx.replace('<a:videoFile ', '<a:audioFile ').replace(/<p:video>/g, '<p:audio>').replace(/<\/p:video>/g, '</p:audio>');
    var SW = 12192000, SH = 6858000;
    var maxW = isAudio ? 1371600 : SW * 0.8, maxH = isAudio ? 1371600 : SH * 0.8;
    var k = Math.min(maxW / poster.w, maxH / poster.h);
    var cx = Math.round(poster.w * k), cy = Math.round(poster.h * k);
    sx = sx.replace(/<a:off x="\d+" y="\d+"\/><a:ext cx="\d+" cy="\d+"\/>/, '<a:off x="' + Math.round((SW - cx) / 2) + '" y="' + Math.round((SH - cy) / 2) + '"/><a:ext cx="' + cx + '" cy="' + cy + '"/>');
    zip.file('ppt/slides/slide1.xml', sx, { createFolders: false });
    return zip.generateAsync({ type: 'base64', compression: 'DEFLATE', compressionOptions: { level: 1 } });
  }

  function blobToBase64(blob) {
    return new Promise(function (resolve, reject) {
      var r = new FileReader();
      r.onload = function () { var s = String(r.result); resolve(s.substring(s.indexOf(',') + 1)); };
      r.onerror = function () { reject(r.error); };
      r.readAsDataURL(blob);
    });
  }

  function imageBlobToPngBase64(blob) {
    return new Promise(function (resolve, reject) {
      var url = URL.createObjectURL(blob), img = new Image();
      img.onload = function () {
        var c = document.createElement('canvas');
        c.width = img.naturalWidth; c.height = img.naturalHeight;
        c.getContext('2d').drawImage(img, 0, 0);
        URL.revokeObjectURL(url);
        var s = c.toDataURL('image/png'); resolve(s.substring(s.indexOf(',') + 1));
      };
      img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('Could not read this image.')); };
      img.src = url;
    });
  }

  // Very large pictures (e.g. 40 MB PNGs) are scaled to a slide-friendly size first.
  var MAX_IMAGE_BYTES = 2 * 1024 * 1024, MAX_SIDE = 2400, MAX_B64 = 3500000;
  function shrinkImage(blob, side) {
    return new Promise(function (resolve, reject) {
      var url = URL.createObjectURL(blob), img = new Image();
      img.onload = function () {
        var w = img.naturalWidth, h = img.naturalHeight, k = Math.min(1, (side || MAX_SIDE) / Math.max(w, h));
        var c = document.createElement('canvas');
        c.width = Math.round(w * k); c.height = Math.round(h * k);
        c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
        URL.revokeObjectURL(url);
        var type = /png|gif|webp|avif/i.test(blob.type) ? 'image/png' : 'image/jpeg';
        var s = c.toDataURL(type, 0.92); resolve(s.substring(s.indexOf(',') + 1));
      };
      img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('Could not read this image.')); };
      img.src = url;
    });
  }

  var jszipPromise;
  function loadJSZip() {
    if (window.JSZip) return Promise.resolve(window.JSZip);
    if (!jszipPromise) {
      jszipPromise = new Promise(function (resolve, reject) {
        var s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
        s.onload = function () { resolve(window.JSZip); };
        s.onerror = function () { reject(new Error('Could not open the ZIP file.')); };
        document.head.appendChild(s);
      });
    }
    return jszipPromise;
  }

  /* -------------------------------------------- send to PowerPoint */
  function sendFile(kind, name, data) {
    var id = Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
    var total = Math.max(1, Math.ceil(data.length / CHUNK));
    var done = canAck
      ? new Promise(function (resolve) {
          waiters[id] = resolve;
          setTimeout(function () { if (waiters[id]) { delete waiters[id]; resolve({ ok: true, unconfirmed: true }); } }, 180000);
        })
      : null;
    return (async function () {
      post({ t: 'begin', id: id, kind: kind, name: name, total: total });
      for (var i = 0; i < total; i++) {
        post({ t: 'chunk', id: id, i: i, d: data.substr(i * CHUNK, CHUNK) });
        if (i % 25 === 24) await sleep(0);
      }
      post({ t: 'end', id: id });
      var res = done ? await done : { ok: true, unconfirmed: true };
      if (!res.ok) throw new Error(res.error || 'PowerPoint could not insert this file.');
      return res;
    })();
  }

  /* Cloud-storage files often come with no file name and a generic type
     (application/octet-stream), so read the first bytes to tell what they are.
     Without this they were not recognised and were saved to Downloads. */
  async function sniffExt(blob) {
    try {
      var b = new Uint8Array(await blob.slice(0, 16).arrayBuffer());
      var s4 = String.fromCharCode(b[0], b[1], b[2], b[3]), s8 = String.fromCharCode(b[8], b[9], b[10], b[11]);
      if (b[0] === 0x89 && s4.slice(1) === 'PNG') return 'png';
      if (b[0] === 0xFF && b[1] === 0xD8) return 'jpg';
      if (s4 === 'GIF8') return 'gif';
      if (s4 === 'RIFF' && s8 === 'WEBP') return 'webp';
      if (s4 === 'RIFF' && s8 === 'WAVE') return 'wav';
      if (String.fromCharCode(b[4], b[5], b[6], b[7]) === 'ftyp') {
        var brand = String.fromCharCode(b[8], b[9], b[10], b[11]);
        if (/^(M4A |M4B )/.test(brand)) return 'm4a';
        if (/^qt/.test(brand)) return 'mov';
        return 'mp4';
      }
      if (b[0] === 0x1A && b[1] === 0x45 && b[2] === 0xDF && b[3] === 0xA3) return 'webm';
      if (s4 === 'OggS') return 'ogg';
      if (s4.slice(0, 3) === 'ID3' || (b[0] === 0xFF && (b[1] & 0xE0) === 0xE0)) return 'mp3';
      if (b[0] === 0x50 && b[1] === 0x4B) return 'zip';
    } catch (e) {}
    return '';
  }

  async function sendBlob(blob, name) {
    var ext = extOf(name) || MIME_EXT[(blob.type || '').split(';')[0]] || '';
    if (!kindOf(ext)) {
      var sn = await sniffExt(blob);
      if (sn) {
        ext = sn;
        name = (name && /\.[a-z0-9]{1,5}$/i.test(name) ? name.replace(/\.[a-z0-9]{1,5}$/i, '') : (name || 'designstager-file')) + '.' + sn;
      }
    }
    var kind = kindOf(ext);
    if (kind === 'media') return sendFile('pptx', (name || 'media').replace(/\.[a-z0-9]+$/i, '') + '.pptx', await mediaSlideBase64(blob, name, ext));
    if (!kind) {
      saveNormally(blob, name);
      throw new Error((/^(mp3|wav|m4a|aac|ogg|flac|mp4|mov|webm|avi|mkv)$/.test(ext) ? 'This audio/video format can\'t be placed on a slide' : '"' + (name || 'This file') + '" can\'t be placed on a slide') + ' — it was saved to your Downloads instead.');
    }

    if (kind === 'zip') {
      var JSZip = await loadJSZip();
      var zip = await JSZip.loadAsync(blob);
      /* A PowerPoint file is itself a zip: a nameless one lands here. */
      if (zip.files['ppt/presentation.xml']) {
        return sendFile('pptx', (name || 'slides').replace(/\.[a-z0-9]{1,5}$/i, '') + '.pptx', await blobToBase64(blob));
      }
      var entries = Object.keys(zip.files).map(function (k) { return zip.files[k]; })
        .filter(function (f) { return !f.dir && !/(^|\/)(__MACOSX|\.)/.test(f.name); });
      var deck = entries.filter(function (f) { return kindOf(extOf(f.name)) === 'pptx'; })[0];
      if (deck) return sendBlob(await deck.async('blob'), deck.name.split('/').pop());
      var imgs = entries.filter(function (f) { var k = kindOf(extOf(f.name)); return k === 'image' || k === 'svg' || k === 'convert'; }).slice(0, ZIP_MAX_IMAGES);
      if (!imgs.length) throw new Error('No slides or images found inside this ZIP.');
      for (var j = 0; j < imgs.length; j++) {
        var n = imgs[j].name.split('/').pop();
        var t = extOf(n) === 'svg' ? 'image/svg+xml' : '';
        await sendBlob(new Blob([await imgs[j].async('arraybuffer')], t ? { type: t } : {}), n);
      }
      return;
    }
    if (kind === 'svg') return sendFile('svg', name, await blob.text());
    if (kind === 'image' || kind === 'convert') {
      var data = (blob.size > MAX_IMAGE_BYTES) ? await shrinkImage(blob) : (kind === 'convert' ? await imageBlobToPngBase64(blob) : await blobToBase64(blob));
      for (var side = 1800; data.length > MAX_B64 && side >= 600; side -= 400) data = await shrinkImage(blob, side);
      return sendFile('image', name, data);
    }
    if (kind === 'pptx') {
      var tail = new Uint8Array(await blob.slice(Math.max(0, blob.size - 66000)).arrayBuffer()), ok = false;
      for (var t = tail.length - 22; t >= 0; t--) { if (tail[t] === 80 && tail[t + 1] === 75 && tail[t + 2] === 5 && tail[t + 3] === 6) { ok = true; break; } }
      if (!ok) throw new Error('This PowerPoint file looks damaged or incomplete, so it could not be inserted.');
    }
    return sendFile(kind, name, await blobToBase64(blob));
  }

  async function run(task) {
    if (busy) { toast('Please wait — still inserting the previous file…'); return; }
    busy = true;
    toast('Inserting into PowerPoint…', 'info', true);
    try {
      await task();
      toast('Inserted into your presentation ✓', 'ok');
    } catch (err) {
      console.error('[DSOffice]', err);
      toast(err && err.message ? err.message : 'Something went wrong.', 'error');
    } finally {
      busy = false;
    }
  }

  var PATTERNS = (CFG.patterns || []).filter(Boolean);
  function matchesPattern(url) {
    for (var i = 0; i < PATTERNS.length; i++) if (url.indexOf(PATTERNS[i]) !== -1) return true;
    return false;
  }
  function sameSite(url) { try { return new URL(url, location.href).origin === location.origin; } catch (e) { return false; } }
  function proxyAllowed(url) {
    try {
      var u = new URL(url, location.href);
      return u.protocol === 'https:' && (u.origin === location.origin || (CFG.proxyHosts || []).indexOf(u.hostname.toLowerCase()) !== -1);
    } catch (e) { return false; }
  }
  function proxyUrl(url) { return CFG.proxy + (CFG.proxy.indexOf('?') === -1 ? '?' : '&') + 'url=' + encodeURIComponent(url); }

  async function download(url) {
    // Cloud-storage files (My Generations): the server looks the file up for the
    // signed-in user and fetches it, since the REST route needs a nonce and
    // redirects to another domain.
    if (CFG.proxy && sameSite(url) && HUB_RE.test(url)) {
      return fetch(proxyUrl(url), { credentials: 'include' });
    }
    // Download-manager links (e.g. /download/123/) usually redirect to the real file,
    // which may live on another domain. Let the server fetch those for us.
    if (CFG.proxy && sameSite(url) && matchesPattern(url) && !FILE_RE.test(url)) {
      return fetch(proxyUrl(url), { credentials: 'include' });
    }
    try {
      return await fetch(url, { credentials: 'include' });
    } catch (e) {
      if (CFG.proxy && proxyAllowed(url)) return fetch(proxyUrl(url), { credentials: 'include' });
      throw new Error('Could not download the file. If files are served from another domain/CDN, it must allow CORS for this site.');
    }
  }

  function insertUrl(url, name) {
    url = innerRestUrl(new URL(url, location.href).href);
    return run(async function () {
      var res = await download(url);
      if (!res.ok) {
        var t = ''; try { t = (await res.text()).slice(0, 480); } catch (e) {}
        var lim = ''; try { lim = res.headers.get('x-ds-limit') || ''; } catch (e) {}
        if (lim || res.status === 429) {
          var le = new Error(lim === 'plan' ? 'Not included in your plan.' : (lim === 'month' ? 'Monthly download limit reached.' : 'Daily download limit reached.'));
          dsNotice(t && !/</.test(t) ? t : 'You have reached your download limit for now. This is a plan limit, not an error.', true);
          throw le;
        }
        throw new Error(t && !/</.test(t) ? t : 'Download failed (HTTP ' + res.status + ').');
      }
      var blob = new Blob([await res.arrayBuffer()], { type: ((res.headers.get('content-type') || '').split(';')[0]) });
      var fname = name || nameFromDisposition(res.headers.get('content-disposition')) || nameFromUrl(res.url || url);
      if (!kindOf(extOf(fname))) {
        var ext = MIME_EXT[(blob.type || '').split(';')[0]];
        if (ext) fname = (fname || 'file').replace(/\.[a-z0-9]{1,5}$/i, '') + '.' + ext;
      }
      if (/text\/html/i.test(blob.type) && extOf(fname) !== 'svg') {
        throw new Error('The link returned a web page instead of a file — please log in, or check that this button links to the file.');
      }
      await sendBlob(blob, fname);
    });
  }

  function insertBlob(blob, name) {
    return run(function () { return sendBlob(blob, name || 'file'); });
  }

  window.DSOffice = { insertUrl: insertUrl, insertBlob: insertBlob, isActive: function () { return active; } };

  /* ------------------------------------------- catch download clicks */
  var extraSel = (CFG.selectors || '').trim();

  function urlFor(el) {
    return el.getAttribute('data-ds-insert') || el.getAttribute('data-download-url') || el.getAttribute('data-url') || el.href || el.getAttribute('href') || '';
  }

  function isDownload(el, url) {
    if (!url || /^(javascript:|#|mailto:|tel:)/i.test(url)) return false;
    if (el.hasAttribute('data-ds-insert')) return true;
    if (el.tagName === 'A' && el.hasAttribute('download')) return true;
    if (/^(blob|data):/i.test(url)) return true;
    if (FILE_RE.test(url)) return true;
    if (isAppFile(url)) return true;
    try { if (extraSel && el.matches(extraSel)) return true; } catch (e) {}
    return false;
  }

  document.addEventListener('click', function (e) {
    if (!active || e.defaultPrevented) return;
    var sel = 'a[href],[data-ds-insert]' + (extraSel ? ',' + extraSel : '');
    var el;
    try { el = e.target.closest && e.target.closest(sel); } catch (err) { el = e.target.closest && e.target.closest('a[href],[data-ds-insert]'); }
    if (!el) return;
    var url = urlFor(el);
    if (!isDownload(el, url)) return;
    e.preventDefault();
    e.stopImmediatePropagation();
    insertUrl(url, (el.getAttribute && el.getAttribute('download')) || '');
  }, true);

  // Downloads started by JavaScript (a.click(), FileSaver.js, window.open(file)).
  function interceptAnchor(a) {
    var url = a.href;
    if (!active || !url || !(a.hasAttribute('download') || /^(blob|data):/i.test(url) || FILE_RE.test(url) || isAppFile(url))) return false;
    insertUrl(url, a.getAttribute('download') || '');
    return true;
  }
  var aClick = HTMLAnchorElement.prototype.click;
  function saveNormally(blob, name) {
    try { var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = name || 'download'; aClick.call(a); } catch (e) {}
  }
  HTMLAnchorElement.prototype.click = function () { if (!interceptAnchor(this)) return aClick.apply(this, arguments); };
  var aDispatch = EventTarget.prototype.dispatchEvent;
  HTMLAnchorElement.prototype.dispatchEvent = function (ev) {
    if (ev && ev.type === 'click' && !this.isConnected && interceptAnchor(this)) return false;
    return aDispatch.apply(this, arguments);
  };
  var wOpen = window.open;
  window.open = function (u) {
    if (active && typeof u === 'string' && (FILE_RE.test(u) || /^blob:/i.test(u) || matchesPattern(u) || isAppFile(u))) { insertUrl(new URL(u, location.href).href, ''); return null; }
    return wOpen.apply(window, arguments);
  };
  // Downloads started with window.location.href = '/download/123/' (e.g. after a
  // credit / limit check). The site's own logic runs first; only the final
  // navigation to the file is turned into "insert".
  if (window.navigation && typeof window.navigation.addEventListener === 'function') {
    window.navigation.addEventListener('navigate', function (e) {
      if (!active || !e.cancelable || e.hashChange || (e.destination && e.destination.sameDocument)) return;
      var u = e.destination && e.destination.url;
      if (!u || !(FILE_RE.test(u) || matchesPattern(u) || isAppFile(u))) return;
      e.preventDefault();
      insertUrl(u, '');
    });
  }

  // Some libraries revoke blob URLs immediately after "downloading"; keep them alive long enough to read.
  var revoke = URL.revokeObjectURL;
  URL.revokeObjectURL = function (u) { if (!active) return revoke.call(URL, u); setTimeout(function () { revoke.call(URL, u); }, 60000); };
})();

DSASSET
 );
$a['media-template.pptx'] = array( 'application/vnd.openxmlformats-officedocument.presentationml.presentation', <<<'DSASSET'
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
DSASSET
 , true );
return $a;
	}

	/* ------------------------------------------------------------------
	 * Manifest (the file you load into PowerPoint). Generated from this
	 * site's own URLs, so staging and live each get a correct manifest.
	 * ------------------------------------------------------------------ */
	public static function addin_id() {
		$id = get_option( 'ds_office_bridge_addin_id' );
		if ( ! $id ) {
			$id = wp_generate_uuid4();
			update_option( 'ds_office_bridge_addin_id', $id, false );
		}
		return $id;
	}

	public static function origin( $url ) {
		$p = wp_parse_url( $url );
		return $p['scheme'] . '://' . $p['host'] . ( isset( $p['port'] ) ? ':' . $p['port'] : '' );
	}

	public static function manifest_xml( $args = array() ) {
		$home   = untrailingslashit( isset( $args['home'] ) ? $args['home'] : home_url() );
		$asset  = function ( $n, $x = array() ) use ( $args ) {
			return isset( $args['asset'] ) ? call_user_func( $args['asset'], $n, $x ) : DS_Office_Bridge::asset_url( $n, $x );
		};
		$id     = isset( $args['id'] ) ? $args['id'] : self::addin_id();
		$name   = isset( $args['name'] ) ? $args['name'] : 'DesignStager';
		$ver    = isset( $args['version'] ) ? $args['version'] : '1.0.0.0';
		$page   = $asset( 'taskpane.html' );
		$sopts  = self::opts();
		$supp   = $home . '/' . ltrim( (string) $sopts['support_path'], '/' );
		$e      = function ( $s ) {
			return htmlspecialchars( $s, ENT_XML1 | ENT_QUOTES, 'UTF-8' );
		};
		$domains = array( self::origin( $home ) );
		$dom_xml = '';
		foreach ( $domains as $d ) {
			$dom_xml .= "\n    <AppDomain>" . $e( $d ) . '</AppDomain>';
		}

		return '<?xml version="1.0" encoding="UTF-8"?>
<OfficeApp xmlns="http://schemas.microsoft.com/office/appforoffice/1.1"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xmlns:bt="http://schemas.microsoft.com/office/officeappbasictypes/1.0"
           xmlns:ov="http://schemas.microsoft.com/office/taskpaneappversionoverrides"
           xsi:type="TaskPaneApp">
  <Id>' . $e( $id ) . '</Id>
  <Version>' . $e( $ver ) . '</Version>
  <ProviderName>DesignStager</ProviderName>
  <DefaultLocale>en-US</DefaultLocale>
  <DisplayName DefaultValue="' . $e( $name ) . '"/>
  <Description DefaultValue="Browse DesignStager inside PowerPoint and insert slides, images and icons directly into your presentation."/>
  <IconUrl DefaultValue="' . $e( $asset( 'icon-32.png' ) ) . '"/>
  <HighResolutionIconUrl DefaultValue="' . $e( $asset( 'icon-64.png' ) ) . '"/>
  <SupportUrl DefaultValue="' . $e( $supp ) . '"/>
  <AppDomains>' . $dom_xml . '
  </AppDomains>
  <Hosts>
    <Host Name="Presentation"/>
  </Hosts>
  <Requirements>
    <Sets DefaultMinVersion="1.1">
      <Set Name="DialogApi" MinVersion="1.1"/>
      <Set Name="PowerPointApi" MinVersion="1.2"/>
    </Sets>
  </Requirements>
  <DefaultSettings>
    <SourceLocation DefaultValue="' . $e( $page ) . '"/>
  </DefaultSettings>
  <Permissions>ReadWriteDocument</Permissions>
  <VersionOverrides xmlns="http://schemas.microsoft.com/office/taskpaneappversionoverrides" xsi:type="VersionOverridesV1_0">
    <Hosts>
      <Host xsi:type="Presentation">
        <DesktopFormFactor>
          <GetStarted>
            <Title resid="DS.GetStarted.Title"/>
            <Description resid="DS.GetStarted.Desc"/>
            <LearnMoreUrl resid="DS.Site.Url"/>
          </GetStarted>
          <FunctionFile resid="DS.Page.Url"/>
          <ExtensionPoint xsi:type="PrimaryCommandSurface">
            <OfficeTab id="TabHome">
              <Group id="DS.Group">
                <Label resid="DS.Group.Label"/>
                <Icon>
                  <bt:Image size="16" resid="DS.Icon.16"/>
                  <bt:Image size="32" resid="DS.Icon.32"/>
                  <bt:Image size="80" resid="DS.Icon.80"/>
                </Icon>
                <Control xsi:type="Button" id="DS.OpenLibrary">
                  <Label resid="DS.Open.Label"/>
                  <Supertip>
                    <Title resid="DS.Open.Label"/>
                    <Description resid="DS.Open.Tip"/>
                  </Supertip>
                  <Icon>
                    <bt:Image size="16" resid="DS.Icon.16"/>
                    <bt:Image size="32" resid="DS.Icon.32"/>
                    <bt:Image size="80" resid="DS.Icon.80"/>
                  </Icon>
                  <Action xsi:type="ShowTaskpane">
                    <TaskpaneId>DS.Panel</TaskpaneId>
                    <SourceLocation resid="DS.Page.Url"/>
                  </Action>
                </Control>
              </Group>
            </OfficeTab>
          </ExtensionPoint>
        </DesktopFormFactor>
      </Host>
    </Hosts>
    <Resources>
      <bt:Images>
        <bt:Image id="DS.Icon.16" DefaultValue="' . $e( $asset( 'icon-16.png' ) ) . '"/>
        <bt:Image id="DS.Icon.32" DefaultValue="' . $e( $asset( 'icon-32.png' ) ) . '"/>
        <bt:Image id="DS.Icon.80" DefaultValue="' . $e( $asset( 'icon-80.png' ) ) . '"/>
      </bt:Images>
      <bt:Urls>
        <bt:Url id="DS.Page.Url" DefaultValue="' . $e( $page ) . '"/>
        <bt:Url id="DS.Site.Url" DefaultValue="' . $e( $home . '/' ) . '"/>
      </bt:Urls>
      <bt:ShortStrings>
        <bt:String id="DS.Group.Label" DefaultValue="' . $e( $name ) . '"/>
        <bt:String id="DS.Open.Label" DefaultValue="' . $e( $name ) . '"/>
        <bt:String id="DS.GetStarted.Title" DefaultValue="' . $e( $name ) . ' is ready"/>
      </bt:ShortStrings>
      <bt:LongStrings>
        <bt:String id="DS.Open.Tip" DefaultValue="Open the ' . $e( $name ) . ' panel. Click Browse to open the library and insert slides, images and media into your presentation."/>
        <bt:String id="DS.GetStarted.Desc" DefaultValue="Home tab &gt; Open ' . $e( $name ) . '."/>
      </bt:LongStrings>
    </Resources>
  </VersionOverrides>
</OfficeApp>
';
	}

	public static function download_manifest() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not allowed.' );
		}
		check_admin_referer( 'ds_office_manifest' );
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		nocache_headers();
		header( 'Content-Type: application/xml; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="designstager-manifest-' . sanitize_file_name( $host ) . '.xml"' );
		$is_live = ( false === strpos( $host, 'staging' ) );
		echo self::manifest_xml( array( 'name' => 'DesignStager' ) ); // phpcs:ignore
		exit;
	}

	/* ------------------------------------------------------------------
	 * Settings page: Settings > DesignStager Office
	 * ------------------------------------------------------------------ */
	public static function admin_menu() {
		add_options_page( 'DesignStager Office', 'DesignStager Office', 'manage_options', 'ds-office-bridge', array( __CLASS__, 'render_settings' ) );
	}

	public static function register_settings() {
		register_setting(
			'ds_office_bridge',
			self::OPT,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	public static function sanitize( $in ) {
		$d = self::defaults();
		$in = is_array( $in ) ? $in : array();
		return array(
			'enabled'       => empty( $in['enabled'] ) ? 0 : 1,
			'selectors'     => isset( $in['selectors'] ) ? sanitize_textarea_field( $in['selectors'] ) : '',
			'hide_css'      => isset( $in['hide_css'] ) ? wp_strip_all_tags( $in['hide_css'] ) : $d['hide_css'],
			'formatting'    => ( isset( $in['formatting'] ) && 'UseDestinationTheme' === $in['formatting'] ) ? 'UseDestinationTheme' : 'KeepSourceFormatting',
			'dialog_width'  => isset( $in['dialog_width'] ) ? max( 30, min( 100, (int) $in['dialog_width'] ) ) : 95,
			'dialog_height' => isset( $in['dialog_height'] ) ? max( 30, min( 100, (int) $in['dialog_height'] ) ) : 95,
			'proxy_hosts'   => isset( $in['proxy_hosts'] ) ? sanitize_text_field( $in['proxy_hosts'] ) : $d['proxy_hosts'],
			'hide_upsell'   => empty( $in['hide_upsell'] ) ? 0 : 1,
			'url_patterns'  => isset( $in['url_patterns'] ) ? sanitize_text_field( $in['url_patterns'] ) : '/download/',
			'start_path'    => isset( $in['start_path'] ) ? '/' . ltrim( sanitize_text_field( $in['start_path'] ), '/' ) : '/',
			'login_path'    => isset( $in['login_path'] ) ? '/' . ltrim( sanitize_text_field( $in['login_path'] ), '/' ) : $d['login_path'],
			'signup_path'   => isset( $in['signup_path'] ) ? '/' . ltrim( sanitize_text_field( $in['signup_path'] ), '/' ) : $d['signup_path'],
			'support_path'  => isset( $in['support_path'] ) ? '/' . ltrim( sanitize_text_field( $in['support_path'] ), '/' ) : $d['support_path'],
			'tile_thumbs'   => self::sanitize_tile_thumbs( isset( $in['tile_thumbs'] ) ? $in['tile_thumbs'] : array() ),
		);
	}

	public static function sanitize_tile_thumbs( $in ) {
		$out = array();
		foreach ( array_keys( self::tile_keys() ) as $k ) {
			$u = ( is_array( $in ) && isset( $in[ $k ] ) ) ? esc_url_raw( trim( (string) $in[ $k ] ) ) : '';
			if ( '' !== $u ) {
				$out[ $k ] = $u;
			}
		}
		return $out;
	}

	/* The media picker on the settings page. */
	public static function admin_assets( $hook ) {
		if ( 'settings_page_ds-office-bridge' !== $hook ) {
			return;
		}
		wp_enqueue_media();
	}

	public static function render_settings() {
		$o   = self::opts();
		$n   = self::OPT;
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=ds_office_manifest' ), 'ds_office_manifest' );
		?>
		<div class="wrap">
			<h1>DesignStager Office (PowerPoint add-in)</h1>
			<?php
			$hs = self::health_state( isset( $_GET['ds_recheck'] ) ); // phpcs:ignore
			$hu = add_query_arg( 'ds_recheck', '1' );
			?>
			<p>
				<?php if ( ! empty( $hs['ok'] ) ) : ?>
					<span style="display:inline-block;padding:4px 10px;border-radius:12px;background:#d5f5e3;color:#145a32;font-weight:600">Add-in URLs: serving correctly</span>
				<?php else : ?>
					<span style="display:inline-block;padding:4px 10px;border-radius:12px;background:#fadbd8;color:#7b241c;font-weight:600">Add-in URLs: NOT serving &mdash; <?php echo esc_html( $hs['msg'] ); ?></span>
				<?php endif; ?>
				&nbsp;<a href="<?php echo esc_url( $hu ); ?>">Re-check</a>
				&nbsp;<code><?php echo esc_html( self::asset_url( 'taskpane.html' ) ); ?></code>
			</p>
			<p><a class="button button-primary" href="<?php echo esc_url( $url ); ?>">Download manifest.xml for this site</a>
			&nbsp; <a class="button" target="_blank" href="<?php echo esc_url( add_query_arg( 'ds_office', '1', home_url( $o['start_path'] ) ) ); ?>">Preview start page</a></p>
			<p>Load the manifest in PowerPoint (Insert &rsaquo; Add-ins &rsaquo; My Add-ins &rsaquo; Upload My Add-in). Then use <strong>Home &rsaquo; Open DesignStager</strong>.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'ds_office_bridge' ); ?>
				<table class="form-table" role="presentation">
					<tr><th>Enabled</th><td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[enabled]" value="1" <?php checked( $o['enabled'], 1 ); ?>> Allow the PowerPoint add-in to use this site</label></td></tr>
					<tr><th>Marketplace mode</th><td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[hide_upsell]" value="1" <?php checked( $o['hide_upsell'], 1 ); ?>> Hide pricing, upgrade and checkout links inside the PowerPoint window</label><p class="description">Required for AppSource: an add-in may not up-sell or take payment outside Microsoft Marketplace (policy 1100.3). People buy on the website as usual; the add-in only uses the account they already have.</p></td></tr>
					<tr><th>Start page</th><td><input class="regular-text" name="<?php echo esc_attr( $n ); ?>[start_path]" value="<?php echo esc_attr( $o['start_path'] ); ?>"><p class="description">Path the PowerPoint window opens on, e.g. <code>/</code> or <code>/templates/</code>.</p></td></tr>
					<tr><th>Sign-in page</th><td><input class="regular-text" name="<?php echo esc_attr( $n ); ?>[login_path]" value="<?php echo esc_attr( $o['login_path'] ); ?>"><p class="description">Where the task pane&rsquo;s <strong>Sign in</strong> button goes, e.g. <code>/login/</code>.</p></td></tr>
					<tr><th>Sign-up page</th><td><input class="regular-text" name="<?php echo esc_attr( $n ); ?>[signup_path]" value="<?php echo esc_attr( $o['signup_path'] ); ?>"><p class="description">Where the task pane&rsquo;s <strong>Sign up</strong> button goes.</p></td></tr>
					<tr><th>Support page</th><td><input class="regular-text" name="<?php echo esc_attr( $n ); ?>[support_path]" value="<?php echo esc_attr( $o['support_path'] ); ?>"><p class="description">Used as <code>SupportUrl</code> in the manifest. AppSource requires a real support page, not the home page.</p></td></tr>
					<tr><th>Window size (% of screen)</th><td>Width <input type="number" min="30" max="100" name="<?php echo esc_attr( $n ); ?>[dialog_width]" value="<?php echo esc_attr( $o['dialog_width'] ); ?>"> Height <input type="number" min="30" max="100" name="<?php echo esc_attr( $n ); ?>[dialog_height]" value="<?php echo esc_attr( $o['dialog_height'] ); ?>"></td></tr>
					<tr><th>Inserted slides</th><td><select name="<?php echo esc_attr( $n ); ?>[formatting]">
						<option value="KeepSourceFormatting" <?php selected( $o['formatting'], 'KeepSourceFormatting' ); ?>>Keep the template's own design</option>
						<option value="UseDestinationTheme" <?php selected( $o['formatting'], 'UseDestinationTheme' ); ?>>Adapt to the user's presentation theme</option>
					</select></td></tr>
					<tr><th>Download URL patterns</th><td><input class="regular-text" name="<?php echo esc_attr( $n ); ?>[url_patterns]" value="<?php echo esc_attr( $o['url_patterns'] ); ?>"><p class="description">Comma-separated. Any navigation to a URL containing one of these is treated as a download (your site's own click logic, credit checks and limits still run first).</p></td></tr>
					<tr><th>Allowed file hosts</th><td><input class="regular-text" name="<?php echo esc_attr( $n ); ?>[proxy_hosts]" value="<?php echo esc_attr( $o['proxy_hosts'] ); ?>"><p class="description">Comma-separated. Domains where download files may live (besides this site), e.g. your live site or CDN.</p></td></tr>
					<tr><th>Extra download-button selectors</th><td><textarea class="large-text code" rows="3" name="<?php echo esc_attr( $n ); ?>[selectors]"><?php echo esc_textarea( $o['selectors'] ); ?></textarea><p class="description">Comma-separated CSS selectors for download buttons whose link does not end in .pptx/.png/.svg etc. Example: <code>.ds-download-btn, a.download-link</code></p></td></tr>
					<tr><th>CSS inside PowerPoint only</th><td><textarea class="large-text code" rows="5" name="<?php echo esc_attr( $n ); ?>[hide_css]"><?php echo esc_textarea( $o['hide_css'] ); ?></textarea><p class="description">Applied only in the PowerPoint window, e.g. hide the footer or cookie banner: <code>footer, .cookie-notice{display:none!important}</code></p></td></tr>
				</table>
				<h2 id="ds-tile-pictures">AI Suites tile pictures</h2>
				<p>The picture shown on each tile in the add-in's AI Suites section. Choose an image from the Media Library or paste an image URL. A landscape image around 640 &times; 360 px works best. Leave a tile empty to use an automatic picture from the marketplace.</p>
				<table class="form-table" role="presentation">
					<?php
					$tt = is_array( $o['tile_thumbs'] ) ? $o['tile_thumbs'] : array();
					foreach ( self::tile_keys() as $tk => $tl ) :
						$tv = isset( $tt[ $tk ] ) ? $tt[ $tk ] : '';
						$tf = $n . '[tile_thumbs][' . $tk . ']';
						?>
						<tr>
							<th><?php echo esc_html( $tl ); ?></th>
							<td>
								<div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
									<img class="ds-tile-prev" src="<?php echo esc_url( $tv ); ?>" alt="" style="width:160px;height:90px;object-fit:cover;border-radius:6px;background:#1d2335;<?php echo $tv ? '' : 'display:none;'; ?>">
									<input type="url" class="regular-text ds-tile-url" name="<?php echo esc_attr( $tf ); ?>" value="<?php echo esc_attr( $tv ); ?>" placeholder="https://…">
									<button type="button" class="button ds-tile-pick">Choose image</button>
									<button type="button" class="button-link ds-tile-clear"<?php echo $tv ? '' : ' style="display:none"'; ?>>Remove</button>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<script>
				(function () {
					function row(el) { while (el && el.tagName !== 'TD') { el = el.parentNode; } return el; }
					function set(td, url) {
						var inp = td.querySelector('.ds-tile-url'), img = td.querySelector('.ds-tile-prev'), clr = td.querySelector('.ds-tile-clear');
						inp.value = url || '';
						img.src = url || ''; img.style.display = url ? '' : 'none';
						clr.style.display = url ? '' : 'none';
					}
					document.addEventListener('click', function (e) {
						var t = e.target;
						if (t.classList.contains('ds-tile-pick')) {
							e.preventDefault();
							if (!window.wp || !wp.media) { alert('The Media Library is not available on this page.'); return; }
							var td = row(t);
							var frame = wp.media({ title: 'Choose a tile picture', button: { text: 'Use this picture' }, library: { type: 'image' }, multiple: false });
							frame.on('select', function () {
								var a = frame.state().get('selection').first().toJSON();
								var u = (a.sizes && (a.sizes.medium_large || a.sizes.large)) ? (a.sizes.medium_large || a.sizes.large).url : a.url;
								set(td, u);
							});
							frame.open();
						}
						if (t.classList.contains('ds-tile-clear')) { e.preventDefault(); set(row(t), ''); }
					});
					document.addEventListener('input', function (e) {
						if (e.target.classList.contains('ds-tile-url')) {
							var td = row(e.target), v = e.target.value.trim(), img = td.querySelector('.ds-tile-prev');
							img.src = v; img.style.display = v ? '' : 'none';
						}
					});
				})();
				</script>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}

DS_Office_Bridge::init();
