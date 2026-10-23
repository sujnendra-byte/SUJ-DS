<?php
/**
 * Code Snippets export — Review & Rating Popup with Improve Feedback [CLONE - moved to left side]
 *
 * Snippet ID : 810
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-124
 * Modified   : 2026-09-09 02:40:48
 * Load order : 130 of 144
 */
/**
 * Snippet: Review & Rating Popup + "Review Us" button (homepage, bottom) + WP Admin Reviews list
 * - "Review Us" button shown only on the homepage, pinned bottom-right
 * - Detailed comment box for ALL ratings (1-5 stars): required for 1-3, optional for 4-5
 * - Every submission is stored as a "Review" entry visible in wp-admin (with average rating)
 * - 1-3 star reviews are also emailed to the site admin for quick attention
 * - Auto-popup triggers (once per cooldown window, so it never nags):
 *     - 15 minutes of browsing the site in one visit
 *     - clicking any "Download" action (generated image / presentation / video / audio, templates, etc.)
 * - A 7-day cooldown applies between automatic popups; the manual button always works regardless.
 * - "Don't show this again" permanently stops the AUTO popup (button still always works)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'dstg_review_popup_output' ) ) :

/** 1. Register the "Reviews" custom post type so submissions show in wp-admin */
add_action( 'init', 'dstg_register_review_cpt' );
function dstg_register_review_cpt() {
	register_post_type( 'dstg_review', array(
		'labels' => array(
			'name'          => 'Reviews',
			'singular_name' => 'Review',
			'menu_name'     => 'Reviews',
			'all_items'     => 'All Reviews',
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-star-filled',
		'menu_position'       => 26,
		'supports'            => array( 'title' ),
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'hierarchical'        => false,
		'has_archive'         => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
	) );
}

/** 2. Custom admin list-table columns: rating, review text, reviewer, page */
add_filter( 'manage_dstg_review_posts_columns', 'dstg_review_columns' );
function dstg_review_columns( $columns ) {
	$new = array();
	$new['cb']            = $columns['cb'];
	$new['dstg_rating']   = 'Rating';
	$new['dstg_comment']  = 'Review';
	$new['dstg_reviewer'] = 'From';
	$new['dstg_page']     = 'Page';
	$new['date']          = $columns['date'];
	return $new;
}

add_action( 'manage_dstg_review_posts_custom_column', 'dstg_review_column_content', 10, 2 );
function dstg_review_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'dstg_rating':
			$rating = (int) get_post_meta( $post_id, '_dstg_rating', true );
			echo str_repeat( '&#9733;', $rating ) . str_repeat( '&#9734;', 5 - $rating );
			break;
		case 'dstg_comment':
			$text = get_post_field( 'post_content', $post_id );
			echo $text ? esc_html( wp_trim_words( $text, 25 ) ) : '<em>(no comment left)</em>';
			break;
		case 'dstg_reviewer':
			echo esc_html( get_post_meta( $post_id, '_dstg_reviewer', true ) ?: 'Guest' );
			break;
		case 'dstg_page':
			$url = get_post_meta( $post_id, '_dstg_page_url', true );
			if ( $url ) {
				$path = wp_parse_url( $url, PHP_URL_PATH );
				echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $path ? $path : $url ) . '</a>';
			}
			break;
	}
}

add_filter( 'manage_edit-dstg_review_sortable_columns', 'dstg_review_sortable_columns' );
function dstg_review_sortable_columns( $columns ) {
	$columns['dstg_rating'] = 'dstg_rating';
	return $columns;
}

