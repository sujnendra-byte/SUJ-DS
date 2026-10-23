<?php
/**
 * Code Snippets export — DS Delete My Account (24-hour delay)
 *
 * Snippet ID : 1069
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager
 * Modified   : 2026-10-07
 * Load order : 263
 */
/**
 * DS Delete My Account
 * Run: Everywhere
 * NEW SNIPPET - does not modify any existing snippet.
 *
 * What it does
 *  - Adds "Delete my account" under "Sign Out" in the My Account popup.
 *  - The button opens a confirmation box above the popup: what is lost, a
 *    tick box the visitor must tick, and the date the account will be deleted.
 *  - Nothing is deleted straight away. The account is deleted 24 hours later,
 *    and the visitor can change their mind until then ("Keep my account").
 *  - Confirming cancels an active subscription straight away (it will not
 *    renew). When the time is up the subscription is stopped completely and
 *    the account is deleted with wp_delete_user(), so every existing clean-up
 *    (generated files, voice samples, referral records) runs as usual.
 *  - Items the user already published on the Marketplace stay available:
 *    they are handed to the site administrator just before the deletion.
 *  - A basic record is kept (name, email address, date): Users > Deleted
 *    accounts. The Terms page gets a "Deleting your account" section.
 *  - An email address that had a deleted account does not get the free
 *    sign-up credits a second time. Only a one-way hash of the address is kept.
 *  - Administrators cannot delete their account this way: they see the
 *    button and the box, but the tick box refuses and says why.
 *
 * Addresses: POST ds/v1/account-delete, POST ds/v1/account-delete-cancel
 * Filters:   ds_del_delay (seconds, default one day)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( defined( 'DS_DEL_LOADED' ) ) {
	return;
}
define( 'DS_DEL_LOADED', '1.1.0' );

/* ------------------------------------------------------------------ helpers */

function ds_del_delay() {
	return max( HOUR_IN_SECONDS, (int) apply_filters( 'ds_del_delay', DAY_IN_SECONDS ) );
}

/** Time (unix) this account is due to be deleted, or 0. */
function ds_del_due( $uid ) {
	return max( 0, (int) get_user_meta( (int) $uid, 'ds_del_at', true ) );
}

function ds_del_allowed( $uid ) {
	return $uid > 0 && ! user_can( (int) $uid, 'manage_options' );
}

function ds_del_when_text( $ts ) {
	return wp_date( 'j F Y, g:i a', (int) $ts );
}

function ds_del_norm_email( $email ) {
	$e = strtolower( trim( (string) $email ) );
	if ( function_exists( 'dsrf_norm_email' ) ) {
		return (string) dsrf_norm_email( $e );
	}
	if ( false === strpos( $e, '@' ) ) {
		return $e;
	}
	list( $local, $domain ) = explode( '@', $e, 2 );
	$local = preg_replace( '/\+.*$/', '', $local );
	if ( 'gmail.com' === $domain || 'googlemail.com' === $domain ) {
		$local  = str_replace( '.', '', $local );
		$domain = 'gmail.com';
	}
	return $local . '@' . $domain;
}

/* --------------------------------------------------------------- REST routes */

add_action( 'rest_api_init', function () {
	register_rest_route( 'ds/v1', '/account-delete', array(
		'methods'             => 'POST',
		'permission_callback' => function () {
			return is_user_logged_in();
		},
		'callback'            => function ( WP_REST_Request $req ) {
			$uid = get_current_user_id();
			if ( ! ds_del_allowed( $uid ) ) {
				return new WP_Error( 'ds_del_admin', 'Administrator accounts cannot be deleted here.', array( 'status' => 403 ) );
			}
			if ( ! rest_sanitize_boolean( $req->get_param( 'confirm' ) ) ) {
				return new WP_Error( 'ds_del_confirm', 'Please tick the box to confirm.', array( 'status' => 400 ) );
			}
			$due = ds_del_due( $uid );
			if ( ! $due ) {
				$due = time() + ds_del_delay();
				update_user_meta( $uid, 'ds_del_at', $due );
				update_user_meta( $uid, 'ds_del_requested', time() );
				/* Cancel an active subscription now, the same way the "Cancel
				   Subscription" button does: it will not renew. */
				if ( '' !== (string) get_user_meta( $uid, 'ds_subscription_id', true )
					&& 'cancelled' !== (string) get_user_meta( $uid, 'ds_subscription_status', true )
					&& function_exists( 'ds_rest_cancel_subscription' ) ) {
					try {
						ds_rest_cancel_subscription( new WP_REST_Request( 'POST', '/ds/v1/cancel-subscription' ) );
					} catch ( \Throwable $e ) {
						update_user_meta( $uid, 'ds_del_cancel_error', substr( $e->getMessage(), 0, 200 ) );
					}
				}
				ds_del_mail( $uid, $due );
			}
			return rest_ensure_response( array( 'ok' => true, 'pending' => true, 'at' => $due, 'at_text' => ds_del_when_text( $due ) ) );
		},
	) );

	register_rest_route( 'ds/v1', '/account-delete-cancel', array(
		'methods'             => 'POST',
		'permission_callback' => function () {
			return is_user_logged_in();
		},
		'callback'            => function () {
			$uid = get_current_user_id();
			delete_user_meta( $uid, 'ds_del_at' );
			delete_user_meta( $uid, 'ds_del_requested' );
			return rest_ensure_response( array( 'ok' => true, 'pending' => false ) );
		},
	) );
} );

