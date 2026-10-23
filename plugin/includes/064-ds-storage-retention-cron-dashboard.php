<?php
/**
 * Code Snippets export — DS Storage Retention Cron & Dashboard
 *
 * Snippet ID : 750
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-064
 * Modified   : 2026-09-09 02:43:25
 * Load order : 064 of 144
 */
/**
 * Snippet Name: DS Storage Retention Cron & Dashboard
 * Run: Everywhere
 *
 * Phase 3 of the Designstager Storage & Retention system. This is a brand-new,
 * NET-ADDITIVE snippet -- it does NOT modify "DS Storage & API Settings (R2 +
 * Retention)" or any other existing snippet. It only CALLS the shared helpers
 * that snippet already exposes (ds_hub_get_generated_file(),
 * ds_hub_delete_generated_file_now(), ds_hub_get_retention_days(), the
 * ds_hub_notify_settings / ds_hub_retention_policy options, ds_hub_log(), etc.)
 * and reads the existing {$wpdb->prefix}ds_generated_files table. Requires the
 * "DS Storage & API Settings (R2 + Retention)" snippet to be active.
 *
 * What this adds:
 *   1. A daily WP-Cron job ('ds_hub_retention_cron') that:
 *        a. Sends a one-time "expiring soon" email (only if enabled in
 *           Storage Settings) to the owner of any TEMPORARY, GENERATED
 *           (never marketplace) file whose expiry falls within the
 *           configured "days before" window. Never sent twice for the
 *           same file.
 *        b. Permanently deletes (R2 object + DB row status) any TEMPORARY,
 *           GENERATED file whose expiry has passed. Saved creations and
 *           MARKETPLACE-SOURCED files are NEVER touched by this job --
 *           marketplace content must never auto-expire, full stop.
 *   2. WordPress Admin -> Designstager -> Storage Dashboard: live stats
 *      (counts + storage used, by status and by type), files expiring
 *      soon, last/next cron run, and a "needs attention" list of files
 *      whose R2 delete keeps failing -- with a manual per-file retry
 *      button and a manual "Run Cleanup Now" button.
 */
if ( ! defined('ABSPATH') ) exit;

/* =====================================================================
 * 1. CRON SCHEDULING
 * ===================================================================== */

if ( ! function_exists('ds_hub_retention_cron_hook') ) {
	function ds_hub_retention_cron_hook() {
		if ( ! wp_next_scheduled( 'ds_hub_retention_cron' ) ) {
			// Stagger a few minutes off the hour so it doesn't pile up with other cron jobs.
			wp_schedule_event( time() + 300, 'daily', 'ds_hub_retention_cron' );
		}
	}
	add_action( 'init', 'ds_hub_retention_cron_hook' );
}

if ( ! has_action( 'ds_hub_retention_cron', 'ds_hub_run_retention_cron' ) ) {
	add_action( 'ds_hub_retention_cron', 'ds_hub_run_retention_cron' );
}

/* =====================================================================
 * 2. CRON WORKER
 * ===================================================================== */

if ( ! function_exists('ds_hub_run_retention_cron') ) {
	function ds_hub_run_retention_cron() {
		if ( ! function_exists('ds_hub_delete_generated_file_now') ) {
			// The R2/Retention hub library isn't active -- nothing we can safely do.
			return array( 'notified' => 0, 'deleted' => 0, 'delete_failed' => 0, 'skipped' => true );
		}

		$notified = ds_hub_retention_send_notifications();
		$del      = ds_hub_retention_delete_expired();

		$result = array(
			'notified'      => $notified,
			'deleted'       => $del['deleted'],
			'delete_failed' => $del['failed'],
			'skipped'       => false,
		);

		update_option( 'ds_hub_retention_cron_last_run', array(
			'time'   => current_time( 'mysql', true ),
			'result' => $result,
		) );

		if ( function_exists('ds_hub_log') ) {
			ds_hub_log( 'retention_cron_run', $result );
		}

		return $result;
	}
}

