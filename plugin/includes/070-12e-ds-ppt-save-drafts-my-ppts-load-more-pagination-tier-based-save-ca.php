<?php
/**
 * Code Snippets export — 12E ds ppt save drafts — my-ppts Load More pagination [Tier-Based Save Cap]
 *
 * Snippet ID : 756
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-070
 * Modified   : 2026-09-09 02:40:19
 * Load order : 070 of 144
 */
/**
 * Snippet: 12E ds ppt save drafts
 * Run: Everywhere
 * Registers REST endpoints: save-ppt, my-ppts, load-ppt, delete-ppt
 */
if ( ! defined('ABSPATH') ) exit;

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/save-ppt', array(
        'methods' => 'POST',
        'callback' => 'ds_ppt_save_draft',
        'permission_callback' => function(){ return is_user_logged_in(); },
    ));
    register_rest_route('ds/v1', '/my-ppts', array(
        'methods' => 'GET',
        'callback' => 'ds_ppt_list_drafts',
        'permission_callback' => function(){ return is_user_logged_in(); },
    ));
    register_rest_route('ds/v1', '/load-ppt', array(
        'methods' => 'POST',
        'callback' => 'ds_ppt_load_draft',
        'permission_callback' => function(){ return is_user_logged_in(); },
    ));
    register_rest_route('ds/v1', '/delete-ppt', array(
        'methods' => 'POST',
        'callback' => 'ds_ppt_delete_draft',
        'permission_callback' => function(){ return is_user_logged_in(); },
    ));
});

if ( ! function_exists('ds_ppt_save_draft') ) {
function ds_ppt_save_draft( WP_REST_Request $req ) {
    $uid   = get_current_user_id();
    $id    = sanitize_text_field( $req->get_param('ppt_id') );
    $title = sanitize_text_field( $req->get_param('title') ?: 'Presentation' );
    $data  = $req->get_param('ppt_data');

    $is_new_draft = empty($id);
    if ( $is_new_draft && function_exists('ds_hub_count_saved_items') ) {
        if ( ds_hub_count_saved_items( $uid ) >= ( function_exists('ds_get_user_saved_cap') ? ds_get_user_saved_cap( $uid ) : 100 ) ) {
            return rest_ensure_response(array('success'=>false,'error'=>"You've reached your saved-item limit. Delete an older saved item (image, video, or presentation) before saving a new one."));
        }
    }
    if ( empty($id) ) {
        $id = 'ppt_' . time() . '_' . wp_generate_password(6, false, false);
    }
    if ( ! is_array($data) || empty($data['slides']) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'No slide data'));
    }

    // Only clear transient UI flags. KEEP image_data so the exact image
    // the user finalized is preserved when the draft is reopened later.
    foreach ( $data['slides'] as $k => $s ) {
        unset(
            $data['slides'][$k]['image_loading'],
            $data['slides'][$k]['image_failed']
        );
    }

    $drafts = get_user_meta($uid, 'ds_ppt_drafts', true);
    if ( ! is_array($drafts) ) $drafts = array();

    $design = sanitize_text_field( $req->get_param('design') ?: 'navy-pro' );
    $drafts[$id] = array(
        'id'           => $id,
        'title'        => $title,
        'slides_count' => count($data['slides']),
        'data'         => $data,
        'design'       => $design,
        'updated'      => time(),
    );

    // Keep newest 20 max
    uasort($drafts, function($a, $b){ return $b['updated'] - $a['updated']; });
    if ( count($drafts) > 20 ) {
        $drafts = array_slice($drafts, 0, 20, true);
    }

    update_user_meta($uid, 'ds_ppt_drafts', $drafts);
    return rest_ensure_response(array('success' => true, 'ppt_id' => $id));
}
}