add_action( 'pre_get_posts', 'dstg_review_sort_by_rating' );
function dstg_review_sort_by_rating( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->get( 'post_type' ) !== 'dstg_review' ) {
		return;
	}
	if ( $query->get( 'orderby' ) === 'dstg_rating' ) {
		$query->set( 'meta_key', '_dstg_rating' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}

/** 3. Average-rating summary above the Reviews list in wp-admin */
add_action( 'admin_notices', 'dstg_reviews_summary_notice' );
function dstg_reviews_summary_notice() {
	$screen = get_current_screen();
	if ( ! $screen || $screen->id !== 'edit-dstg_review' ) {
		return;
	}

	global $wpdb;
	$rows = $wpdb->get_col( $wpdb->prepare(
		"SELECT pm.meta_value FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'",
		'_dstg_rating',
		'dstg_review'
	) );
	$rows  = array_map( 'intval', $rows );
	$count = count( $rows );
	$avg   = $count ? round( array_sum( $rows ) / $count, 2 ) : 0;

	echo '<div class="notice notice-info"><p><strong>Average rating:</strong> ' . esc_html( $avg ) . ' / 5 from ' . esc_html( $count ) . ' review' . ( 1 === $count ? '' : 's' ) . '</p></div>';
}

/** 4. Front-end button + popup */
add_action( 'wp_footer', 'dstg_review_popup_output', 100 );
function dstg_review_popup_output() {

	if ( is_admin() ) {
		return;
	}

	$ajax_url        = admin_url( 'admin-ajax.php' );
	$nonce           = wp_create_nonce( 'dstg_review_feedback_nonce' );
	$current_url     = esc_url( home_url( add_query_arg( array(), $_SERVER['REQUEST_URI'] ?? '' ) ) );
	$show_button_now = is_front_page();
	?>
	<style>
		#dstg-review-bottombar {
			position: fixed;
			bottom: 0;
			left: 0;
			right: 0;
			z-index: 999999;
			display: flex;
			justify-content: flex-start;
			align-items: center;
			padding: 16px 20px;
			pointer-events: none;
		}
		#dstg-review-open-btn {
			pointer-events: auto;
			background: linear-gradient(135deg,#6c5ce7,#8e6cf1);
			color: #fff;
			border: none;
			border-radius: 999px;
			padding: 9px 18px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			box-shadow: 0 4px 14px rgba(108,92,231,0.35);
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
			transition: transform 0.15s ease, box-shadow 0.15s ease;
		}
		#dstg-review-open-btn:hover {
			transform: translateY(-1px);
			box-shadow: 0 6px 18px rgba(108,92,231,0.45);
		}

		#dstg-review-overlay {
			display: none;
			position: fixed;
			inset: 0;
			background: rgba(20,20,30,0.55);
			z-index: 1000000;
			align-items: center;
			justify-content: center;
			padding: 20px;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
		}
		#dstg-review-overlay.dstg-open {
			display: flex;
		}
		#dstg-review-modal {
			background: #fff;
			border-radius: 16px;
			max-width: 420px;
			width: 100%;
			padding: 28px 24px 20px;
			position: relative;
			box-shadow: 0 20px 60px rgba(0,0,0,0.25);
			text-align: center;
			animation: dstgPop 0.18s ease;
		}
		@keyframes dstgPop {
			from { transform: scale(0.94); opacity: 0; }
			to   { transform: scale(1); opacity: 1; }
		}
		#dstg-review-close {
			position: absolute;
			top: 10px;
			right: 14px;
			background: none;
			border: none;
			font-size: 22px;
			line-height: 1;
			cursor: pointer;
			color: #888;
		}
		#dstg-review-close:hover { color: #333; }
		#dstg-review-modal h3 {
			margin: 6px 0 8px;
			font-size: 19px;
			color: #222;
		}
		#dstg-review-modal p {
			margin: 0 0 16px;
			color: #666;
			font-size: 14px;
			line-height: 1.5;
		}
		#dstg-stars {
			font-size: 38px;
			letter-spacing: 6px;
			margin: 10px 0 18px;
			cursor: pointer;
		}
		#dstg-stars span {
			color: #ddd;
			transition: color 0.1s ease, transform 0.1s ease;
			display: inline-block;
		}
		#dstg-stars span.dstg-hover,
		#dstg-stars span.dstg-selected {
			color: #ffb400;
			transform: scale(1.08);
		}
		#dstg-comment-text {
			width: 100%;
			box-sizing: border-box;
			border: 1px solid #ddd;
			border-radius: 10px;
			padding: 10px 12px;
			font-size: 14px;
			font-family: inherit;
			resize: vertical;
			margin-bottom: 14px;
		}
		.dstg-btn-primary {
			background: linear-gradient(135deg,#6c5ce7,#8e6cf1);
			color: #fff;
			border: none;
			border-radius: 999px;
			padding: 11px 26px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			width: 100%;
		}
		.dstg-btn-primary:disabled {
			opacity: 0.55;
			cursor: not-allowed;
		}
		#dstg-dont-show {
			display: block;
			margin-top: 16px;
			font-size: 12.5px;
			color: #999;
			cursor: pointer;
			user-select: none;
		}
		#dstg-dont-show input {
			margin-right: 6px;
			vertical-align: middle;
		}
		.dstg-step { display: none; }
		.dstg-step.dstg-active { display: block; }
		#dstg-review-error {
			color: #d63638;
			font-size: 13px;
			margin: -8px 0 12px;
			display: none;
		}
	</style>

	<?php if ( $show_button_now ) : ?>
	<div id="dstg-review-bottombar">
		<button type="button" id="dstg-review-open-btn">&#9733; Review Us</button>
	</div>
	<?php endif; ?>

	<div id="dstg-review-overlay">
		<div id="dstg-review-modal">
			<button type="button" id="dstg-review-close" aria-label="Close">&times;</button>

			<div class="dstg-step dstg-active" data-step="rate">
				<h3>How was your experience?</h3>
				<p>Tap a star to let us know.</p>
				<div id="dstg-stars">
					<span data-star="1">&#9733;</span><span data-star="2">&#9733;</span><span data-star="3">&#9733;</span><span data-star="4">&#9733;</span><span data-star="5">&#9733;</span>
				</div>
			</div>

			<div class="dstg-step" data-step="comment">
				<h3 id="dstg-comment-title">Tell us more</h3>
				<p id="dstg-comment-subtitle">What should we improve?</p>
				<div id="dstg-review-error">Please enter a few details before submitting.</div>
				<textarea id="dstg-comment-text" rows="4" placeholder="Tell us what we can do better..."></textarea>
				<button type="button" class="dstg-btn-primary" id="dstg-submit-comment">Submit Review</button>
			</div>

			<div class="dstg-step" data-step="submitted">
				<h3>Thank you! &#127881;</h3>
				<p>Your review has been submitted. We really appreciate it.</p>
				<button type="button" class="dstg-btn-primary" data-close>Close</button>
			</div>

			<label id="dstg-dont-show">
				<input type="checkbox" id="dstg-dont-show-checkbox" /> Don't show this popup automatically again
			</label>
		</div>
	</div>

	<script>
	(function () {
		var DISMISS_KEY    = 'dstg_review_popup_dismissed_forever';
		var LAST_SHOWN_KEY = 'dstg_review_last_auto_shown';
		var SESSION_KEY    = 'dstg_review_session_start';
		var COOLDOWN_MS    = 7 * 24 * 60 * 60 * 1000;   // don't auto-show more than once a week
		var TIME_ON_SITE_MS = 15 * 60 * 1000;           // 15 minutes browsing the site
		var DOWNLOAD_DELAY_MS = 1800;                   // let the actual download start first

		var AJAX_URL = <?php echo wp_json_encode( $ajax_url ); ?>;
		var NONCE    = <?php echo wp_json_encode( $nonce ); ?>;
		var PAGE_URL = <?php echo wp_json_encode( $current_url ); ?>;

		var overlay     = document.getElementById( 'dstg-review-overlay' );
		var openBtn     = document.getElementById( 'dstg-review-open-btn' );
		var closeBtn    = document.getElementById( 'dstg-review-close' );
		var dontShow    = document.getElementById( 'dstg-dont-show-checkbox' );
		var stars       = overlay.querySelectorAll( '#dstg-stars span' );
		var steps       = overlay.querySelectorAll( '.dstg-step' );
		var commentTa   = document.getElementById( 'dstg-comment-text' );
		var commentTitle    = document.getElementById( 'dstg-comment-title' );
		var commentSubtitle = document.getElementById( 'dstg-comment-subtitle' );
		var submitBtn   = document.getElementById( 'dstg-submit-comment' );
		var errorBox    = document.getElementById( 'dstg-review-error' );
		var selectedRating  = 0;
		var commentRequired = false;

		function isDismissedForever() {
			try {
				return localStorage.getItem( DISMISS_KEY ) === '1';
			} catch ( e ) {
				return false;
			}
		}
		function dismissForever() {
			try {
				localStorage.setItem( DISMISS_KEY, '1' );
			} catch ( e ) {}
		}
		function canAutoShow() {
			if ( isDismissedForever() ) {
				return false;
			}
			try {
				var last = parseInt( localStorage.getItem( LAST_SHOWN_KEY ) || '0', 10 );
				return ( Date.now() - last ) >= COOLDOWN_MS;
			} catch ( e ) {
				return true;
			}
		}
		function markAutoShown() {
			try {
				localStorage.setItem( LAST_SHOWN_KEY, String( Date.now() ) );
			} catch ( e ) {}
		}

		function showStep( name ) {
			steps.forEach( function ( el ) {
				el.classList.toggle( 'dstg-active', el.getAttribute( 'data-step' ) === name );
			} );
		}

		function resetModal() {
			selectedRating = 0;
			stars.forEach( function ( s ) { s.classList.remove( 'dstg-selected', 'dstg-hover' ); } );
			commentTa.value = '';
			errorBox.style.display = 'none';
			dontShow.checked = false;
			showStep( 'rate' );
		}

		function openPopup() {
			resetModal();
			overlay.classList.add( 'dstg-open' );
		}

		function closePopup() {
			overlay.classList.remove( 'dstg-open' );
			if ( dontShow.checked ) {
				dismissForever();
			}
		}

		// Manual trigger (the "Review Us" button) always works, no cooldown.
		if ( openBtn ) {
			openBtn.addEventListener( 'click', openPopup );
		}
		closeBtn.addEventListener( 'click', closePopup );
		overlay.addEventListener( 'click', function ( e ) {
			if ( e.target === overlay ) closePopup();
		} );
		overlay.querySelectorAll( '[data-close]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', closePopup );
		} );

		stars.forEach( function ( star ) {
			var val = parseInt( star.getAttribute( 'data-star' ), 10 );
			star.addEventListener( 'mouseenter', function () {
				stars.forEach( function ( s ) {
					s.classList.toggle( 'dstg-hover', parseInt( s.getAttribute( 'data-star' ), 10 ) <= val );
				} );
			} );
			star.addEventListener( 'mouseleave', function () {
				stars.forEach( function ( s ) { s.classList.remove( 'dstg-hover' ); } );
			} );
			star.addEventListener( 'click', function () {
				selectedRating = val;
				stars.forEach( function ( s ) {
					s.classList.toggle( 'dstg-selected', parseInt( s.getAttribute( 'data-star' ), 10 ) <= val );
				} );
				// Any rating submitted -> stop auto-popups from nagging again.
				dismissForever();

				errorBox.style.display = 'none';
				commentTa.value = '';

				if ( val >= 4 ) {
					commentRequired = false;
					commentTitle.textContent = 'Awesome! 🎉';
					commentSubtitle.textContent = 'Want to tell us what you loved? (optional)';
					commentTa.placeholder = 'What did you enjoy most?';
				} else {
					commentRequired = true;
					commentTitle.textContent = 'Sorry to hear that';
					commentSubtitle.textContent = 'What should we improve?';
					commentTa.placeholder = 'Tell us what we can do better...';
				}
				submitBtn.textContent = 'Submit Review';
				showStep( 'comment' );
			} );
		} );

		submitBtn.addEventListener( 'click', function () {
			var message = commentTa.value.trim();
			if ( commentRequired && ! message ) {
				errorBox.style.display = 'block';
				return;
			}
			errorBox.style.display = 'none';
			submitBtn.disabled = true;
			submitBtn.textContent = 'Sending...';

			var body = new URLSearchParams();
			body.set( 'action', 'dstg_submit_review_feedback' );
			body.set( 'nonce', NONCE );
			body.set( 'rating', String( selectedRating ) );
			body.set( 'message', message );
			body.set( 'page_url', PAGE_URL );

			fetch( AJAX_URL, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: body.toString()
			} ).then( function () {
				showStep( 'submitted' );
			} ).catch( function () {
				showStep( 'submitted' );
			} ).finally( function () {
				submitBtn.disabled = false;
				submitBtn.textContent = 'Submit Review';
			} );
		} );

		function triggerAutoShow() {
			if ( overlay.classList.contains( 'dstg-open' ) ) {
				return;
			}
			if ( ! canAutoShow() ) {
				return;
			}
			markAutoShown();
			openPopup();
		}

		// Trigger 1: ~15 minutes of browsing the site in this visit (persists across page loads
		// in the same tab via sessionStorage, resets when the tab/browser is closed).
		(function scheduleTimeOnSiteTrigger() {
			var start;
			try {
				start = parseInt( sessionStorage.getItem( SESSION_KEY ) || '', 10 );
				if ( ! start ) {
					start = Date.now();
					sessionStorage.setItem( SESSION_KEY, String( start ) );
				}
			} catch ( e ) {
				start = Date.now();
			}
			var remaining = TIME_ON_SITE_MS - ( Date.now() - start );
			if ( remaining <= 0 ) {
				triggerAutoShow();
			} else {
				window.setTimeout( triggerAutoShow, remaining );
			}
		})();

		// Trigger 2: the visitor downloads something (generated image / presentation / video /
		// audio, a template, etc.) Matches the site's download buttons/links generically.
		document.addEventListener( 'click', function ( e ) {
			var el = e.target.closest(
				'.ds-cdl, [aria-label="Download" i], [title="Download" i], a[download], ' +
				'a[href$=".pptx"], a[href$=".ppt"], a[href$=".pdf"], a[href$=".zip"], ' +
				'a[href$=".png"], a[href$=".jpg"], a[href$=".jpeg"], a[href$=".mp4"], a[href$=".mp3"]'
			);
			if ( el ) {
				window.setTimeout( triggerAutoShow, DOWNLOAD_DELAY_MS );
			}
		}, true );
	})();
	</script>
	<?php
}

