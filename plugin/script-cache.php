<?php
/**
 * DesignStager V34 - script cache.
 *
 * The site prints most of its JavaScript INSIDE the page (inline <script> blocks): about 350 KB on every page for a
 * logged-in user and about 2.3 MB on the AI Presentation page. Inline script cannot be cached by the browser, so it
 * was downloaded again on every single page view.
 *
 * This file changes nothing about WHAT runs or in which order. Just before a page is sent, every inline script that
 * is byte-for-byte identical to a copy shipped in assets/sc/ is replaced by a normal <script src="..."> tag pointing
 * at that copy. The browser (and a CDN) then keeps the file, and the next page view no longer downloads it.
 *
 *   - Only scripts whose content is exactly known are touched (matched by an MD5 of the content). A script that
 *     carries anything per-user or per-request (a nonce, a user id, a URL of this site) never matches and stays
 *     inline, exactly as before.
 *   - A plain <script src> tag runs at the same place and in the same order as the inline block it replaces.
 *   - Runs of neighbouring self-contained scripts (the design templates) are served as ONE file. Each member is
 *     wrapped in its own try / catch, so an error in one still cannot stop the others - same as separate blocks.
 *   - A script that begins with a few settings (the site address, a price) and continues with a constant rest is
 *     split at that point: the settings stay in the page, the rest is served as a file that runs right after them.
 *   - Any problem (unexpected markup, missing file list) returns the page untouched.
 *
 * Switches:
 *   define( 'DSV42_NO_SCRIPT_CACHE', true );   in wp-config.php turns the whole thing off (the DSV41 / DSV40 / DSV39 / DSV38 switches still work).
 *   ?dsv_inline=1 on any URL shows that one page the old way (for comparing).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dsv36_sc_map() {
	static $m = null;
	if ( null === $m ) {
		$f = DSV42_PATH . 'assets/sc/map.php';
		$m = is_file( $f ) ? (array) include $f : array();
		if ( empty( $m['files'] ) || ! is_array( $m['files'] ) ) {
			$m = array( 'files' => array(), 'bundles' => array(), 'tails' => array() );
		}
		if ( empty( $m['bundles'] ) || ! is_array( $m['bundles'] ) ) {
			$m['bundles'] = array();
		}
		if ( empty( $m['tails'] ) || ! is_array( $m['tails'] ) ) {
			$m['tails'] = array();
		}
	}
	return $m;
}

function dsv36_sc_tag( $attrs, $file ) {
	$url = DSV42_URL . 'assets/sc/' . $file . '?ver=' . rawurlencode( DSV42_VERSION );
	return '<script' . $attrs . ' src="' . esc_url( $url ) . '"></script>';
}

function dsv36_sc_process( $html ) {
	if ( ! is_string( $html ) || strlen( $html ) < 2000 || false === stripos( $html, '</html>' ) ) {
		return $html;
	}
	$map = dsv36_sc_map();
	if ( empty( $map['files'] ) ) {
		return $html;
	}
	try {
		/* 1. cut the page into text / script pieces with plain string search (no regular expression on big blocks) */
		$pieces = array();           // array( 0 => text ) or array( 0 => whole block, 1 => attributes, 2 => body )
		$pos = 0; $len = strlen( $html );
		while ( $pos < $len ) {
			$a = stripos( $html, '<script', $pos );
			if ( false === $a ) { break; }
			$c = isset( $html[ $a + 7 ] ) ? $html[ $a + 7 ] : '';
			if ( '>' !== $c && ' ' !== $c && "\n" !== $c && "\t" !== $c && "\r" !== $c ) { $pieces[] = array( substr( $html, $pos, $a + 7 - $pos ) ); $pos = $a + 7; continue; }
			$b = strpos( $html, '>', $a );
			if ( false === $b ) { break; }
			$e = stripos( $html, '</script>', $b );
			if ( false === $e ) { break; }
			if ( $a > $pos ) { $pieces[] = array( substr( $html, $pos, $a - $pos ) ); }
			$pieces[] = array( substr( $html, $a, $e + 9 - $a ), substr( $html, $a + 7, $b - $a - 7 ), substr( $html, $b + 1, $e - $b - 1 ) );
			$pos = $e + 9;
		}
		if ( $pos < $len ) { $pieces[] = array( substr( $html, $pos ) ); }

		/* 2. which script pieces are known files */
		$known = array(); $split = array(); $any = false;
		foreach ( $pieces as $i => $p ) {
			if ( ! isset( $p[2] ) || strlen( $p[2] ) < 1500 ) { continue; }
			$at = $p[1];
			if ( preg_match( '/\bsrc\s*=/i', $at ) ) { continue; }
			if ( preg_match( '/\btype\s*=\s*["\']?([^"\'\s>]+)/i', $at, $t ) && ! in_array( strtolower( $t[1] ), array( 'text/javascript', 'application/javascript' ), true ) ) { continue; }
			$h = md5( $p[2] );
			if ( isset( $map['files'][ $h ] ) ) { $known[ $i ] = $h; $any = true; continue; }
			/* a script that starts with a few settings (site address, costs) and continues with a constant rest:
			   the settings stay in the page, the rest becomes a cached file that runs right after them */
			$bl = strlen( $p[2] );
			if ( $bl < 6100 || empty( $map['tails'] ) ) { continue; }
			$end = md5( substr( $p[2], -64 ) );
			if ( ! isset( $map['tails'][ $end ] ) ) { continue; }
			foreach ( $map['tails'][ $end ] as $cand ) {
				$tl = (int) $cand[0];
				if ( $tl < $bl && md5( substr( $p[2], $bl - $tl ) ) === $cand[1] ) {
					$split[ $i ] = array( $tl, $cand[1] ); $any = true;
					break;
				}
			}
		}
		if ( ! $any ) {
			return $html;
		}
		foreach ( $split as $i => $sp ) {
			$p = $pieces[ $i ];
			$pieces[ $i ] = array( '<script' . $p[1] . '>' . substr( $p[2], 0, strlen( $p[2] ) - $sp[0] ) . '</script>' . dsv36_sc_tag( ' data-dsv-rest="1"', 't-' . $sp[1] . '.js' ) );
		}

		/* 3. neighbouring self-contained scripts -> one file, everything else -> its own file */
		$idx = array_keys( $known ); $n = count( $idx ); $k = 0;
		while ( $k < $n ) {
			$i = $idx[ $k ]; $h = $known[ $i ];
			$run = array( $i );
			if ( 2 === (int) $map['files'][ $h ] ) {
				$j = $k + 1; $prev = $i;
				while ( $j < $n ) {
					$ni = $idx[ $j ];
					if ( 2 !== (int) $map['files'][ $known[ $ni ] ] ) { break; }
					$gap_ok = true;
					for ( $g = $prev + 1; $g < $ni; $g++ ) { if ( isset( $pieces[ $g ][2] ) || '' !== trim( $pieces[ $g ][0] ) ) { $gap_ok = false; break; } }
					if ( ! $gap_ok ) { break; }
					$run[] = $ni; $prev = $ni; $j++;
				}
			}
			$done = false;
			if ( count( $run ) > 1 ) {
				$hs = array(); foreach ( $run as $ri ) { $hs[] = $known[ $ri ]; }
				$key = md5( implode( ',', $hs ) );
				if ( isset( $map['bundles'][ $key ] ) ) {
					$pieces[ $run[0] ] = array( dsv36_sc_tag( ' data-dsv-bundle="' . count( $run ) . '"', 'b-' . $key . '.js' ) );
					for ( $r = 1; $r < count( $run ); $r++ ) { $pieces[ $run[ $r ] ] = array( '' ); }
					$done = true;
				}
			}
			if ( ! $done ) {
				foreach ( $run as $ri ) { $pieces[ $ri ] = array( dsv36_sc_tag( $pieces[ $ri ][1], $known[ $ri ] . '.js' ) ); }
			}
			$k += count( $run );
		}
		$out = '';
		foreach ( $pieces as $p ) { $out .= $p[0]; }
		return $out;
	} catch ( Throwable $e ) {
		return $html;
	}
}