function ds_del_mail( $uid, $due ) {
	$u = get_userdata( $uid );
	if ( ! $u || ! is_email( $u->user_email ) ) {
		return;
	}
	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$body = 'Hi ' . $u->display_name . ",\n\n"
		. 'We received a request to delete your ' . $site . " account.\n\n"
		. 'Your account and everything in it will be permanently deleted on ' . ds_del_when_text( $due ) . ".\n\n"
		. 'Changed your mind? Sign in before then, open My Account and choose "Cancel deletion - keep my account": ' . home_url( '/login/' ) . "\n\n"
		. "If you did not ask for this, sign in and choose \"Cancel deletion - keep my account\", then change your password.\n\n"
		. 'Team ' . $site;
	wp_mail( $u->user_email, 'Your ' . $site . ' account is scheduled for deletion', $body );
}

/* ------------------------------------------------------- the actual deletion */

/** Deletes one account whose time is up. Returns true when it was deleted. */
function ds_del_execute( $uid ) {
	$uid = (int) $uid;
	$due = ds_del_due( $uid );
	if ( ! $due || $due > time() || ! ds_del_allowed( $uid ) || ! get_userdata( $uid ) ) {
		return false;
	}

	/* Stop an active subscription now, so a deleted account is never charged again. */
	$sub = (string) get_user_meta( $uid, 'ds_subscription_id', true );
	if ( '' !== $sub && function_exists( 'ds_get_razorpay_keys' ) ) {
		$keys = ds_get_razorpay_keys();
		if ( ! empty( $keys['key_id'] ) && ! empty( $keys['key_secret'] ) ) {
			wp_remote_post( 'https://api.razorpay.com/v1/subscriptions/' . rawurlencode( $sub ) . '/cancel', array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $keys['key_id'] . ':' . $keys['key_secret'] ), // phpcs:ignore
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( array( 'cancel_at_cycle_end' => 0 ) ),
				'timeout' => 20,
			) );
		}
	}

	$u     = get_userdata( $uid );
	$log   = get_option( 'ds_del_log', array() );
	$log   = is_array( $log ) ? $log : array();
	$log[] = array(
		'time'  => current_time( 'mysql' ),
		'name'  => (string) $u->display_name,
		'email' => (string) $u->user_email,
		'plan'  => function_exists( 'ds_get_user_plan' ) ? (string) ds_get_user_plan( $uid ) : '',
		'kept'  => ds_del_keep_marketplace( $uid ),
	);
	update_option( 'ds_del_log', array_slice( $log, -500 ), false );

	if ( ! function_exists( 'wp_delete_user' ) ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
	}
	return (bool) wp_delete_user( $uid );
}

/**
 * Published Marketplace items stay available. They (with their versions,
 * cover image and the upload they came from) are handed to the site
 * administrator, so deleting the account does not delete them.
 * Returns how many items were kept.
 */