if ( ! function_exists('ds_ppt_list_drafts') ) {
function ds_ppt_list_drafts( WP_REST_Request $req = null ) {
    $uid    = get_current_user_id();
    $drafts = get_user_meta($uid, 'ds_ppt_drafts', true);
    if ( ! is_array($drafts) ) $drafts = array();


if ( ! function_exists('ds_ppt_thumb') ) {
/**
 * Downscale a slide's base64 image to a card-sized thumbnail, cached per user.
 *
 * Drafts store the full-resolution image the user finalised (~1.2 MB base64
 * each). The My Presentations grid renders it 240 px wide, so shipping the
 * original made the list response ~17 MB. This returns a ~320 px JPEG,
 * typically 15-25 KB, and remembers it so the work happens once per image.
 *
 * @param string $data_uri Original base64 data URI.
 * @param int    $uid      User the cache belongs to.
 * @return string Small data URI, or '' when it cannot be produced.
 */
function ds_ppt_thumb( $data_uri, $uid, $cache_only = false ) {
    if ( ! is_string( $data_uri ) || strpos( $data_uri, 'data:image' ) !== 0 ) {
        return '';
    }

    $key   = md5( $data_uri );
    $cache = get_user_meta( $uid, 'ds_ppt_thumb_cache', true );
    if ( ! is_array( $cache ) ) { $cache = array(); }
    if ( isset( $cache[ $key ] ) ) {
        return $cache[ $key ];
    }
    if ( $cache_only ) {
        return ''; // Caller is checking cheaply; do no image work.
    }

    if ( ! function_exists( 'imagecreatefromstring' ) ) {
        return ''; // No GD — fall back to no thumbnail rather than 1.2 MB.
    }

    $comma = strpos( $data_uri, ',' );
    if ( false === $comma ) { return ''; }
    $raw = base64_decode( substr( $data_uri, $comma + 1 ), true );
    if ( ! $raw ) { return ''; }

    $img = @imagecreatefromstring( $raw );
    unset( $raw );
    if ( ! $img ) { return ''; }

    $w = imagesx( $img );
    $h = imagesy( $img );
    if ( $w > 320 ) {
        $small = @imagescale( $img, 320 );
        if ( $small ) { imagedestroy( $img ); $img = $small; }
    }

    ob_start();
    imagejpeg( $img, null, 62 );
    $bytes = ob_get_clean();
    imagedestroy( $img );
    if ( ! $bytes ) { return ''; }

    $thumb = 'data:image/jpeg;base64,' . base64_encode( $bytes );

    // Keep the cache bounded — 40 thumbnails at ~20 KB is well under 1 MB.
    if ( count( $cache ) > 40 ) { $cache = array_slice( $cache, -20, 20, true ); }
    $cache[ $key ] = $thumb;
    update_user_meta( $uid, 'ds_ppt_thumb_cache', $cache );

    return $thumb;
}
}

    $out = array();
    foreach ( $drafts as $d ) {
        $first_slide = isset($d['data']['slides'][0]) ? $d['data']['slides'][0] : null;
        $out[] = array(
            'id'           => $d['id'],
            'title'        => $d['title'],
            'slides_count' => $d['slides_count'],
            'updated'      => $d['updated'],
            'design'       => isset($d['design']) ? $d['design'] : 'dark-modern',
            'first_slide'  => $first_slide,
        );
    }
    usort($out, function($a, $b){ return $b['updated'] - $a['updated']; });
    $__total = count($out);
    $__offset = max(0, (int) ($req ? $req->get_param('offset') : 0));
    $__limit = (int) ($req ? $req->get_param('limit') : 0);
    if ($__limit <= 0) $__limit = 6;
    if ($__limit > 24) $__limit = 24;
    $__slice = array_slice($out, $__offset, $__limit);

    /*
     * 16.2 — replace each card's full-resolution image with a ~320 px
     * thumbnail. Only the slice being returned is processed, and results are
     * cached, so this is one cheap pass instead of a multi-megabyte response.
     */
    $__budget = 2; // 16.3: cap fresh conversions per request — see below.
    foreach ( $__slice as $__i => $__d ) {
        if ( empty( $__d['first_slide']['image_data'] ) ) {
            continue;
        }
        /*
         * 16.3 — decoding six ~1.2 MB JPEGs in one request held a limiter slot
         * for a long time and backed the client queue up behind it, which is
         * what made delete appear dead. Serve anything already cached for free,
         * and convert at most $__budget new ones per request; the rest render
         * without a thumbnail this time and fill in on the next load.
         */
        // 17.2: images are stored as files (snippet 145) — resolve the ref first.
        $__src = function_exists( 'ds_pptimg_get' ) ? ds_pptimg_get( $uid, $__d['first_slide']['image_data'] ) : $__d['first_slide']['image_data'];
        $__cached = ds_ppt_thumb( $__src, $uid, true );
        if ( '' !== $__cached ) {
            $__slice[ $__i ]['first_slide']['image_data'] = $__cached;
        } elseif ( $__budget > 0 ) {
            $__budget--;
            $__slice[ $__i ]['first_slide']['image_data'] = ds_ppt_thumb( $__src, $uid );
        } else {
            $__slice[ $__i ]['first_slide']['image_data'] = '';
        }
    }
    return rest_ensure_response(array('success' => true, 'drafts' => $__slice, 'total' => $__total, 'next_offset' => $__offset + count($__slice)));
}
}