add_action( 'template_redirect', function () {
	if ( defined( 'DSV42_NO_SCRIPT_CACHE' ) && DSV42_NO_SCRIPT_CACHE ) { return; }
	if ( defined( 'DSV41_NO_SCRIPT_CACHE' ) && DSV41_NO_SCRIPT_CACHE ) { return; }
	if ( defined( 'DSV40_NO_SCRIPT_CACHE' ) && DSV40_NO_SCRIPT_CACHE ) { return; }
	if ( defined( 'DSV39_NO_SCRIPT_CACHE' ) && DSV39_NO_SCRIPT_CACHE ) { return; }   /* the V39 switch still works */
	if ( defined( 'DSV38_NO_SCRIPT_CACHE' ) && DSV38_NO_SCRIPT_CACHE ) { return; }   /* the V38 switch still works */
	if ( defined( 'DSV37_NO_SCRIPT_CACHE' ) && DSV37_NO_SCRIPT_CACHE ) { return; }   /* the V37 switch still works */
	if ( defined( 'DSV36_NO_SCRIPT_CACHE' ) && DSV36_NO_SCRIPT_CACHE ) { return; }   /* the V36 switch still works */
	if ( defined( 'DSV35_NO_SCRIPT_CACHE' ) && DSV35_NO_SCRIPT_CACHE ) { return; }   /* the V35 switch still works */
	if ( defined( 'DSV34_NO_SCRIPT_CACHE' ) && DSV34_NO_SCRIPT_CACHE ) { return; }   /* the V34 switch still works */
	if ( is_admin() || is_feed() || is_robots() || is_trackback() ) { return; }
	if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) ) { return; }
	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) : 'GET';
	if ( 'GET' !== $method ) { return; }
	if ( isset( $_GET['dsv_inline'] ) ) { return; }
	ob_start( 'dsv36_sc_process' );
}, 0 );