function ds_del_keep_marketplace( $uid ) {
	global $wpdb;
	$uid   = (int) $uid;
	$items = get_posts( array( 'post_type' => 'dlm_download', 'post_status' => 'publish', 'author' => $uid, 'numberposts' => -1, 'fields' => 'ids', 'suppress_filters' => true ) );
	if ( ! $items ) {
		return 0;
	}
	$admins = get_users( array( 'role' => 'administrator', 'orderby' => 'ID', 'order' => 'ASC', 'number' => 1, 'fields' => 'ids' ) );
	$owner  = (int) apply_filters( 'ds_del_marketplace_owner', $admins ? (int) $admins[0] : 0, $uid );
	if ( $owner <= 0 || $owner === $uid ) {
		return 0;
	}
	$move = array();
	foreach ( $items as $id ) {
		$id     = (int) $id;
		$move[] = $id;
		$up     = (int) get_post_meta( $id, 'ds_creator_upload_id', true );
		$refs   = array( (int) get_post_thumbnail_id( $id ) );
		if ( $up > 0 ) {
			$move[] = $up;
			$refs[] = (int) get_post_meta( $up, 'ds_creator_thumbnail_id', true );
			$refs[] = (int) get_post_meta( $up, 'ds_creator_file_id', true );
		}
		foreach ( array_filter( array( $id, $up ) ) as $parent ) {
			$kids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_author = %d", $parent, $uid ) );
			$move = array_merge( $move, array_map( 'intval', (array) $kids ) );
		}
		$move = array_merge( $move, array_filter( $refs ) );
	}
	$move = array_values( array_unique( array_filter( array_map( 'intval', $move ) ) ) );
	foreach ( $move as $pid ) {
		$wpdb->update( $wpdb->posts, array( 'post_author' => $owner ), array( 'ID' => $pid, 'post_author' => $uid ) );
		clean_post_cache( $pid );
	}
	return count( $items );
}

/* Remember the address (one-way hash only) so the free sign-up credits are not given twice. */
add_action( 'delete_user', function ( $uid ) {
	$u = get_userdata( $uid );
	if ( ! $u || ! $u->user_email ) {
		return;
	}
	$reg = get_option( 'ds_del_emails', array() );
	$reg = is_array( $reg ) ? $reg : array();
	$reg[ sha1( ds_del_norm_email( $u->user_email ) ) ] = current_time( 'Y-m-d' );
	update_option( 'ds_del_emails', $reg, false );
}, 4 );

function ds_del_had_account( $email ) {
	$h = sha1( ds_del_norm_email( $email ) );
	foreach ( array( 'ds_del_emails', 'dsrf_deleted_emails' ) as $opt ) {
		$reg = get_option( $opt, array() );
		if ( is_array( $reg ) && isset( $reg[ $h ] ) ) {
			return true;
		}
	}
	return false;
}

/* Runs before the sign-up bonus snippet (priority 20), which skips an account already marked. */
add_action( 'user_register', function ( $uid ) {
	$u = get_userdata( $uid );
	if ( $u && $u->user_email && ds_del_had_account( $u->user_email ) ) {
		update_user_meta( $uid, 'ds_signup_bonus_given', 'returning' );
		update_user_meta( $uid, 'ds_del_returning', 1 );
	}
}, 15 );

/* Hourly check. */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ds_del_cron' ) ) {
		wp_schedule_event( time() + 300, 'hourly', 'ds_del_cron' );
	}
} );

add_action( 'ds_del_cron', function () {
	$ids = get_users( array(
		'meta_key'     => 'ds_del_at', // phpcs:ignore
		'meta_value'   => time(), // phpcs:ignore
		'meta_compare' => '<=',
		'meta_type'    => 'NUMERIC',
		'number'       => 10,
		'fields'       => 'ids',
	) );
	foreach ( (array) $ids as $id ) {
		ds_del_execute( (int) $id );
	}
} );