if ( ! function_exists('ds_ppt_load_draft') ) {
function ds_ppt_load_draft( WP_REST_Request $req ) {
    $uid    = get_current_user_id();
    $id     = sanitize_text_field($req->get_param('ppt_id'));
    $drafts = get_user_meta($uid, 'ds_ppt_drafts', true);

    if ( ! is_array($drafts) || empty($drafts[$id]) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Not found'));
    }
    return rest_ensure_response(array(
        'success' => true,
        'data'    => $drafts[$id]['data'],
        'design'  => isset($drafts[$id]['design']) ? $drafts[$id]['design'] : 'navy-pro',
    ));
}
}

if ( ! function_exists('ds_ppt_delete_draft') ) {
function ds_ppt_delete_draft( WP_REST_Request $req ) {
    $uid    = get_current_user_id();
    $id     = sanitize_text_field($req->get_param('ppt_id'));
    $drafts = get_user_meta($uid, 'ds_ppt_drafts', true);

    if ( is_array($drafts) && isset($drafts[$id]) ) {
        unset($drafts[$id]);
        update_user_meta($uid, 'ds_ppt_drafts', $drafts);
    }
    return rest_ensure_response(array('success' => true));
}
}


/* =====================================================================
 * 17.2 — PPT image offload + single-flight save (was drafted as snippet 145)
 * ===================================================================== */

/**
 * DS PPT — Image offload + single-flight save
 *
 * Load order : 145 (after every other PPT snippet)
 *
 * WHY
 * ---
 * Slide images were stored inline as base64 inside the single user-meta row
 * `ds_ppt_drafts` (up to 20 decks x 20 images). Every Save re-uploaded ~6 MB
 * from the browser AND re-serialized every deck the user owns into MySQL, so
 * saves routinely ran past the 45s client timeout. The client then retried
 * while the old upload was still running, 2-3 uploads held all 3 REST slots,
 * and marketplace image fetches queued behind them (images "not loading").
 *
 * WHAT THIS DOES
 * --------------
 * Server:
 *  - Stores each slide image once, as a file, under
 *    uploads/ds-ppt-img/<user id>/<sha1>.txt (direct web access denied),
 *    and keeps only a short "dsimg:<sha1>" reference in the database.
 *  - ds_ppt_drafts writes are dehydrated automatically (covers old decks too:
 *    the first save after this ships migrates the user's existing drafts).
 *  - load-ppt, my-ppts thumbnails, shared links and build-pptx-v2 turn the
 *    references back into data URIs, so no existing front-end code changes.
 *  - New endpoints: ppt-img-has (which hashes exist) and ppt-img-put (store one).
 *
 * Browser (installed in wp_head right after the fetch limiter, so it sits
 * closest to the network, after every other save-ppt wrapper has built its body):
 *  - Before save-ppt / create-share-link / build-pptx-v2, uploads only images
 *    the server doesn't have yet (one at a time) and sends references instead.
 *    A typical re-save is now a few KB.
 *  - Only one save-ppt is ever in flight. Saves requested meanwhile collapse
 *    into a single follow-up save carrying the newest data.
 */

if ( ! defined( 'DS_PPTIMG_PREFIX' ) ) define( 'DS_PPTIMG_PREFIX', 'dsimg:' );

/* ─────────────────────────── storage helpers ─────────────────────────── */

if ( ! function_exists( 'ds_pptimg_root' ) ) {
function ds_pptimg_root( $create = false ) {
	$up   = wp_upload_dir( null, false );
	$root = trailingslashit( $up['basedir'] ) . 'ds-ppt-img';
	if ( $create && ! is_dir( $root ) ) {
		wp_mkdir_p( $root );
		@file_put_contents( $root . '/.htaccess', "Options -Indexes\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" );
		@file_put_contents( $root . '/index.php', "<?php // Silence is golden.\n" );
	}
	return $root;
}
}