/** 5. AJAX handler: store every review in wp-admin, email admin for 1-3 star reviews */
add_action( 'wp_ajax_dstg_submit_review_feedback', 'dstg_handle_review_feedback' );
add_action( 'wp_ajax_nopriv_dstg_submit_review_feedback', 'dstg_handle_review_feedback' );
function dstg_handle_review_feedback() {
	check_ajax_referer( 'dstg_review_feedback_nonce', 'nonce' );

	$rating   = isset( $_POST['rating'] ) ? max( 1, min( 5, intval( $_POST['rating'] ) ) ) : 0;
	$message  = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$page_url = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '';

	if ( $rating < 1 ) {
		wp_send_json_error( array( 'message' => 'Invalid rating.' ) );
	}
	if ( $rating <= 3 && empty( $message ) ) {
		wp_send_json_error( array( 'message' => 'Please tell us what to improve.' ) );
	}

	$current_user = wp_get_current_user();
	$reviewer     = ( $current_user && $current_user->exists() ) ? $current_user->display_name : 'Guest';

	$post_id = wp_insert_post( array(
		'post_type'    => 'dstg_review',
		'post_status'  => 'publish',
		'post_title'   => sprintf( '%d-star review - %s', $rating, current_time( 'Y-m-d H:i' ) ),
		'post_content' => $message,
	) );

	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_dstg_rating', $rating );
		update_post_meta( $post_id, '_dstg_page_url', $page_url );
		update_post_meta( $post_id, '_dstg_reviewer', $reviewer );
	}

	// Only nudge the admin by email for lower ratings that need attention.
	if ( $rating <= 3 && ! empty( $message ) ) {
		$to      = get_option( 'admin_email' );
		$subject = sprintf( '[%s] New %d-star feedback: what to improve', get_bloginfo( 'name' ), $rating );
		$body    = "Rating: {$rating}/5

"
			. "What they said we should improve:
{$message}

"
			. "From: {$reviewer}
"
			. "Page: {$page_url}
"
			. "Date: " . current_time( 'mysql' );
		wp_mail( $to, $subject, $body );
	}

	wp_send_json_success();
}

endif;