/* If the hourly check is late, the account is deleted the moment its owner next opens a page. */
add_action( 'init', function () {
	if ( ! is_user_logged_in() || is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	$uid = get_current_user_id();
	$due = ds_del_due( $uid );
	if ( $due && $due <= time() && ds_del_allowed( $uid ) ) {
		wp_logout();
		ds_del_execute( $uid );
		nocache_headers();
		wp_safe_redirect( add_query_arg( 'ds_account', 'deleted', home_url( '/' ) ) );
		exit;
	}
}, 20 );

/* --------------------------------------------------------- admin: user screen */

add_action( 'edit_user_profile', function ( $user ) {
	$due = ds_del_due( $user->ID );
	if ( ! $due ) {
		return;
	}
	?>
	<h2>Account deletion</h2>
	<table class="form-table" role="presentation"><tr><th scope="row">Scheduled</th><td>
		<p>This user asked to delete their account. It will be deleted on <strong><?php echo esc_html( ds_del_when_text( $due ) ); ?></strong>.</p>
		<label><input type="checkbox" name="ds_del_keep" value="1"> Cancel the deletion and keep this account</label>
	</td></tr></table>
	<?php
} );

add_action( 'edit_user_profile_update', function ( $uid ) {
	if ( current_user_can( 'edit_user', $uid ) && ! empty( $_POST['ds_del_keep'] ) ) { // phpcs:ignore -- nonce checked by core on this screen
		delete_user_meta( $uid, 'ds_del_at' );
		delete_user_meta( $uid, 'ds_del_requested' );
	}
} );

add_action( 'admin_menu', function () {
	add_users_page( 'Deleted accounts', 'Deleted accounts', 'manage_options', 'ds-deleted-accounts', 'ds_del_admin_page' );
} );

function ds_del_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$done = '';
	if ( isset( $_POST['ds_del_now'] ) && check_admin_referer( 'ds_del_now' ) ) {
		$now_id = (int) $_POST['ds_del_now'];
		if ( ds_del_due( $now_id ) && ds_del_allowed( $now_id ) ) {
			update_user_meta( $now_id, 'ds_del_at', time() - 1 );
			$done = ds_del_execute( $now_id ) ? 'The account was deleted.' : 'The account could not be deleted.';
		}
	}
	if ( '' !== $done ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $done ) . '</p></div>';
	}
	$pending = get_users( array( 'meta_key' => 'ds_del_at', 'meta_compare' => 'EXISTS', 'number' => 200 ) ); // phpcs:ignore
	$log     = get_option( 'ds_del_log', array() );
	$log     = is_array( $log ) ? array_reverse( $log ) : array();
	?>
	<div class="wrap">
		<h1>Deleted accounts</h1>
		<p>Users delete their own account from My Account. The account is deleted <?php echo (int) round( ds_del_delay() / HOUR_IN_SECONDS ); ?> hours after they confirm.</p>
		<h2>Waiting to be deleted (<?php echo count( $pending ); ?>)</h2>
		<table class="widefat striped" style="max-width:900px"><thead><tr><th>Name</th><th>Email</th><th>Will be deleted on</th><th></th></tr></thead><tbody>
		<?php if ( ! $pending ) : ?><tr><td colspan="4">None.</td></tr><?php endif; ?>
		<?php foreach ( $pending as $pu ) : ?>
			<tr><td><a href="<?php echo esc_url( get_edit_user_link( $pu->ID ) ); ?>"><?php echo esc_html( $pu->display_name ); ?></a></td><td><?php echo esc_html( $pu->user_email ); ?></td><td><?php echo esc_html( ds_del_when_text( ds_del_due( $pu->ID ) ) ); ?></td><td><form method="post" style="margin:0" onsubmit="return window.confirm('Delete this account now? This cannot be undone.');"><?php wp_nonce_field( 'ds_del_now' ); ?><button class="button button-small" name="ds_del_now" value="<?php echo (int) $pu->ID; ?>">Delete now</button></form></td></tr>
		<?php endforeach; ?>
		</tbody></table>
		<p class="description">To stop one, open the user and tick "Cancel the deletion and keep this account".</p>
		<h2>Deleted (<?php echo count( $log ); ?>)</h2>
		<table class="widefat striped" style="max-width:900px"><thead><tr><th>Date</th><th>Name</th><th>Email</th><th>Plan</th><th>Marketplace items kept</th></tr></thead><tbody>
		<?php if ( ! $log ) : ?><tr><td colspan="5">None yet.</td></tr><?php endif; ?>
		<?php foreach ( $log as $r ) : ?>
			<tr><td><?php echo esc_html( isset( $r['time'] ) ? $r['time'] : '' ); ?></td><td><?php echo esc_html( isset( $r['name'] ) ? $r['name'] : '' ); ?></td><td><?php echo esc_html( isset( $r['email'] ) ? $r['email'] : '' ); ?></td><td><?php echo esc_html( isset( $r['plan'] ) ? $r['plan'] : '' ); ?></td><td><?php echo (int) ( isset( $r['kept'] ) ? $r['kept'] : 0 ); ?></td></tr>
		<?php endforeach; ?>
		</tbody></table>
	</div>
	<?php
}

/* -------------------------------------------------------------- Terms page */