if ( ! function_exists( 'ds_pptimg_dir' ) ) {
function ds_pptimg_dir( $uid, $create = false ) {
	$dir = ds_pptimg_root( $create ) . '/' . (int) $uid;
	if ( $create && ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
		@file_put_contents( $dir . '/index.php', "<?php // Silence is golden.\n" );
	}
	return $dir;
}
}

if ( ! function_exists( 'ds_pptimg_is_ref' ) ) {
function ds_pptimg_is_ref( $v ) {
	return is_string( $v ) && strlen( $v ) === 46 && strpos( $v, DS_PPTIMG_PREFIX ) === 0
		&& preg_match( '/^[a-f0-9]{40}$/', substr( $v, 6 ) );
}
}

if ( ! function_exists( 'ds_pptimg_is_data' ) ) {
function ds_pptimg_is_data( $v ) {
	return is_string( $v ) && strlen( $v ) > 1000 && strpos( $v, 'data:image/' ) === 0;
}
}

/** Store one data URI for a user. Returns the "dsimg:<sha1>" ref, or '' on failure. */
if ( ! function_exists( 'ds_pptimg_put' ) ) {
function ds_pptimg_put( $uid, $data_uri ) {
	if ( ! ds_pptimg_is_data( $data_uri ) ) return '';
	if ( strlen( $data_uri ) > 40 * 1024 * 1024 ) return '';
	$comma = strpos( $data_uri, ',' );
	if ( false === $comma || false === strpos( substr( $data_uri, 0, $comma ), ';base64' ) ) return '';

	$hash = sha1( $data_uri );
	$dir  = ds_pptimg_dir( $uid, true );
	$file = $dir . '/' . $hash . '.txt';
	if ( ! file_exists( $file ) ) {
		$tmp = $file . '.' . wp_generate_password( 6, false, false ) . '.tmp';
		if ( false === @file_put_contents( $tmp, $data_uri, LOCK_EX ) ) return '';
		if ( ! @rename( $tmp, $file ) ) { @unlink( $tmp ); if ( ! file_exists( $file ) ) return ''; }
	} else {
		@touch( $file ); // keeps GC from treating an in-use image as stale
	}
	return DS_PPTIMG_PREFIX . $hash;
}
}

if ( ! function_exists( 'ds_pptimg_has' ) ) {
function ds_pptimg_has( $uid, $ref_or_hash ) {
	$h = strpos( (string) $ref_or_hash, DS_PPTIMG_PREFIX ) === 0 ? substr( $ref_or_hash, 6 ) : (string) $ref_or_hash;
	if ( ! preg_match( '/^[a-f0-9]{40}$/', $h ) ) return false;
	return file_exists( ds_pptimg_dir( $uid ) . '/' . $h . '.txt' );
}
}

/** Resolve a ref back to its data URI. Non-refs are returned unchanged. */
if ( ! function_exists( 'ds_pptimg_get' ) ) {
function ds_pptimg_get( $uid, $v ) {
	if ( ! ds_pptimg_is_ref( $v ) ) return $v;
	$file = ds_pptimg_dir( $uid ) . '/' . substr( $v, 6 ) . '.txt';
	$d    = is_readable( $file ) ? @file_get_contents( $file ) : false;
	return ( is_string( $d ) && $d !== '' ) ? $d : '';
}
}

/** Replace inline image_data with refs. $changed is set true when anything was replaced. */
if ( ! function_exists( 'ds_pptimg_dehydrate_slides' ) ) {
function ds_pptimg_dehydrate_slides( $uid, $slides, &$changed = false ) {
	if ( ! is_array( $slides ) ) return $slides;
	foreach ( $slides as $i => $s ) {
		if ( is_array( $s ) && isset( $s['image_data'] ) && ds_pptimg_is_data( $s['image_data'] ) ) {
			$ref = ds_pptimg_put( $uid, $s['image_data'] );
			if ( $ref ) { $slides[ $i ]['image_data'] = $ref; $changed = true; }
		}
	}
	return $slides;
}
}