/** Send one-time "expiring soon" emails. Returns the count sent. */
if ( ! function_exists('ds_hub_retention_send_notifications') ) {
	function ds_hub_retention_send_notifications() {
		global $wpdb;
		$notify = get_option( 'ds_hub_notify_settings', array( 'enabled' => false, 'days_before' => 7 ) );
		if ( empty( $notify['enabled'] ) ) return 0;

		$days_before = max( 1, (int) $notify['days_before'] );
		$table       = $wpdb->prefix . 'ds_generated_files';
		$now         = gmdate( 'Y-m-d H:i:s' );
		$window_end  = gmdate( 'Y-m-d H:i:s', time() + ( $days_before * DAY_IN_SECONDS ) );

		// source != 'marketplace' is a belt-and-braces guard -- marketplace content
		// must never be touched by this job, even though 'generated' is the only
		// other value in practice today.
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$table} WHERE status = 'temporary' AND source != 'marketplace'
			 AND expires_at IS NOT NULL AND expires_at > %s AND expires_at <= %s
			 ORDER BY expires_at ASC LIMIT 300",
			$now, $window_end
		) );

		$sent = 0;
		foreach ( (array) $rows as $row ) {
			$meta = json_decode( (string) $row->meta, true );
			if ( ! is_array( $meta ) ) $meta = array();
			if ( ! empty( $meta['notify_sent_at'] ) ) continue; // never send twice for the same file

			$user = get_userdata( (int) $row->user_id );
			if ( ! $user || ! is_email( $user->user_email ) ) continue;

			$expires_ts = strtotime( $row->expires_at . ' UTC' );
			$date_str   = $expires_ts ? date_i18n( 'F j, Y', $expires_ts ) : 'soon';
			$type_label = $row->file_type ? ucfirst( (string) $row->file_type ) : 'file';
			$creations_url = home_url( '/my-creations/' );

			$subject = 'Your Designstager ' . strtolower( $type_label ) . ' expires on ' . $date_str;
			$body = "Hi " . $user->display_name . ",\n\n"
				. "One of your AI-generated files on Designstager (a " . strtolower( $type_label ) . ") is scheduled "
				. "to be permanently deleted from storage on {$date_str}.\n\n"
				. "If you'd like to keep it, download it or use \"Save to My Creations\" before then:\n"
				. $creations_url . "\n\n"
				. "If you do nothing, the file will be automatically and permanently removed after that date. "
				. "This does not affect anything you've submitted to the Marketplace -- marketplace content is "
				. "never auto-deleted.\n\n"
				. "-- Designstager";

			$ok = wp_mail( $user->user_email, $subject, $body );
			if ( $ok ) {
				$meta['notify_sent_at'] = current_time( 'mysql', true );
				$wpdb->update( $table, array( 'meta' => wp_json_encode( $meta ) ), array( 'id' => (int) $row->id ) );
				$sent++;
				if ( function_exists('ds_hub_log') ) {
					ds_hub_log( 'expiry_notice_sent', array( 'to' => $user->user_email, 'expires_at' => $row->expires_at ), (int) $row->id );
				}
			}
		}
		return $sent;
	}
}

/** Delete every temporary, generated file whose expiry has passed. Returns array('deleted'=>n,'failed'=>n). */
if ( ! function_exists('ds_hub_retention_delete_expired') ) {
	function ds_hub_retention_delete_expired() {
		global $wpdb;
		$table = $wpdb->prefix . 'ds_generated_files';
		$now   = gmdate( 'Y-m-d H:i:s' );

		// Same belt-and-braces marketplace exclusion as the notifier. Rows that have
		// already failed 5+ times in a row are skipped here -- they need a human to
		// look at the R2 config/permissions rather than retrying forever -- but they
		// still surface in the Storage Dashboard's "needs attention" list below.
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT id FROM {$table} WHERE status = 'temporary' AND source != 'marketplace'
			 AND expires_at IS NOT NULL AND expires_at <= %s AND delete_attempts < 5
			 ORDER BY expires_at ASC LIMIT 200",
			$now
		) );

		$deleted = 0; $failed = 0;
		foreach ( (array) $rows as $row ) {
			$result = ds_hub_delete_generated_file_now( (int) $row->id );
			if ( is_wp_error( $result ) ) { $failed++; } else { $deleted++; }
		}
		return array( 'deleted' => $deleted, 'failed' => $failed );
	}
}