function ds_del_terms_text() {
	$h = (int) round( ds_del_delay() / HOUR_IN_SECONDS );
	return 'You can permanently delete your account at any time from My Account &rarr; Delete my account. To prevent misuse, the account is closed and deleted ' . $h . ' hours after you confirm; until then you can open My Account and choose "Cancel deletion - keep my account" to stop it. Confirming cancels any active subscription immediately, so it will not renew. Deletion permanently removes your saved and unsaved creations and your voice samples from our servers. Remaining credits and unused subscription time are not refunded. Items you have already published on the Marketplace remain available. We keep a basic record of the deletion (your name, email address and the deletion date) for security and billing purposes. If you sign up again with the same email address, the free sign-up credits are not granted a second time.';
}

add_filter( 'ds_terms_sections', function ( $sections ) {
	if ( ! is_array( $sections ) ) {
		return $sections;
	}
	foreach ( $sections as $sec ) {
		if ( is_array( $sec ) && isset( $sec['title'] ) && false !== stripos( (string) $sec['title'], 'deleting your account' ) ) {
			return $sections;
		}
	}
	$n    = count( $sections );
	$last = $n ? $sections[ $n - 1 ] : null;
	$new  = array( 'icon' => '', 'title' => 'Deleting your account', 'body' => ds_del_terms_text() );
	/* Keep "Contact" as the last section and keep the numbering in order. */
	if ( is_array( $last ) && isset( $last['title'] ) && preg_match( '/^(\d+)\.\s*(Contact.*)$/i', (string) $last['title'], $m ) ) {
		$new['title']                 = (int) $m[1] . '. Deleting your account';
		$sections[ $n - 1 ]['title']  = ( (int) $m[1] + 1 ) . '. ' . $m[2];
		array_splice( $sections, $n - 1, 0, array( $new ) );
		return $sections;
	}
	if ( is_array( $last ) && isset( $last['title'] ) && preg_match( '/^(\d+)\./', (string) $last['title'], $m ) ) {
		$new['title'] = ( (int) $m[1] + 1 ) . '. Deleting your account';
	}
	$sections[] = $new;
	return $sections;
}, 20 );

/* ------------------------------------------------ button + confirmation box */