if ( ! function_exists( 'ds_pptimg_hydrate_slides' ) ) {
function ds_pptimg_hydrate_slides( $uid, $slides ) {
	if ( ! is_array( $slides ) ) return $slides;
	foreach ( $slides as $i => $s ) {
		if ( is_array( $s ) && isset( $s['image_data'] ) && ds_pptimg_is_ref( $s['image_data'] ) ) {
			$slides[ $i ]['image_data'] = ds_pptimg_get( $uid, $s['image_data'] );
		}
	}
	return $slides;
}
}

/** Refs in slides whose files are missing (hashes only). */
if ( ! function_exists( 'ds_pptimg_missing' ) ) {
function ds_pptimg_missing( $uid, $slides ) {
	$miss = array();
	if ( ! is_array( $slides ) ) return $miss;
	foreach ( $slides as $s ) {
		if ( is_array( $s ) && isset( $s['image_data'] ) && ds_pptimg_is_ref( $s['image_data'] )
			&& ! ds_pptimg_has( $uid, $s['image_data'] ) ) {
			$miss[] = substr( $s['image_data'], 6 );
		}
	}
	return array_values( array_unique( $miss ) );
}
}

/** Remember refs used by a public share link so GC never deletes them. */
if ( ! function_exists( 'ds_pptimg_pin_refs' ) ) {
function ds_pptimg_pin_refs( $uid, $slides ) {
	$pins = get_user_meta( $uid, 'ds_pptimg_pins', true );
	if ( ! is_array( $pins ) ) $pins = array();
	$now = time();
	foreach ( (array) $slides as $s ) {
		if ( is_array( $s ) && isset( $s['image_data'] ) && ds_pptimg_is_ref( $s['image_data'] ) ) {
			$pins[ substr( $s['image_data'], 6 ) ] = $now;
		}
	}
	foreach ( $pins as $h => $t ) { if ( $now - (int) $t > 95 * DAY_IN_SECONDS ) unset( $pins[ $h ] ); }
	update_user_meta( $uid, 'ds_pptimg_pins', $pins );
}
}

/** Delete image files no draft or share uses any more (at most hourly per user). */
if ( ! function_exists( 'ds_pptimg_gc' ) ) {
function ds_pptimg_gc( $uid, $drafts ) {
	if ( get_transient( 'ds_pptimg_gc_' . (int) $uid ) ) return;
	set_transient( 'ds_pptimg_gc_' . (int) $uid, 1, HOUR_IN_SECONDS );

	$keep = array();
	foreach ( (array) $drafts as $d ) {
		if ( empty( $d['data']['slides'] ) || ! is_array( $d['data']['slides'] ) ) continue;
		foreach ( $d['data']['slides'] as $s ) {
			if ( is_array( $s ) && isset( $s['image_data'] ) && ds_pptimg_is_ref( $s['image_data'] ) ) {
				$keep[ substr( $s['image_data'], 6 ) ] = 1;
			}
		}
	}
	$pins = get_user_meta( $uid, 'ds_pptimg_pins', true );
	if ( is_array( $pins ) ) { foreach ( $pins as $h => $t ) $keep[ $h ] = 1; }

	$dir = ds_pptimg_dir( $uid );
	if ( ! is_dir( $dir ) ) return;
	$cut = time() - 2 * DAY_IN_SECONDS; // grace period: uploaded-but-not-yet-saved images
	foreach ( (array) glob( $dir . '/*.txt' ) as $f ) {
		$h = basename( $f, '.txt' );
		if ( ! isset( $keep[ $h ] ) && @filemtime( $f ) < $cut ) @unlink( $f );
	}
}
}

/* ─────────────── transparent dehydration of ds_ppt_drafts writes ─────────────── */

add_filter( 'update_user_metadata', function ( $check, $uid, $key, $value, $prev = '' ) {
	static $busy = false;
	if ( 'ds_ppt_drafts' !== $key || $busy || ! is_array( $value ) ) return $check;

	$changed = false;
	foreach ( $value as $id => $d ) {
		if ( is_array( $d ) && ! empty( $d['data']['slides'] ) && is_array( $d['data']['slides'] ) ) {
			$value[ $id ]['data']['slides'] = ds_pptimg_dehydrate_slides( $uid, $d['data']['slides'], $changed );
		}
	}

	$busy = true;
	$res  = $changed ? update_user_meta( $uid, $key, $value, $prev ) : null;
	$busy = false;

	try { ds_pptimg_gc( $uid, $value ); } catch ( Throwable $e ) {}

	return $changed ? $res : $check;
}, 10, 5 );