/* =====================================================================
 * 3. ADMIN: STORAGE DASHBOARD
 * ===================================================================== */

add_action( 'admin_menu', function() {
	if ( ! function_exists('ds_hub_render_storage_settings_page') ) return; // hub library not active
	add_submenu_page( 'designstager-hub', 'Storage Dashboard', 'Storage Dashboard', 'manage_options', 'designstager-storage-dashboard', 'ds_hub_render_storage_dashboard_page' );
}, 20 );

if ( ! function_exists('ds_hub_dash_fmt_bytes') ) {
	function ds_hub_dash_fmt_bytes( $b ) {
		$b = (float) $b;
		if ( $b >= 1073741824 ) return round( $b / 1073741824, 2 ) . ' GB';
		if ( $b >= 1048576 )    return round( $b / 1048576, 2 ) . ' MB';
		if ( $b >= 1024 )       return round( $b / 1024, 2 ) . ' KB';
		return (int) $b . ' B';
	}
}

if ( ! function_exists('ds_hub_dash_tabs_nav') ) {
	function ds_hub_dash_tabs_nav( $active ) {
		$tabs = array(
			'designstager-hub'                => 'R2 Storage',
			'designstager-ai-keys'            => 'AI API Settings',
			'designstager-storage-settings'   => 'Storage Settings',
			'designstager-storage-dashboard'  => 'Storage Dashboard',
		);
		echo '<h2 class="ds-hub-title" style="display:flex;align-items:center;gap:8px;"><span class="dashicons dashicons-cloud"></span> Designstager — Storage &amp; API Settings</h2>';
		echo '<div class="ds-hub-tabs">';
		foreach ( $tabs as $slug => $label ) {
			$class = ( $slug === $active ) ? 'active' : '';
			printf( '<a class="%s" href="%s">%s</a>', esc_attr( $class ), esc_url( admin_url( 'admin.php?page=' . $slug ) ), esc_html( $label ) );
		}
		echo '</div>';
	}
}