add_action( 'wp_footer', function () {
	if ( ! is_user_logged_in() ) {
		return;
	}
	$uid = get_current_user_id();
	/* An administrator sees the button and the box too, but the tick box
	   cannot be ticked and the request is refused on the server as well. */
	$is_admin = ! ds_del_allowed( $uid );
	$due     = ds_del_due( $uid );
	$plan    = function_exists( 'ds_get_user_plan' ) ? (string) ds_get_user_plan( $uid ) : 'free';
	$status  = function_exists( 'ds_get_user_plan_status' ) ? (string) ds_get_user_plan_status( $uid ) : 'active';
	$paid    = in_array( $plan, array( 'premium', 'pro', 'business' ), true ) && 'expired' !== $status;
	$credits = function_exists( 'ds_get_credits' ) ? (int) ds_get_credits( $uid ) : 0;
	$hours   = (int) round( ds_del_delay() / HOUR_IN_SECONDS );
	$cfg     = array(
		'rest'    => esc_url_raw( rest_url( 'ds/v1/' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
		'pending' => $due > 0,
		'atText'  => $due ? ds_del_when_text( $due ) : '',
		'hours'   => $hours,
		'admin'   => $is_admin,
	);
	?>
<style id="ds-del-css">
.ds-del-row{margin-top:8px}
.ds-del-row .ds-del-open{display:flex;align-items:center;gap:9px;width:100%;box-sizing:border-box;padding:11px 14px;border-radius:12px;border:1.5px dashed #fecaca;background:#fff;color:#b91c1c;font:700 13px/1.2 inherit;font-family:inherit;cursor:pointer;text-align:left;transition:background .15s}
.ds-del-row .ds-del-open:hover,.ds-del-row .ds-del-open:focus-visible{background:#fff5f5}
.ds-del-row .ds-del-pend{margin-top:12px;padding:14px 16px;border-radius:14px;background:#fff7ed;border:1.5px solid #fed7aa;color:#7c2d12;font-size:12.5px;line-height:1.6}
.ds-del-row .ds-del-pend strong{color:#7c2d12}
.ds-del-row .ds-del-keep{margin-top:10px;padding:9px 14px;border-radius:9px;border:none;background:#0f172a;color:#fff;font:700 13px/1.2 inherit;font-family:inherit;cursor:pointer}
#ds-del-ov{display:none;position:fixed;inset:0;z-index:1000001;background:rgba(15,15,35,.62);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
#ds-del-ov.on{display:flex}
#ds-del-box{width:460px;max-width:100%;max-height:calc(100vh - 32px);overflow:auto;background:#fff;color:#0f0f23;border-radius:20px;box-shadow:0 30px 90px rgba(0,0,0,.35);padding:26px 26px 22px;box-sizing:border-box;text-align:left}
#ds-del-box *{box-sizing:border-box}
#ds-del-box .ds-del-ic{width:46px;height:46px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;margin:0 0 14px}
#ds-del-box .ds-del-ic.ok{background:#dcfce7;color:#16a34a}
#ds-del-box h2{margin:0 0 6px;padding:0;font-size:19px;line-height:1.3;font-weight:800;color:#0f0f23;font-family:inherit}
#ds-del-box p{margin:0 0 12px;font-size:13.5px;line-height:1.6;color:#4b5563}
#ds-del-box .ds-del-list{margin:0 0 14px;padding:14px 16px;border-radius:12px;background:#fff5f5;border:1px solid #fecaca}
#ds-del-box .ds-del-li{position:relative;margin:0 0 7px;padding:0 0 0 20px;font-size:13px;line-height:1.5;color:#7f1d1d}
#ds-del-box .ds-del-li:last-child{margin-bottom:0}
#ds-del-box .ds-del-li:before{content:"";position:absolute;left:4px;top:8px;width:6px;height:6px;border-radius:50%;background:#dc2626}
#ds-del-box .ds-del-note{margin:0 0 14px;padding:12px 14px;border-radius:12px;background:#f8fafc;border:1px solid #e2e8f0;font-size:12.5px;line-height:1.6;color:#334155}
#ds-del-box .ds-del-chk{display:flex;align-items:flex-start;gap:10px;margin:0 0 16px;font-size:13px;line-height:1.5;color:#0f0f23;font-weight:600;cursor:pointer}
#ds-del-box .ds-del-chk input{flex:0 0 18px;width:18px;height:18px;margin:1px 0 0;accent-color:#dc2626;cursor:pointer}
#ds-del-box .ds-del-btns{display:flex;gap:10px;flex-wrap:wrap}
#ds-del-box .ds-del-btns button{flex:1 1 150px;min-height:44px;padding:11px 14px;border-radius:10px;border:none;font:700 13.5px/1.2 inherit;font-family:inherit;cursor:pointer}
#ds-del-box .ds-del-no{background:#0f172a;color:#fff}
#ds-del-box .ds-del-yes{background:#dc2626;color:#fff}
#ds-del-box .ds-del-yes[disabled]{background:#f3f4f6;color:#9ca3af;cursor:not-allowed}
#ds-del-box button:focus-visible,.ds-del-row .ds-del-open:focus-visible,.ds-del-row .ds-del-keep:focus-visible{outline:2px solid #2563eb;outline-offset:2px}
.ds-del-row .ds-del-open,.ds-del-row .ds-del-keep,#ds-del-box .ds-del-btns button{text-transform:none!important;letter-spacing:0!important;font-weight:700!important;font-size:13px!important;line-height:1.2!important;box-shadow:none!important}#ds-del-box .ds-del-btns button{font-size:13.5px!important}.ds-del-row .ds-del-open{color:#b91c1c!important;background:#fff!important}.ds-del-row .ds-del-open:hover{background:#fff5f5!important}#ds-del-box .ds-del-no{background:#0f172a!important;color:#fff!important}#ds-del-box .ds-del-yes{background:#dc2626!important;color:#fff!important}#ds-del-box .ds-del-yes[disabled]{background:#f3f4f6!important;color:#9ca3af!important}#ds-del-box .ds-del-undo{background:#fff!important;color:#b91c1c!important;border:1.5px solid #fecaca!important}
#ds-del-box .ds-del-err{display:none;margin:0 0 12px;padding:10px 12px;border-radius:10px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-size:12.5px;line-height:1.5}
</style>
<div id="ds-del-ov" role="dialog" aria-modal="true" aria-labelledby="ds-del-title">
	<div id="ds-del-box">
		<div id="ds-del-ask">
			<div class="ds-del-ic" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01M10.3 3.9L2.5 17.5A2 2 0 004.2 20.5h15.6a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
			<h2 id="ds-del-title">Delete your account?</h2>
			<p>Your account will be permanently deleted <strong><?php echo (int) $hours; ?> hours</strong> after you confirm. This cannot be undone. You will lose:</p>
			<div class="ds-del-list">
				<div class="ds-del-li">Your profile and your sign-in</div>
				<div class="ds-del-li">Your remaining credits<?php echo $credits > 0 ? ' (' . esc_html( number_format( $credits ) ) . ')' : ''; ?>, with no refund</div>
				<div class="ds-del-li">Every presentation, image, video, audio and file you created or saved</div>
				<div class="ds-del-li">Your download history, drafts and referral rewards</div>
				<div class="ds-del-li">Your voice samples</div>
				<?php if ( $paid ) : ?><div class="ds-del-li">Your paid plan: the subscription is cancelled as soon as you confirm, and the time left on it is not refunded</div><?php endif; ?>
			</div>
			<p>Items you have already published on the Marketplace stay available.</p>
			<div class="ds-del-note">Until then your account keeps working. Changed your mind? Open My Account within <?php echo (int) $hours; ?> hours and choose <strong>Cancel deletion - keep my account</strong><?php echo $paid ? ' (a cancelled subscription is not switched back on)' : ''; ?>. If you sign up again later with the same email address, the free sign-up credits are not given a second time.</div>
			<div class="ds-del-err" id="ds-del-err" role="alert"></div>
			<label class="ds-del-chk"><input type="checkbox" id="ds-del-check"> <span>I understand that my account and everything in it will be permanently deleted after <?php echo (int) $hours; ?> hours.</span></label>
			<div class="ds-del-btns">
				<button type="button" class="ds-del-no" id="ds-del-no">Keep my account</button>
				<button type="button" class="ds-del-yes" id="ds-del-yes" disabled>Delete my account</button>
			</div>
		</div>
		<div id="ds-del-done" style="display:none">
			<div class="ds-del-ic ok" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
			<h2>Deletion scheduled</h2>
			<p>Your account will be permanently deleted on <strong id="ds-del-when"></strong>. We have emailed you a confirmation.</p>
			<div class="ds-del-note">Changed your mind? Choose <strong>Cancel deletion</strong> now, or open My Account any time before then and choose <strong>Cancel deletion - keep my account</strong>.</div>
			<div class="ds-del-btns"><button type="button" class="ds-del-no" id="ds-del-close">Close</button><button type="button" class="ds-del-undo" id="ds-del-undo">Cancel deletion</button></div>
		</div>
	</div>
</div>
<script id="ds-del-js" data-no-optimize="1" data-no-defer="1" data-no-minify="1" data-cfasync="false">
(function () {
  var C = <?php echo wp_json_encode( $cfg ); ?>;
  function $(id) { return document.getElementById(id); }
  function el(tag, cls, text) { var e = document.createElement(tag); if (cls) e.className = cls; if (text) e.textContent = text; return e; }
  var lastFocus = null;

  function call(path, body) {
    return fetch(C.rest + path, { method: 'POST', credentials: 'same-origin', headers: { 'X-WP-Nonce': C.nonce, 'Content-Type': 'application/json' }, body: JSON.stringify(body || {}) })
      .then(function (r) { return r.json().then(function (d) { if (!r.ok) throw new Error((d && d.message) || 'Something went wrong. Please try again.'); return d; }); });
  }

  /* The Sign Out link can be the site's /logout/ page or wp-login.php?action=logout,
     and the popup can be printed more than once: every copy gets the button. */
  function paint() {
    var outs = document.querySelectorAll('[id="ds-acct-modal"] a.ds-action-btn[href*="logout"]');
    if (!outs.length) return false;
    [].forEach.call(outs, function (out) {
      var row = out.nextElementSibling;
      if (!row || !row.classList.contains('ds-del-row')) { row = el('div', 'ds-del-row'); out.parentNode.insertBefore(row, out.nextSibling); }
      row.textContent = '';
      if (C.pending) {
        var box = el('div', 'ds-del-pend');
        box.appendChild(el('strong', '', 'Your account is scheduled for deletion.'));
        box.appendChild(document.createTextNode(' It will be permanently deleted on ' + C.atText + '.'));
        box.appendChild(document.createElement('br'));
        var keep = el('button', 'ds-del-keep', 'Cancel deletion - keep my account'); keep.type = 'button';
        keep.addEventListener('click', function () {
          keep.disabled = true; keep.textContent = 'Saving...';
          call('account-delete-cancel').then(function () { C.pending = false; C.atText = ''; paint(); })
            .catch(function (e) { keep.disabled = false; keep.textContent = 'Cancel deletion - keep my account'; box.appendChild(el('div', '', e.message)); });
        });
        box.appendChild(keep);
        row.appendChild(box);
      } else {
        var b = el('button', 'ds-del-open'); b.type = 'button';
        b.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V4h6v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        b.appendChild(el('span', '', 'Delete my account'));
        b.addEventListener('click', open);
        row.appendChild(b);
      }
    });
    return true;
  }

  function open() {
    lastFocus = document.activeElement;
    $('ds-del-ask').style.display = ''; $('ds-del-done').style.display = 'none';
    $('ds-del-check').checked = false; $('ds-del-yes').disabled = true; $('ds-del-yes').textContent = 'Delete my account';
    $('ds-del-err').style.display = 'none';
    $('ds-del-ov').classList.add('on');
    $('ds-del-no').focus();
  }
  function close() {
    $('ds-del-ov').classList.remove('on');
    if (lastFocus && lastFocus.focus && document.contains(lastFocus)) lastFocus.focus();
  }

  function init() {
    var ov = $('ds-del-ov'); if (!ov) return;
    $('ds-del-check').addEventListener('change', function () {
      if (C.admin) {
        this.checked = false; $('ds-del-yes').disabled = true;
        var er = $('ds-del-err');
        er.textContent = 'You are an administrator. Administrator accounts cannot be deleted here.';
        er.style.display = 'block';
        return;
      }
      $('ds-del-yes').disabled = !this.checked;
    });
    $('ds-del-undo').addEventListener('click', function () {
      var b = this; b.disabled = true; b.textContent = 'Saving...';
      call('account-delete-cancel').then(function () { C.pending = false; C.atText = ''; paint(); b.disabled = false; b.textContent = 'Cancel deletion'; close(); })
        .catch(function () { b.disabled = false; b.textContent = 'Cancel deletion'; });
    });
    $('ds-del-no').addEventListener('click', close);
    $('ds-del-close').addEventListener('click', close);
    ov.addEventListener('click', function (e) { if (e.target === ov) close(); });
    document.addEventListener('keydown', function (e) {
      if (!ov.classList.contains('on')) return;
      if (e.key === 'Escape') { e.stopPropagation(); close(); return; }
      if (e.key === 'Tab') {
        var f = [].filter.call(ov.querySelectorAll('button,input'), function (x) { return !x.disabled && x.offsetParent !== null; });
        if (!f.length) return;
        if (e.shiftKey && document.activeElement === f[0]) { e.preventDefault(); f[f.length - 1].focus(); }
        else if (!e.shiftKey && document.activeElement === f[f.length - 1]) { e.preventDefault(); f[0].focus(); }
      }
    }, true);
    $('ds-del-yes').addEventListener('click', function () {
      var btn = this, err = $('ds-del-err');
      if (!$('ds-del-check').checked) return;
      btn.disabled = true; btn.textContent = 'Please wait...'; err.style.display = 'none';
      call('account-delete', { confirm: true }).then(function (d) {
        C.pending = true; C.atText = d.at_text || '';
        $('ds-del-when').textContent = C.atText;
        $('ds-del-ask').style.display = 'none'; $('ds-del-done').style.display = '';
        $('ds-del-close').focus();
        paint();
      }).catch(function (e) {
        btn.disabled = false; btn.textContent = 'Delete my account';
        err.textContent = e.message; err.style.display = 'block';
      });
    });
    /* The My Account popup is printed by another snippet; wait for it if needed. */
    if (!paint()) { var n = 0, t = setInterval(function () { if (paint() || ++n > 40) clearInterval(t); }, 250); }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>
	<?php
}, 40 );

/* A short note on the home page after the account has gone. */
add_action( 'wp_footer', function () {
	if ( is_user_logged_in() || empty( $_GET['ds_account'] ) || 'deleted' !== $_GET['ds_account'] ) { // phpcs:ignore
		return;
	}
	?>
<div role="status" style="position:fixed;left:50%;bottom:24px;transform:translateX(-50%);z-index:999999;max-width:calc(100vw - 32px);padding:12px 18px;border-radius:12px;background:#0f172a;color:#fff;font:600 14px/1.5 -apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;box-shadow:0 10px 40px rgba(0,0,0,.35)">Your account has been deleted.</div>
	<?php
}, 40 );