/* ───────────────────────────── REST endpoints ───────────────────────────── */

add_action( 'rest_api_init', function () {
	register_rest_route( 'ds/v1', '/ppt-img-has', array(
		'methods'             => 'POST',
		'permission_callback' => function () { return is_user_logged_in(); },
		'callback'            => function ( WP_REST_Request $req ) {
			$uid  = get_current_user_id();
			$have = array();
			foreach ( array_slice( (array) $req->get_param( 'hashes' ), 0, 200 ) as $h ) {
				$h = strtolower( (string) $h );
				if ( ds_pptimg_has( $uid, $h ) ) { $have[] = $h; @touch( ds_pptimg_dir( $uid ) . '/' . $h . '.txt' ); }
			}
			return rest_ensure_response( array( 'success' => true, 'have' => $have ) );
		},
	) );

	register_rest_route( 'ds/v1', '/ppt-img-put', array(
		'methods'             => 'POST',
		'permission_callback' => function () { return is_user_logged_in(); },
		'callback'            => function ( WP_REST_Request $req ) {
			$ref = ds_pptimg_put( get_current_user_id(), (string) $req->get_param( 'data' ) );
			if ( ! $ref ) {
				return rest_ensure_response( array( 'success' => false, 'error' => 'Invalid image' ) );
			}
			return rest_ensure_response( array( 'success' => true, 'ref' => $ref ) );
		},
	) );
} );

/* Reject a save/build/share whose image refs point at files we don't have, so
   the browser re-uploads them instead of silently losing the picture. */
add_filter( 'rest_pre_dispatch', function ( $result, $server, $request ) {
	if ( null !== $result || ! is_user_logged_in() ) return $result;
	$route = $request->get_route();
	$uid   = get_current_user_id();
	$slides = null;
	if ( '/ds/v1/save-ppt' === $route ) {
		$d = $request->get_param( 'ppt_data' );
		$slides = is_array( $d ) && isset( $d['slides'] ) ? $d['slides'] : null;
	} elseif ( '/ds/v1/build-pptx-v2' === $route || '/ds/v1/create-share-link' === $route ) {
		$slides = $request->get_param( 'slides' );
	}
	if ( ! is_array( $slides ) ) return $result;

	$miss = ds_pptimg_missing( $uid, $slides );
	if ( $miss ) {
		return rest_ensure_response( array( 'success' => false, 'error' => 'Some images need to be re-uploaded', 'missing_images' => $miss ) );
	}

	if ( '/ds/v1/build-pptx-v2' === $route ) {
		// The builder expects data URIs.
		$request->set_param( 'slides', ds_pptimg_hydrate_slides( $uid, $slides ) );
	} elseif ( '/ds/v1/create-share-link' === $route ) {
		$changed = false;
		$slides  = ds_pptimg_dehydrate_slides( $uid, $slides, $changed );
		$request->set_param( 'slides', $slides );
		ds_pptimg_pin_refs( $uid, $slides );
	}
	return $result;
}, 10, 3 );

/* load-ppt / my-ppts / shared-ppt responses: turn refs back into data URIs. */
add_filter( 'rest_post_dispatch', function ( $response, $server, $request ) {
	$route = $request->get_route();
	$is_shared = strpos( $route, '/ds/v1/shared-ppt/' ) === 0;
	if ( '/ds/v1/load-ppt' !== $route && '/ds/v1/my-ppts' !== $route && ! $is_shared ) return $response;
	if ( ! ( $response instanceof WP_REST_Response ) ) return $response;
	$data = $response->get_data();
	if ( ! is_array( $data ) ) return $response;

	if ( '/ds/v1/load-ppt' === $route && ! empty( $data['data']['slides'] ) ) {
		$data['data']['slides'] = ds_pptimg_hydrate_slides( get_current_user_id(), $data['data']['slides'] );
	} elseif ( $is_shared && ! empty( $data['slides'] ) ) {
		$rec = get_option( 'ds_share_' . sanitize_text_field( $request->get_param( 'token' ) ) );
		if ( ! empty( $rec['owner'] ) ) $data['slides'] = ds_pptimg_hydrate_slides( (int) $rec['owner'], $data['slides'] );
	} elseif ( '/ds/v1/my-ppts' === $route && ! empty( $data['drafts'] ) && is_array( $data['drafts'] ) ) {
		// my-ppts builds thumbnails itself (see 070); any ref left means no thumb yet.
		foreach ( $data['drafts'] as $i => $d ) {
			if ( isset( $d['first_slide']['image_data'] ) && ds_pptimg_is_ref( $d['first_slide']['image_data'] ) ) {
				$data['drafts'][ $i ]['first_slide']['image_data'] = '';
			}
		}
	}
	$response->set_data( $data );
	return $response;
}, 10, 3 );