if ( ! function_exists('ds_hub_render_storage_dashboard_page') ) {
	function ds_hub_render_storage_dashboard_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		global $wpdb;
		$table = $wpdb->prefix . 'ds_generated_files';

		if ( isset( $_POST['ds_hub_run_cleanup_now'] ) && check_admin_referer( 'ds_hub_run_cleanup_now' ) ) {
			$r = ds_hub_run_retention_cron();
			if ( ! empty( $r['skipped'] ) ) {
				echo '<div class="notice notice-error"><p>Cleanup did not run: the Storage &amp; API Settings snippet is not active.</p></div>';
			} else {
				echo '<div class="notice notice-success is-dismissible"><p><strong>Cleanup run complete:</strong> ' . (int) $r['deleted'] . ' file(s) deleted, ' . (int) $r['notified'] . ' notice(s) sent, ' . (int) $r['delete_failed'] . ' failure(s).</p></div>';
			}
		}
		if ( isset( $_POST['ds_hub_retry_delete_id'] ) && check_admin_referer( 'ds_hub_retry_delete' ) ) {
			$rid = (int) $_POST['ds_hub_retry_delete_id'];
			$r   = function_exists('ds_hub_delete_generated_file_now') ? ds_hub_delete_generated_file_now( $rid ) : new WP_Error( 'ds_hub_missing', 'Storage &amp; API Settings snippet is not active.' );
			if ( is_wp_error( $r ) ) {
				echo '<div class="notice notice-error"><p>Retry failed for file #' . $rid . ': ' . esc_html( $r->get_error_message() ) . '</p></div>';
			} else {
				echo '<div class="notice notice-success is-dismissible"><p>File #' . $rid . ' deleted successfully.</p></div>';
			}
		}

		$by_status = $wpdb->get_results( "SELECT status, COUNT(*) as cnt, SUM(file_size) as bytes FROM {$table} GROUP BY status", ARRAY_A );
		$by_type   = $wpdb->get_results( "SELECT file_type, COUNT(*) as cnt, SUM(file_size) as bytes FROM {$table} WHERE status != 'deleted' GROUP BY file_type", ARRAY_A );
		$marketplace_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE source = 'marketplace'" );
		$marketplace_bytes = (float) $wpdb->get_var( "SELECT COALESCE(SUM(file_size),0) FROM {$table} WHERE source = 'marketplace'" );
		$expiring_soon = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE status = 'temporary' AND source != 'marketplace' AND expires_at IS NOT NULL AND expires_at BETWEEN %s AND %s",
			gmdate( 'Y-m-d H:i:s' ), gmdate( 'Y-m-d H:i:s', time() + 7 * DAY_IN_SECONDS )
		) );
		$needs_attention = $wpdb->get_results( "SELECT * FROM {$table} WHERE delete_attempts > 0 AND status != 'deleted' ORDER BY delete_attempts DESC LIMIT 50" );

		$last_run  = get_option( 'ds_hub_retention_cron_last_run' );
		$next_run  = wp_next_scheduled( 'ds_hub_retention_cron' );
		$retention_days = function_exists('ds_hub_get_retention_days') ? ds_hub_get_retention_days() : null;
		$saved_days     = function_exists('ds_hub_get_saved_retention_days') ? ds_hub_get_saved_retention_days() : null;
		$notify         = get_option( 'ds_hub_notify_settings', array( 'enabled' => false, 'days_before' => 7 ) );

		if ( function_exists('ds_hub_admin_css') ) ds_hub_admin_css();
		?>
		<div class="wrap ds-hub-wrap">
			<?php ds_hub_dash_tabs_nav( 'designstager-storage-dashboard' ); ?>

			<div class="ds-hub-card">
				<h2>Current Policy</h2>
				<p style="color:#6b7280;">
					Generated files auto-delete after
					<strong><?php echo $retention_days !== null ? (int) $retention_days . ' days' : 'unknown'; ?></strong>.
					Saved creations:
					<strong><?php echo $saved_days === null ? 'kept permanently' : (int) $saved_days . ' days'; ?></strong>.
					Expiry email:
					<strong><?php echo ! empty( $notify['enabled'] ) ? ( (int) $notify['days_before'] . ' day(s) before deletion' ) : 'disabled'; ?></strong>.
					Marketplace content: <strong>never expires</strong>, regardless of the settings above.
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=designstager-storage-settings' ) ); ?>">Change these settings &rarr;</a>
				</p>
				<p style="color:#6b7280;">
					Cron: last run
					<strong><?php echo $last_run && ! empty( $last_run['time'] ) ? esc_html( $last_run['time'] ) . ' UTC' : 'never'; ?></strong><?php if ( $last_run && ! empty( $last_run['result'] ) ) : $lr = $last_run['result']; ?>
						(deleted <?php echo (int) ( $lr['deleted'] ?? 0 ); ?>, notified <?php echo (int) ( $lr['notified'] ?? 0 ); ?>, failed <?php echo (int) ( $lr['delete_failed'] ?? 0 ); ?>)
					<?php endif; ?>
					&nbsp;|&nbsp; next run
					<strong><?php echo $next_run ? esc_html( date_i18n( 'M j, Y g:i a', $next_run ) ) : 'not scheduled'; ?></strong>
				</p>
				<form method="post" style="margin-top:10px;">
					<?php wp_nonce_field( 'ds_hub_run_cleanup_now' ); ?>
					<button type="submit" name="ds_hub_run_cleanup_now" value="1" class="button button-secondary" onclick="return confirm('Run the retention cleanup job right now? This will permanently delete any expired generated files.');">Run Cleanup Now</button>
				</form>
			</div>

			<div class="ds-hub-card">
				<h2>Storage by Status</h2>
				<table class="widefat striped" style="max-width:640px;">
					<thead><tr><th>Status</th><th>Files</th><th>Storage used</th></tr></thead>
					<tbody>
					<?php if ( empty( $by_status ) ) : ?>
						<tr><td colspan="3">No generated files yet.</td></tr>
					<?php else : foreach ( $by_status as $row ) : ?>
						<tr>
							<td><?php echo esc_html( ucfirst( $row['status'] ) ); ?></td>
							<td><?php echo (int) $row['cnt']; ?></td>
							<td><?php echo esc_html( ds_hub_dash_fmt_bytes( $row['bytes'] ) ); ?></td>
						</tr>
					<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div>

			<div class="ds-hub-card">
				<h2>Storage by Type <span style="color:#6b7280;font-weight:400;font-size:13px;">(excludes deleted files)</span></h2>
				<table class="widefat striped" style="max-width:640px;">
					<thead><tr><th>Type</th><th>Files</th><th>Storage used</th></tr></thead>
					<tbody>
					<?php if ( empty( $by_type ) ) : ?>
						<tr><td colspan="3">No generated files yet.</td></tr>
					<?php else : foreach ( $by_type as $row ) : ?>
						<tr>
							<td><?php echo esc_html( ucfirst( $row['file_type'] ) ); ?></td>
							<td><?php echo (int) $row['cnt']; ?></td>
							<td><?php echo esc_html( ds_hub_dash_fmt_bytes( $row['bytes'] ) ); ?></td>
						</tr>
					<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div>

			<div class="ds-hub-card">
				<h2>Marketplace Content <span class="ds-hub-badge on">Never Expires</span></h2>
				<p style="color:#6b7280;"><?php echo (int) $marketplace_count; ?> file(s) stored via the retention hub with source = "marketplace" (<?php echo esc_html( ds_hub_dash_fmt_bytes( $marketplace_bytes ) ); ?>). These are permanently excluded from both the notification and deletion passes above, no matter what the retention policy says.</p>
			</div>

			<div class="ds-hub-card">
				<h2>Expiring in the Next 7 Days</h2>
				<p style="color:#6b7280;"><?php echo (int) $expiring_soon; ?> generated file(s) will be auto-deleted within 7 days unless downloaded or saved by their owner.</p>
			</div>

			<div class="ds-hub-card">
				<h2>Needs Attention <span style="color:#6b7280;font-weight:400;font-size:13px;">(delete failures)</span></h2>
				<?php if ( empty( $needs_attention ) ) : ?>
					<p style="color:#6b7280;">Nothing needs attention -- no files have failed to delete.</p>
				<?php else : ?>
					<table class="widefat striped">
						<thead><tr><th>ID</th><th>User</th><th>Type</th><th>Attempts</th><th>Last error</th><th>Last attempt</th><th></th></tr></thead>
						<tbody>
						<?php foreach ( $needs_attention as $row ) : $u = get_userdata( (int) $row->user_id ); ?>
							<tr>
								<td><?php echo (int) $row->id; ?></td>
								<td><?php echo $u ? esc_html( $u->user_email ) : '#' . (int) $row->user_id; ?></td>
								<td><?php echo esc_html( $row->file_type ); ?></td>
								<td><?php echo (int) $row->delete_attempts; ?></td>
								<td style="max-width:280px;"><?php echo esc_html( $row->delete_error ); ?></td>
								<td><?php echo esc_html( $row->last_delete_attempt_at ); ?></td>
								<td>
									<form method="post" style="margin:0;">
										<?php wp_nonce_field( 'ds_hub_retry_delete' ); ?>
										<input type="hidden" name="ds_hub_retry_delete_id" value="<?php echo (int) $row->id; ?>">
										<button type="submit" class="button button-small">Retry Delete</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