/* Public viewer (?ds_view=TOKEN) reads the option directly — hydrate it there too. */
add_action( 'template_redirect', function () {
	if ( empty( $_GET['ds_view'] ) ) return;
	$token = sanitize_text_field( wp_unslash( $_GET['ds_view'] ) );
	add_filter( 'option_ds_share_' . $token, function ( $rec ) {
		if ( is_array( $rec ) && ! empty( $rec['owner'] ) && ! empty( $rec['slides'] ) ) {
			$rec['slides'] = ds_pptimg_hydrate_slides( (int) $rec['owner'], $rec['slides'] );
		}
		return $rec;
	} );
}, 0 );

/* ───────────────────────────── browser side ───────────────────────────── */

add_action( 'wp_head', function () {
	if ( ! is_user_logged_in() ) return;
	?>
<script id="ds-ppt-img-offload">
(function(){
  'use strict';
  if (window.__dsImgOffload) return; window.__dsImgOffload = true;
  if (!window.fetch || !window.crypto || !crypto.subtle || !window.TextEncoder) return;

  var inner = window.fetch;                  // limiter + image shrink (snippet 127)
  var known = Object.create(null);           // sha1 -> true (server has it)
  var hashCache = new Map();                 // data URI -> sha1 (per page)
  var MIN_LEN = 1000;

  function urlOf(u){ return typeof u === 'string' ? u : (u && u.url) || ''; }
  function isData(v){ return typeof v === 'string' && v.length > MIN_LEN && v.indexOf('data:image/') === 0; }

  function sha1(str){
    if (hashCache.has(str)) return Promise.resolve(hashCache.get(str));
    return crypto.subtle.digest('SHA-1', new TextEncoder().encode(str)).then(function(buf){
      var a = new Uint8Array(buf), h = '';
      for (var i = 0; i < a.length; i++) h += (a[i] < 16 ? '0' : '') + a[i].toString(16);
      hashCache.set(str, h);
      return h;
    });
  }

  function restBase(u){ var i = u.indexOf('/ds/v1/'); return i === -1 ? '/wp-json/ds/v1/' : u.slice(0, i + 7); }

  function jsonHeaders(opts){
    var h = {'Content-Type':'application/json'};
    var src = opts && opts.headers;
    try {
      if (src && typeof src.forEach === 'function' && !(src instanceof Array)) { src.forEach(function(v,k){ if (/nonce/i.test(k)) h[k] = v; }); }
      else if (src) { for (var k in src) if (/nonce/i.test(k)) h[k] = src[k]; }
    } catch(e) {}
    if (!h['X-WP-Nonce'] && window.wpApiSettings && wpApiSettings.nonce) h['X-WP-Nonce'] = wpApiSettings.nonce;
    return h;
  }

  /* Upload any images the server doesn't have; replace them with refs in `slides`. */
  function offload(slides, base, opts, force){
    if (!Array.isArray(slides)) return Promise.resolve();
    var idx = [];
    slides.forEach(function(s, i){ if (s && isData(s.image_data)) idx.push(i); });
    if (!idx.length) return Promise.resolve();
    var headers = jsonHeaders(opts);

    return Promise.all(idx.map(function(i){ return sha1(slides[i].image_data); })).then(function(hashes){
      var ask = hashes.filter(function(h){ return force || !known[h]; });
      var check = ask.length && !force
        ? inner(base + 'ppt-img-has', {method:'POST', headers:headers, credentials:'same-origin', body:JSON.stringify({hashes:ask})})
            .then(function(r){ return r.json(); })
            .then(function(d){ (d && d.have || []).forEach(function(h){ known[h] = true; }); })
            .catch(function(){})
        : Promise.resolve();

      return check.then(function(){
        var todo = [], seen = {};
        idx.forEach(function(i, n){ var h = hashes[n]; if ((force || !known[h]) && !seen[h]) { seen[h] = 1; todo.push({h:h, d:slides[i].image_data}); } });
        // one upload at a time: never crowd the image fetches out of the queue
        return todo.reduce(function(p, t){
          return p.then(function(){
            return inner(base + 'ppt-img-put', {method:'POST', headers:headers, credentials:'same-origin', body:JSON.stringify({data:t.d})})
              .then(function(r){ return r.json(); })
              .then(function(d){ if (d && d.success && d.ref === 'dsimg:' + t.h) known[t.h] = true; });
          });
        }, Promise.resolve());
      }).then(function(){
        idx.forEach(function(i, n){ if (known[hashes[n]]) slides[i].image_data = 'dsimg:' + hashes[n]; });
      });
    });
  }

  function slidesOf(body, kind){ return kind === 'save' ? (body.ppt_data && body.ppt_data.slides) : body.slides; }

  /* Send one request with refs; if the server reports missing files, re-upload once. */
  function sendOffloaded(url, opts, kind){
    var body;
    try { body = JSON.parse(opts.body); } catch(e) { return inner(url, opts); }
    var base = restBase(url);
    function attempt(force){
      var b = JSON.parse(JSON.stringify(body)); // never touch the editor's own objects
      return offload(slidesOf(b, kind), base, opts, force).then(function(){
        var o = {}; for (var k in opts) o[k] = opts[k];
        o.body = JSON.stringify(b);
        return inner(url, o);
      });
    }
    return attempt(false).then(function(r){
      return r.clone().json().then(function(d){
        if (d && d.missing_images && d.missing_images.length) {
          d.missing_images.forEach(function(h){ delete known[h]; });
          return attempt(true);
        }
        return r;
      }, function(){ return r; });
    }).catch(function(err){
      // Offload itself failed (e.g. very old browser) — fall back to the original request.
      if (err && err.name === 'AbortError') throw err;
      return inner(url, opts);
    });
  }

  /* ── single-flight save-ppt ── */
  var inflight = null;     // Promise of the running save
  var pending  = null;     // {url, opts, waiters:[{resolve,reject}]}
  var lastId   = '';

  function runSave(url, opts){
    var p = sendOffloaded(url, opts, 'save');
    inflight = p;
    function next(){
      inflight = null;
      if (!pending) return;
      var job = pending; pending = null;
      // A queued save for a brand-new deck must reuse the id the first save created.
      try {
        var b = JSON.parse(job.opts.body);
        if (!b.ppt_id && lastId) { b.ppt_id = lastId; job.opts.body = JSON.stringify(b); }
      } catch(e) {}
      runSave(job.url, job.opts).then(function(r){
        job.waiters.forEach(function(w){ w.resolve(r.clone()); });
      }, function(e){
        job.waiters.forEach(function(w){ w.reject(e); });
      });
    }
    p.then(function(r){
      return r.clone().json().then(function(d){ if (d && d.success && d.ppt_id) lastId = d.ppt_id; }, function(){});
    }, function(){}).then(next, next);
    return p;
  }

  window.fetch = function(input, init){
    var url = urlOf(input);
    var opts = init || {};
    if (typeof opts.body !== 'string' || url.indexOf('/ds/v1/') === -1) return inner.apply(this, arguments);

    if (url.indexOf('/ds/v1/save-ppt') !== -1) {
      if (!inflight) return runSave(url, opts);
      return new Promise(function(resolve, reject){
        if (!pending) pending = {url:url, opts:null, waiters:[]};
        var o = {}; for (var k in opts) o[k] = opts[k];
        pending.url = url; pending.opts = o;               // newest data wins
        pending.waiters.push({resolve:resolve, reject:reject});
      });
    }
    if (url.indexOf('/ds/v1/build-pptx-v2') !== -1) return sendOffloaded(url, opts, 'build');
    if (url.indexOf('/ds/v1/create-share-link') !== -1) return sendOffloaded(url, opts, 'share');
    return inner.apply(this, arguments);
  };
})();
</script>
	<?php
}, 2 );
