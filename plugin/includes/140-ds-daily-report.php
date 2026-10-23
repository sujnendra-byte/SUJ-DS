<?php
/**
 * Code Snippets export — DS Daily Report
 *
 * Snippet ID : 816
 * Active     : yes
 * Priority   : 1001
 * Scope      : global
 * Tags       : designstager, ds-order-130
 * Modified   : 2026-09-08 12:29:30
 * Load order : 140 of 144
 */
/**
 * DS Daily Report - standalone snippet build.
 *
 * In the plugin this was two files: includes/modules/mod-130-9002-ds-daily-report.php
 * (a thin bootstrap) and includes/lib/ds-daily-report-core.php (the class).
 * A snippet cannot require() a sibling file, so the core class is inlined below,
 * unmodified. The plugin's fallback admin screen is dropped - it only ever showed
 * when the core file was missing, which cannot happen here.
 *
 * Note: the standalone plugin also registered a deactivation hook to clear its cron
 * events. Deactivating this snippet will NOT clear them; use Tools -> DS Daily Report.
 */

defined( 'DS_DAILY_REPORT_FILE' ) || define( 'DS_DAILY_REPORT_FILE', __FILE__ );

/* ---------- includes/lib/ds-daily-report-core.php (inlined) ---------- */

/**
 * Module: DS Daily Report
 * ID: 678 | Order: 131 | Scope: global
 *
 * Produces a per-day Excel (.xlsx) summary of DesignStager activity and
 * emails it automatically once per day.
 *
 * Reporting window: 20:00 (site time) on day D-1  ->  20:00 on day D.
 * The report for that window is generated and mailed at 20:00 on day D.
 *
 * Design notes
 * ------------
 * 1. This module NEVER modifies existing plugin logic. It only reads.
 * 2. Because some source data is pruned (ds_generated_files has a retention
 *    cron) or lives in serialized user meta, the module mirrors every counted
 *    event into its own append-only table {prefix}ds_daily_events. Once an
 *    event is mirrored it can always be re-reported, even after the original
 *    row is deleted. Mirroring is watermark-based and idempotent.
 * 3. The .xlsx is written with a self-contained OOXML writer (ZipArchive only)
 *    so there is no PhpSpreadsheet / Composer dependency.
 *
 * @package DesignStager
 */


if ( class_exists( 'DS_Daily_Report' ) ) {
	// Another copy already declared the class. Still make sure the singleton
	// exists, otherwise its hooks - including the Tools -> DS Daily Report
	// screen - are never registered and the report has no manual controls.
	DS_Daily_Report::instance();
	return;
}

final class DS_Daily_Report {

	const VERSION       = '1.0.0';
	const TABLE         = 'ds_daily_events';
	const CRON_HOOK     = 'ds_daily_report_run';
	const SWEEP_HOOK    = 'ds_daily_report_sweep';
	const OPT_DBV       = 'ds_daily_report_db_version';
	const OPT_MARKS     = 'ds_daily_report_watermarks';
	const OPT_SETTINGS  = 'ds_daily_report_settings';
	const DB_VERSION    = 3;

	/** Hour of day (site timezone) at which the window closes and the mail goes out. */
	const BOUNDARY_HOUR = 20;

	/** @var DS_Daily_Report|null */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'maybe_install' ) );

		// Live recorders (cheap, best-effort - the sweepers are the safety net).
		add_action( 'user_register', array( $this, 'on_user_register' ), 10, 1 );
		add_action( 'dlm_downloading', array( $this, 'on_dlm_download' ), 10, 3 );

		// Scheduled work.
		add_action( self::CRON_HOOK, array( $this, 'run_daily' ) );
		add_action( self::SWEEP_HOOK, array( $this, 'sweep_all' ) );
		add_action( 'init', array( $this, 'maybe_schedule' ), 20 );

		// Admin UI.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_post_ds_daily_report_action', array( $this, 'handle_admin_post' ) );
	}

	/* --------------------------------------------------------------------- *
	 * Settings
	 * --------------------------------------------------------------------- */

	public function settings() {
		$defaults = array(
			'recipients' => get_option( 'admin_email' ),
			'enabled'    => 1,
			'keep_files' => 90,
		);
		$saved = get_option( self::OPT_SETTINGS, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return wp_parse_args( $saved, $defaults );
	}

	private function table() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE;
	}

	/* --------------------------------------------------------------------- *
	 * Install
	 * --------------------------------------------------------------------- */

	public function maybe_install() {
		if ( (int) get_option( self::OPT_DBV ) === self::DB_VERSION ) {
			return;
		}
		$this->install();
	}

	public function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = $this->table();
		$collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			event_type VARCHAR(40) NOT NULL DEFAULT '',
			sub_type VARCHAR(60) NOT NULL DEFAULT '',
			user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			object_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			qty INT NOT NULL DEFAULT 1,
			credits DECIMAL(14,2) NOT NULL DEFAULT 0,
			amount DECIMAL(14,2) NOT NULL DEFAULT 0,
			plan VARCHAR(40) NOT NULL DEFAULT '',
			dedupe_key VARCHAR(80) NOT NULL DEFAULT '',
			meta TEXT NULL,
			occurred_at DATETIME NOT NULL,
			recorded_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY dedupe_key (dedupe_key),
			KEY occurred_at (occurred_at),
			KEY event_type_time (event_type, occurred_at)
		) {$collate};";

		dbDelta( $sql );
		update_option( self::OPT_DBV, self::DB_VERSION, false );
	}

	public function maybe_schedule() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( $this->next_boundary_ts(), 'daily', self::CRON_HOOK );
		}
		if ( ! wp_next_scheduled( self::SWEEP_HOOK ) ) {
			wp_schedule_event( time() + 300, 'hourly', self::SWEEP_HOOK );
		}
	}

	public static function deactivate() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
		wp_clear_scheduled_hook( self::SWEEP_HOOK );
	}

	/* --------------------------------------------------------------------- *
	 * Time helpers - all windows are computed in the SITE timezone
	 * --------------------------------------------------------------------- */

	private function tz() {
		return wp_timezone();
	}

	/** UNIX timestamp of the next BOUNDARY_HOUR:00 in site time. */
	private function next_boundary_ts() {
		$now = new DateTime( 'now', $this->tz() );
		$b   = clone $now;
		$b->setTime( self::BOUNDARY_HOUR, 0, 0 );
		if ( $b <= $now ) {
			$b->modify( '+1 day' );
		}
		return $b->getTimestamp();
	}

	/**
	 * Window for the report identified by $date (Y-m-d, the day it is mailed).
	 * Returns [ start DateTime, end DateTime ] = D-1 20:00 .. D 20:00 site time.
	 */
	public function window_for( $date ) {
		$end = DateTime::createFromFormat( 'Y-m-d H:i:s', $date . ' ' . sprintf( '%02d:00:00', self::BOUNDARY_HOUR ), $this->tz() );
		if ( ! $end ) {
			$end = new DateTime( 'now', $this->tz() );
			$end->setTime( self::BOUNDARY_HOUR, 0, 0 );
		}
		$start = clone $end;
		$start->modify( '-1 day' );
		return array( $start, $end );
	}

	/** Convert a site-time DateTime to the 'Y-m-d H:i:s' string stored in wp_posts etc. */
	private function local_sql( DateTime $dt ) {
		return $dt->format( 'Y-m-d H:i:s' );
	}

	/** Convert a site-time DateTime to a UTC 'Y-m-d H:i:s' string. */
	private function utc_sql( DateTime $dt ) {
		$u = clone $dt;
		$u->setTimezone( new DateTimeZone( 'UTC' ) );
		return $u->format( 'Y-m-d H:i:s' );
	}

	/* --------------------------------------------------------------------- *
	 * Event log helpers
	 * --------------------------------------------------------------------- */

	/**
	 * Insert one event. Silently ignores duplicates via the unique dedupe_key,
	 * which makes every sweeper safe to re-run.
	 */
	public function log_event( $args ) {
		global $wpdb;

		$a = wp_parse_args(
			$args,
			array(
				'event_type'  => '',
				'sub_type'    => '',
				'user_id'     => 0,
				'object_id'   => 0,
				'qty'         => 1,
				'credits'     => 0,
				'amount'      => 0,
				'plan'        => '',
				'dedupe_key'  => '',
				'meta'        => '',
				'occurred_at' => null,
			)
		);

		if ( empty( $a['event_type'] ) ) {
			return false;
		}

		if ( empty( $a['occurred_at'] ) ) {
			$a['occurred_at'] = ( new DateTime( 'now', $this->tz() ) )->format( 'Y-m-d H:i:s' );
		}

		if ( empty( $a['dedupe_key'] ) ) {
			$a['dedupe_key'] = substr(
				$a['event_type'] . ':' . $a['sub_type'] . ':' . $a['user_id'] . ':' . $a['object_id'] . ':' . $a['occurred_at'],
				0,
				80
			);
		}

		if ( is_array( $a['meta'] ) ) {
			$a['meta'] = wp_json_encode( $a['meta'] );
		}

		$ok = $wpdb->query(
			$wpdb->prepare(
				'INSERT IGNORE INTO `' . $this->table() . '`
				(event_type, sub_type, user_id, object_id, qty, credits, amount, plan, dedupe_key, meta, occurred_at, recorded_at)
				VALUES (%s,%s,%d,%d,%d,%f,%f,%s,%s,%s,%s,%s)',
				$a['event_type'],
				$a['sub_type'],
				(int) $a['user_id'],
				(int) $a['object_id'],
				(int) $a['qty'],
				(float) $a['credits'],
				(float) $a['amount'],
				$a['plan'],
				$a['dedupe_key'],
				(string) $a['meta'],
				$a['occurred_at'],
				( new DateTime( 'now', $this->tz() ) )->format( 'Y-m-d H:i:s' )
			)
		);

		return false !== $ok;
	}

	private function get_marks() {
		$m = get_option( self::OPT_MARKS, array() );
		return is_array( $m ) ? $m : array();
	}

	private function set_mark( $key, $value ) {
		$m         = $this->get_marks();
		$m[ $key ] = $value;
		update_option( self::OPT_MARKS, $m, false );
	}

	private function get_mark( $key, $default = 0 ) {
		$m = $this->get_marks();
		return isset( $m[ $key ] ) ? $m[ $key ] : $default;
	}

	/** True if a table exists. Results are cached per request. */
	private function table_exists( $name ) {
		global $wpdb;
		static $cache = array();
		if ( isset( $cache[ $name ] ) ) {
			return $cache[ $name ];
		}
		$found          = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $name ) );
		$cache[ $name ] = ( $found === $name );
		return $cache[ $name ];
	}

	/** Find the Download Monitor log table across DLM versions. */
	private function dlm_log_table() {
		global $wpdb;
		foreach ( array( 'dlm_reports_log', 'dlm_download_log' ) as $t ) {
			if ( $this->table_exists( $wpdb->prefix . $t ) ) {
				return $wpdb->prefix . $t;
			}
		}
		return '';
	}

	/** Current plan slug for a user, normalised. */
	private function user_plan( $user_id ) {
		$plan = get_user_meta( $user_id, 'ds_subscription_plan', true );
		if ( empty( $plan ) ) {
			$plan = get_user_meta( $user_id, 'ds_plan', true );
		}
		$plan = strtolower( trim( (string) $plan ) );
		return $plan ? $plan : 'free';
	}

	/* --------------------------------------------------------------------- *
	 * Live recorders
	 * --------------------------------------------------------------------- */

	public function on_user_register( $user_id ) {
		$u = get_userdata( $user_id );
		$this->log_event(
			array(
				'event_type'  => 'signup',
				'sub_type'    => 'registered',
				'user_id'     => $user_id,
				'object_id'   => $user_id,
				'dedupe_key'  => 'signup:' . $user_id,
				'occurred_at' => $u ? get_date_from_gmt( $u->user_registered ) : null,
			)
		);
	}

	/**
	 * Download Monitor fires dlm_downloading for every served download.
	 * Free vs paid is resolved later by the earnings sweeper; here we only
	 * capture the fact and the identifiers.
	 */
	public function on_dlm_download( $download, $version, $file_path ) {
		if ( ! is_object( $download ) || ! method_exists( $download, 'get_id' ) ) {
			return;
		}
		$id = (int) $download->get_id();
		$this->log_event(
			array(
				'event_type' => 'download',
				'sub_type'   => 'served',
				'user_id'    => get_current_user_id(),
				'object_id'  => $id,
				'plan'       => get_current_user_id() ? $this->user_plan( get_current_user_id() ) : '',
				'dedupe_key' => 'dl:' . $id . ':' . get_current_user_id() . ':' . microtime( true ),
			)
		);
	}

	/* --------------------------------------------------------------------- *
	 * Sweepers - mirror source rows into the event log (idempotent)
	 * --------------------------------------------------------------------- */

	public function sweep_all() {
		$this->sweep_generations();
		$this->sweep_credits();
		$this->sweep_plans();
		$this->sweep_downloads();
		$this->sweep_earnings();
	}

	/** ds_generated_files -> generation events (image/video/presentation/audio). */
	public function sweep_generations() {
		global $wpdb;

		$src = $wpdb->prefix . 'ds_generated_files';
		if ( ! $this->table_exists( $src ) ) {
			return;
		}

		$last = (int) $this->get_mark( 'generated_files_id', 0 );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, user_id, file_type, source, created_at
				 FROM `{$src}`
				 WHERE id > %d
				 ORDER BY id ASC
				 LIMIT 2000",
				$last
			),
			ARRAY_A
		);

		if ( empty( $rows ) ) {
			return;
		}

		foreach ( $rows as $r ) {
			$type = strtolower( trim( (string) $r['file_type'] ) );
			if ( '' === $type ) {
				$type = 'other';
			}
			$this->log_event(
				array(
					'event_type'  => 'generation',
					'sub_type'    => $type,
					'user_id'     => (int) $r['user_id'],
					'object_id'   => (int) $r['id'],
					'plan'        => $this->user_plan( (int) $r['user_id'] ),
					'meta'        => array( 'source' => (string) $r['source'] ),
					'dedupe_key'  => 'gen:' . (int) $r['id'],
					'occurred_at' => $r['created_at'],
				)
			);
			$last = max( $last, (int) $r['id'] );
		}

		$this->set_mark( 'generated_files_id', $last );
	}

	/**
	 * ds_credit_transactions is a serialized array in user meta, with a unix
	 * 'timestamp' per record. We sweep forward from the last timestamp seen.
	 *
	 * The dedupe key uses the array index, which is safe here because the
	 * writer appends without trimming. Rows are read in batches so a large
	 * user base never loads the whole meta table into memory at once.
	 */
	public function sweep_credits() {
		global $wpdb;

		$last   = (int) $this->get_mark( 'credit_ts', 0 );
		$max    = $last;
		$offset = 0;
		$batch  = 200;

		do {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT user_id, meta_value
					 FROM `{$wpdb->usermeta}`
					 WHERE meta_key = 'ds_credit_transactions'
					 ORDER BY umeta_id ASC
					 LIMIT %d OFFSET %d",
					$batch,
					$offset
				),
				ARRAY_A
			);

			if ( empty( $rows ) ) {
				break;
			}
			$offset += $batch;

			foreach ( $rows as $row ) {
				$txns = maybe_unserialize( $row['meta_value'] );
				if ( ! is_array( $txns ) ) {
					continue;
				}
				$uid = (int) $row['user_id'];

				foreach ( $txns as $i => $t ) {
					if ( ! is_array( $t ) ) {
						continue;
					}

					$ts = 0;
					if ( ! empty( $t['timestamp'] ) && is_numeric( $t['timestamp'] ) ) {
						$ts = (int) $t['timestamp'];
					} elseif ( ! empty( $t['date'] ) ) {
						$ts = strtotime( (string) $t['date'] );
					}
					if ( ! $ts || $ts <= $last ) {
						continue;
					}

					$credits = isset( $t['credits'] ) ? (float) $t['credits'] : 0;
					$type    = isset( $t['type'] ) ? strtolower( (string) $t['type'] ) : '';
					$desc    = isset( $t['description'] ) ? (string) $t['description'] : '';
					if ( '' === $desc && isset( $t['label'] ) ) {
						$desc = (string) $t['label'];
					}

					$this->log_event(
						array(
							'event_type'  => 'credit',
							'sub_type'    => $this->credit_bucket( $type, $desc, $t ),
							'user_id'     => $uid,
							'credits'     => $credits,
							'plan'        => $this->user_plan( $uid ),
							'meta'        => array(
								'type'  => $type,
								'desc'  => mb_substr( $desc, 0, 180 ),
								'model' => isset( $t['model'] ) ? (string) $t['model'] : '',
							),
							'dedupe_key'  => 'cr:' . $uid . ':' . $ts . ':' . $i,
							'occurred_at' => wp_date( 'Y-m-d H:i:s', $ts ),
						)
					);

					$max = max( $max, $ts );
				}
			}
		} while ( true );

		if ( $max > $last ) {
			$this->set_mark( 'credit_ts', $max );
		}
	}

	/**
	 * Classify a credit transaction into a reporting bucket.
	 * Spend buckets are negative-credit movements tied to a feature; the rest
	 * are top-ups / grants / refunds.
	 */
	private function credit_bucket( $type, $desc, $txn ) {
		$hay = strtolower( $type . ' ' . $desc . ' ' . ( isset( $txn['model'] ) ? $txn['model'] : '' ) . ' ' . ( isset( $txn['product_name'] ) ? $txn['product_name'] : '' ) );

		if ( preg_match( '/refund/', $hay ) ) {
			return 'refund';
		}
		if ( preg_match( '/topup|top-up|top_up|purchase|added|credit_?back|bonus|grant/', $hay ) ) {
			return 'added';
		}
		if ( preg_match( '/subscri|plan|renew/', $hay ) ) {
			return 'subscription_grant';
		}
		if ( preg_match( '/video/', $hay ) ) {
			return 'spend_video';
		}
		if ( preg_match( '/audio|speech|voice/', $hay ) ) {
			return 'spend_audio';
		}
		if ( preg_match( '/ppt|present|slide|deck/', $hay ) ) {
			return 'spend_presentation';
		}
		if ( preg_match( '/image|img|photo|picture/', $hay ) ) {
			return 'spend_image';
		}
		return 'spend_other';
	}

	/** ds_plan_history -> subscription events, bucketed by plan. */
	public function sweep_plans() {
		global $wpdb;

		$last = (int) $this->get_mark( 'plan_ts', 0 );
		$max  = $last;

		$offset = 0;
		$batch  = 200;

		while ( true ) {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT user_id, meta_value
					 FROM `{$wpdb->usermeta}`
					 WHERE meta_key = 'ds_plan_history'
					 ORDER BY umeta_id ASC
					 LIMIT %d OFFSET %d",
					$batch,
					$offset
				),
				ARRAY_A
			);

			if ( empty( $rows ) ) {
				break;
			}
			$offset += $batch;

			foreach ( $rows as $row ) {
				$hist = maybe_unserialize( $row['meta_value'] );
			if ( ! is_array( $hist ) ) {
				continue;
			}
			$uid = (int) $row['user_id'];

			foreach ( $hist as $i => $h ) {
				if ( ! is_array( $h ) ) {
					continue;
				}
				$ts = 0;
				if ( ! empty( $h['date'] ) ) {
					$ts = is_numeric( $h['date'] ) ? (int) $h['date'] : strtotime( (string) $h['date'] );
				}
				if ( ! $ts || $ts <= $last ) {
					continue;
				}

				$plan   = strtolower( (string) ( isset( $h['plan'] ) ? $h['plan'] : ( isset( $h['plan_label'] ) ? $h['plan_label'] : '' ) ) );
				$status = strtolower( (string) ( isset( $h['status'] ) ? $h['status'] : '' ) );

				$this->log_event(
					array(
						'event_type'  => 'subscription',
						'sub_type'    => $plan ? $plan : 'unknown',
						'user_id'     => $uid,
						'plan'        => $plan,
						'meta'        => array(
							'status' => $status,
							'from'   => isset( $h['from'] ) ? (string) $h['from'] : '',
							'sub_id' => isset( $h['sub_id'] ) ? (string) $h['sub_id'] : '',
						),
						'dedupe_key'  => 'plan:' . $uid . ':' . $ts . ':' . $i,
						'occurred_at' => wp_date( 'Y-m-d H:i:s', $ts ),
					)
				);

					$max = max( $max, $ts );
				}
			}
		}

		if ( $max > $last ) {
			$this->set_mark( 'plan_ts', $max );
		}
	}

	/** Download Monitor log -> download events (safety net for the live hook). */
	public function sweep_downloads() {
		global $wpdb;

		$log = $this->dlm_log_table();
		if ( ! $log ) {
			return;
		}

		$cols = $wpdb->get_col( "SHOW COLUMNS FROM `{$log}`" );
		if ( empty( $cols ) ) {
			return;
		}

		$date_col = in_array( 'download_date', $cols, true ) ? 'download_date' : ( in_array( 'created_at', $cols, true ) ? 'created_at' : '' );
		$dl_col   = in_array( 'download_id', $cols, true ) ? 'download_id' : '';
		$user_col = in_array( 'user_id', $cols, true ) ? 'user_id' : '';
		$id_col   = in_array( 'ID', $cols, true ) ? 'ID' : ( in_array( 'id', $cols, true ) ? 'id' : '' );

		if ( ! $date_col || ! $id_col ) {
			return;
		}

		$last = (int) $this->get_mark( 'dlm_log_id', 0 );

		$select = "`{$id_col}` AS lid, `{$date_col}` AS ldate";
		$select .= $dl_col ? ", `{$dl_col}` AS did" : ', 0 AS did';
		$select .= $user_col ? ", `{$user_col}` AS uid" : ', 0 AS uid';

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$select} FROM `{$log}` WHERE `{$id_col}` > %d ORDER BY `{$id_col}` ASC LIMIT 2000",
				$last
			),
			ARRAY_A
		);

		if ( empty( $rows ) ) {
			return;
		}

		foreach ( $rows as $r ) {
			$this->log_event(
				array(
					'event_type'  => 'download_log',
					'sub_type'    => 'dlm',
					'user_id'     => (int) $r['uid'],
					'object_id'   => (int) $r['did'],
					'plan'        => $r['uid'] ? $this->user_plan( (int) $r['uid'] ) : '',
					'dedupe_key'  => 'dlmlog:' . (int) $r['lid'],
					'occurred_at' => $r['ldate'],
				)
			);
			$last = max( $last, (int) $r['lid'] );
		}

		$this->set_mark( 'dlm_log_id', $last );
	}

	/** ds_creator_earnings -> paid download / revenue events. */
	public function sweep_earnings() {
		global $wpdb;

		$src = $wpdb->prefix . 'ds_creator_earnings';
		if ( ! $this->table_exists( $src ) ) {
			return;
		}

		$last = (int) $this->get_mark( 'earnings_id', 0 );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, file_id, creator_id, download_id, amount, platform_cut, sale_price, created_at
				 FROM `{$src}`
				 WHERE id > %d
				 ORDER BY id ASC
				 LIMIT 2000",
				$last
			),
			ARRAY_A
		);

		if ( empty( $rows ) ) {
			return;
		}

		foreach ( $rows as $r ) {
			$this->log_event(
				array(
					'event_type'  => 'paid_download',
					'sub_type'    => 'sale',
					'user_id'     => (int) $r['creator_id'],
					'object_id'   => (int) $r['download_id'],
					'amount'      => (float) $r['sale_price'],
					'meta'        => array(
						'creator_earning' => (float) $r['amount'],
						'platform_cut'    => (float) $r['platform_cut'],
						'file_id'         => (int) $r['file_id'],
					),
					'dedupe_key'  => 'earn:' . (int) $r['id'],
					'occurred_at' => $r['created_at'],
				)
			);
			$last = max( $last, (int) $r['id'] );
		}

		$this->set_mark( 'earnings_id', $last );
	}

	/* --------------------------------------------------------------------- *
	 * Aggregation
	 * --------------------------------------------------------------------- */

	/**
	 * Build the full metric set for the window ending at $date 20:00.
	 *
	 * @param string $date Y-m-d, the day the report is issued.
	 * @return array
	 */
	/**
	 * Subscription purchases inside the window, read straight from the
	 * ds_plan_history user meta that the payment code writes.
	 *
	 * Two credit figures are reported per purchase, because they legitimately
	 * differ (upgrades carry over a pool, manual grants and top-ups add more):
	 *   - plan credits  : what the plan is configured to give
	 *   - credits added : what ds_activate_plan() actually granted that day
	 *
	 * Rows written before the amount was recorded show 0.00 charged.
	 */
	public function subscription_purchases( $start, $end ) {
		global $wpdb;

		$from = $start->getTimestamp();
		$to   = $end->getTimestamp();

		$plan_defaults = array();
		if ( function_exists( 'ds_get_plans' ) ) {
			foreach ( ds_get_plans() as $key => $p ) {
				$plan_defaults[ $key ] = (int) ( isset( $p['credits'] ) ? $p['credits'] : 0 );
			}
		}

		$out    = array();
		$offset = 0;
		$batch  = 200;

		while ( true ) {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT user_id, meta_value
					 FROM `{$wpdb->usermeta}`
					 WHERE meta_key = 'ds_plan_history'
					 ORDER BY umeta_id ASC
					 LIMIT %d OFFSET %d",
					$batch,
					$offset
				),
				ARRAY_A
			);

			if ( empty( $rows ) ) {
				break;
			}
			$offset += $batch;

			foreach ( $rows as $row ) {
				$hist = maybe_unserialize( $row['meta_value'] );
				if ( ! is_array( $hist ) ) {
					continue;
				}
				$uid = (int) $row['user_id'];

				foreach ( $hist as $h ) {
					if ( ! is_array( $h ) || empty( $h['date'] ) ) {
						continue;
					}
					$ts = is_numeric( $h['date'] ) ? (int) $h['date'] : strtotime( (string) $h['date'] );
					if ( ! $ts || $ts < $from || $ts >= $to ) {
						continue;
					}

					$plan = strtolower( (string) ( isset( $h['plan'] ) ? $h['plan'] : '' ) );
					if ( '' === $plan || 'free' === $plan ) {
						continue; // downgrades and expiries are not purchases
					}

					$from_plan = strtolower( (string) ( isset( $h['from'] ) ? $h['from'] : '' ) );
					if ( '' === $from_plan || 'free' === $from_plan ) {
						$kind = 'New';
					} elseif ( $from_plan === $plan ) {
						$kind = 'Renewal';
					} else {
						$kind = 'Plan change';
					}

					$expected = isset( $h['plan_credits'] )
						? (int) $h['plan_credits']
						: ( isset( $plan_defaults[ $plan ] ) ? $plan_defaults[ $plan ] : 0 );

					$user = get_userdata( $uid );

					$out[] = array(
						'ts'       => $ts,
						'time'     => wp_date( 'Y-m-d H:i', $ts ),
						'user_id'  => $uid,
						'user'     => $user ? $user->user_login : ( '#' . $uid ),
						'email'    => $user ? $user->user_email : '',
						'plan'     => $plan,
						'from'     => $from_plan,
						'kind'     => $kind,
						'amount'   => (float) ( isset( $h['amount'] ) ? $h['amount'] : 0 ),
						'currency' => strtoupper( (string) ( isset( $h['currency'] ) ? $h['currency'] : 'INR' ) ),
						'expected' => $expected,
						'granted'  => (int) ( isset( $h['credits'] ) ? $h['credits'] : 0 ),
						'sub_id'   => (string) ( isset( $h['sub_id'] ) ? $h['sub_id'] : '' ),
					);
				}
			}
		}

		usort(
			$out,
			function ( $a, $b ) {
				return $a['ts'] - $b['ts'];
			}
		);

		return $out;
	}

	public function collect( $date ) {
		global $wpdb;

		list( $start, $end ) = $this->window_for( $date );

		$local_start = $this->local_sql( $start );
		$local_end   = $this->local_sql( $end );
		$utc_start   = $this->utc_sql( $start );
		$utc_end     = $this->utc_sql( $end );

		$table = $this->table();
		$data  = array(
			'date'        => $date,
			'window'      => $start->format( 'D d M Y H:i' ) . '  to  ' . $end->format( 'D d M Y H:i' ),
			'timezone'    => $start->format( 'T P' ),
			'generated'   => ( new DateTime( 'now', $this->tz() ) )->format( 'Y-m-d H:i:s' ),
			'notes'       => array(),
		);

		/* ---------- events from our own log ---------- */

		$ev = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT event_type, sub_type, plan, COUNT(*) AS n,
				        SUM(credits) AS credits, SUM(amount) AS amount
				 FROM `{$table}`
				 WHERE occurred_at >= %s AND occurred_at < %s
				 GROUP BY event_type, sub_type, plan",
				$local_start,
				$local_end
			),
			ARRAY_A
		);

		$gen_by_type   = array();
		$gen_by_plan   = array( 'free' => 0, 'paid' => 0 );
		$credit_bucket = array();
		$subs_by_plan  = array();
		$downloads     = 0;
		$paid_dl       = 0;
		$revenue       = 0.0;

		foreach ( $ev as $r ) {
			$n = (int) $r['n'];
			switch ( $r['event_type'] ) {
				case 'generation':
					$t                 = $r['sub_type'] ? $r['sub_type'] : 'other';
					$gen_by_type[ $t ] = ( isset( $gen_by_type[ $t ] ) ? $gen_by_type[ $t ] : 0 ) + $n;
					$tier              = ( 'free' === $r['plan'] || '' === $r['plan'] ) ? 'free' : 'paid';
					$gen_by_plan[ $tier ] += $n;
					break;

				case 'credit':
					$b                   = $r['sub_type'];
					$credit_bucket[ $b ] = ( isset( $credit_bucket[ $b ] ) ? $credit_bucket[ $b ] : 0 ) + (float) $r['credits'];
					break;

				case 'subscription':
					$p                  = $r['sub_type'] ? $r['sub_type'] : 'unknown';
					$subs_by_plan[ $p ] = ( isset( $subs_by_plan[ $p ] ) ? $subs_by_plan[ $p ] : 0 ) + $n;
					break;

				case 'download_log':
					$downloads += $n;
					break;

				case 'paid_download':
					$paid_dl += $n;
					$revenue += (float) $r['amount'];
					break;
			}
		}

		// Fall back to the live-hook download events if DLM logging is off.
		if ( 0 === $downloads ) {
			$downloads = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM `{$table}`
					 WHERE event_type = 'download' AND occurred_at >= %s AND occurred_at < %s",
					$local_start,
					$local_end
				)
			);
		}

		$data['generations']    = $gen_by_type;
		$data['gen_by_tier']    = $gen_by_plan;
		$data['credits']        = $credit_bucket;
		$data['subscriptions']  = $subs_by_plan;
		/* Never let the purchase detail take the whole report down with it. */
		try {
			$data['purchases'] = $this->subscription_purchases( $start, $end );
		} catch ( Throwable $e ) {
			$data['purchases'] = array();
			$data['notes'][]   = 'Subscription purchase detail unavailable: ' . $e->getMessage();
			error_log( 'DS Daily Report: subscription_purchases failed - ' . $e->getMessage() );
		}
		$data['downloads_all']  = $downloads;
		$data['downloads_paid'] = $paid_dl;
		$data['downloads_free'] = max( 0, $downloads - $paid_dl );
		$data['revenue']        = $revenue;

		$spent = 0.0;
		foreach ( $credit_bucket as $k => $v ) {
			if ( 0 === strpos( $k, 'spend_' ) ) {
				$spent += abs( $v );
			}
		}
		$data['credits_spent'] = $spent;

		/* ---------- signups (wp_users stores UTC) ---------- */

		$data['signups'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$wpdb->users}` WHERE user_registered >= %s AND user_registered < %s",
				$utc_start,
				$utc_end
			)
		);

		$data['signups_verified'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT um.user_id)
				 FROM `{$wpdb->usermeta}` um
				 WHERE um.meta_key = 'ds_email_verified_date'
				   AND um.meta_value >= %s AND um.meta_value < %s",
				$local_start,
				$local_end
			)
		);

		/* ---------- content: claims, reviews, comments ---------- */

		$data['copyright_claims'] = $this->count_posts( 'ds_copyright_claim', $local_start, $local_end );
		$data['reviews']          = $this->count_posts( 'dstg_review', $local_start, $local_end );
		$data['creator_uploads']  = $this->count_posts( 'ds_creator_upload', $local_start, $local_end );

		$data['comments'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$wpdb->comments}`
				 WHERE comment_date >= %s AND comment_date < %s AND comment_type IN ('','comment')",
				$local_start,
				$local_end
			)
		);
		$data['comments_pending'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$wpdb->comments}`
				 WHERE comment_date >= %s AND comment_date < %s
				   AND comment_type IN ('','comment') AND comment_approved = '0'",
				$local_start,
				$local_end
			)
		);

		/* ---------- customise design requests ---------- */

		$data['design_requests'] = $this->count_design_requests( $local_start, $local_end, $data['notes'] );

		/* ---------- traffic ---------- */

		$traffic          = $this->count_traffic( $start, $end, $data['notes'] );
		$data['visitors'] = $traffic['visitors'];
		$data['views']    = $traffic['views'];
		$data['traffic_basis'] = $traffic['basis'];

		return $data;
	}

	private function count_posts( $post_type, $from, $to ) {
		global $wpdb;
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$wpdb->posts}`
				 WHERE post_type = %s
				   AND post_status NOT IN ('auto-draft','inherit','trash')
				   AND post_date >= %s AND post_date < %s",
				$post_type,
				$from,
				$to
			)
		);
	}

	/**
	 * Customise / "Start Your Project" design requests.
	 * Counts SureForms entries and LatePoint bookings, whichever exist.
	 */
	private function count_design_requests( $from, $to, &$notes ) {
		global $wpdb;

		$total = 0;
		$found = false;

		$srfm = $wpdb->prefix . 'srfm_entries';
		if ( $this->table_exists( $srfm ) ) {
			$cols = $wpdb->get_col( "SHOW COLUMNS FROM `{$srfm}`" );
			$dcol = in_array( 'created_at', $cols, true ) ? 'created_at' : ( in_array( 'submitted_at', $cols, true ) ? 'submitted_at' : '' );
			if ( $dcol ) {
				$total += (int) $wpdb->get_var(
					$wpdb->prepare( "SELECT COUNT(*) FROM `{$srfm}` WHERE `{$dcol}` >= %s AND `{$dcol}` < %s", $from, $to )
				);
				$found = true;
			}
		}

		if ( ! $found ) {
			$total += $this->count_posts( 'sureforms_entry', $from, $to );
		}

		$lp = $wpdb->prefix . 'latepoint_bookings';
		if ( $this->table_exists( $lp ) ) {
			$total += (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM `{$lp}` WHERE created_at >= %s AND created_at < %s", $from, $to )
			);
			$found = true;
		}

		if ( ! $found ) {
			$notes[] = 'Design requests: no SureForms entries table or LatePoint bookings table was found, so this figure may be incomplete.';
		}

		return $total;
	}

	/**
	 * Visitors and page views.
	 * Prefers WP Statistics' per-visit datetime table so the 20:00-20:00 window
	 * is exact; falls back to its per-day counters (calendar day) otherwise.
	 */
	private function count_traffic( DateTime $start, DateTime $end, &$notes ) {
		global $wpdb;

		$rel = $wpdb->prefix . 'statistics_visitor_relationships';
		$vis = $wpdb->prefix . 'statistics_visitor';
		$pag = $wpdb->prefix . 'statistics_pages';

		$from = $this->local_sql( $start );
		$to   = $this->local_sql( $end );

		if ( $this->table_exists( $rel ) ) {
			$cols = $wpdb->get_col( "SHOW COLUMNS FROM `{$rel}`" );
			if ( in_array( 'date', $cols, true ) ) {
				$visitors = (int) $wpdb->get_var(
					$wpdb->prepare( "SELECT COUNT(DISTINCT visitor_id) FROM `{$rel}` WHERE `date` >= %s AND `date` < %s", $from, $to )
				);
				$views = (int) $wpdb->get_var(
					$wpdb->prepare( "SELECT COUNT(*) FROM `{$rel}` WHERE `date` >= %s AND `date` < %s", $from, $to )
				);
				if ( $visitors || $views ) {
					return array(
						'visitors' => $visitors,
						'views'    => $views,
						'basis'    => 'Exact 20:00-20:00 window (WP Statistics visit log)',
					);
				}
			}
		}

		// Fallback: calendar-day counters.
		$visitors = 0;
		$views    = 0;
		$day      = $end->format( 'Y-m-d' );

		if ( $this->table_exists( $vis ) ) {
			$visitors = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM `{$vis}` WHERE last_counter = %s", $day )
			);
		}
		if ( $this->table_exists( $pag ) ) {
			$views = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COALESCE(SUM(count),0) FROM `{$pag}` WHERE `date` = %s", $day )
			);
		}

		$notes[] = 'Traffic: WP Statistics stores per-day counters here, so visitors/views are for the calendar day ' . $day . ', not the exact 20:00-20:00 window.';

		return array(
			'visitors' => $visitors,
			'views'    => $views,
			'basis'    => 'Calendar day ' . $day . ' (WP Statistics daily counters)',
		);
	}

	/* --------------------------------------------------------------------- *
	 * Report building
	 * --------------------------------------------------------------------- */

	/**
	 * Turn the collected metrics into the sheet structures the writer expects.
	 */
	public function build_sheets( $data ) {
		$H = 'header';
		$T = 'title';
		$N = 'note';

		/* Subscription purchases: one pass, used by both the Summary and Sheet 5. */
		$purch   = ( isset( $data['purchases'] ) && is_array( $data['purchases'] ) ) ? $data['purchases'] : array();
		$by_plan = array();
		$buyers  = array();
		$tot_amt = 0.0;
		$tot_exp = 0;
		$tot_got = 0;
		foreach ( $purch as $p ) {
			$k = $p['plan'];
			if ( ! isset( $by_plan[ $k ] ) ) {
				$by_plan[ $k ] = array( 'n' => 0, 'amount' => 0.0, 'expected' => 0, 'granted' => 0 );
			}
			$by_plan[ $k ]['n']++;
			$by_plan[ $k ]['amount']   += (float) $p['amount'];
			$by_plan[ $k ]['expected'] += (int) $p['expected'];
			$by_plan[ $k ]['granted']  += (int) $p['granted'];
			$buyers[ $p['user_id'] ]    = true;
			$tot_amt += (float) $p['amount'];
			$tot_exp += (int) $p['expected'];
			$tot_got += (int) $p['granted'];
		}

		/* ---- Sheet 1: Summary ---- */
		$summary = array();
		$summary[] = array( array( 'v' => 'DesignStager - Daily Report', 's' => $T ) );
		$summary[] = array( 'Report date', $data['date'] );
		$summary[] = array( 'Window', $data['window'] );
		$summary[] = array( 'Timezone', $data['timezone'] );
		$summary[] = array( 'Generated at', $data['generated'] );
		$summary[] = array();

		$summary[] = array( array( 'v' => 'Metric', 's' => $H ), array( 'v' => 'Value', 's' => $H ) );

		$gen_total = array_sum( $data['generations'] );

		$rows = array(
			array( 'Visitors', (int) $data['visitors'] ),
			array( 'Page views', (int) $data['views'] ),
			array( 'New signups', (int) $data['signups'] ),
			array( 'Signups email-verified', (int) $data['signups_verified'] ),
			array( 'Downloads - total', (int) $data['downloads_all'] ),
			array( 'Downloads - free', (int) $data['downloads_free'] ),
			array( 'Downloads - paid', (int) $data['downloads_paid'] ),
			array( 'Revenue from paid downloads', array( 'v' => (float) $data['revenue'], 's' => 'money' ) ),
			array( 'AI generations - total', (int) $gen_total ),
			array( 'AI generations - by free-tier users', (int) $data['gen_by_tier']['free'] ),
			array( 'AI generations - by paid-tier users', (int) $data['gen_by_tier']['paid'] ),
			array( 'Credits spent', array( 'v' => (float) $data['credits_spent'], 's' => 'num2' ) ),
			array( 'New / changed subscriptions', (int) array_sum( $data['subscriptions'] ) ),
			array( 'Subscription purchases', count( $purch ) ),
			array( 'Users who purchased', count( $buyers ) ),
			array( 'Subscription amount charged', array( 'v' => (float) $tot_amt, 's' => 'money' ) ),
			array( 'Subscription credits - plan (expected)', (int) $tot_exp ),
			array( 'Subscription credits - actually added', (int) $tot_got ),
			array( 'Copyright claims', (int) $data['copyright_claims'] ),
			array( 'Reviews', (int) $data['reviews'] ),
			array( 'Comments', (int) $data['comments'] ),
			array( 'Comments awaiting moderation', (int) $data['comments_pending'] ),
			array( 'Customise design requests', (int) $data['design_requests'] ),
			array( 'Creator uploads submitted', (int) $data['creator_uploads'] ),
		);
		foreach ( $rows as $r ) {
			$summary[] = $r;
		}

		/* ---- Sheet 2: AI generations ---- */
		$gen   = array();
		$gen[] = array( array( 'v' => 'AI generations by type', 's' => $T ) );
		$gen[] = array( array( 'v' => 'Window', 's' => 'bold' ), $data['window'] );
		$gen[] = array();
		$gen[] = array( array( 'v' => 'Type', 's' => $H ), array( 'v' => 'Count', 's' => $H ) );

		$order = array( 'image', 'video', 'presentation', 'audio', 'other' );
		$seen  = array();
		foreach ( $order as $t ) {
			$gen[]        = array( ucfirst( $t ), (int) ( isset( $data['generations'][ $t ] ) ? $data['generations'][ $t ] : 0 ) );
			$seen[ $t ]   = true;
		}
		foreach ( $data['generations'] as $t => $n ) {
			if ( ! isset( $seen[ $t ] ) ) {
				$gen[] = array( ucfirst( $t ), (int) $n );
			}
		}
		$gen[] = array( array( 'v' => 'Total', 's' => 'bold' ), array( 'v' => (int) $gen_total, 's' => 'bold' ) );
		$gen[] = array();
		$gen[] = array( array( 'v' => 'Split by user tier', 's' => $H ), array( 'v' => 'Count', 's' => $H ) );
		$gen[] = array( 'Free-tier users', (int) $data['gen_by_tier']['free'] );
		$gen[] = array( 'Paying users', (int) $data['gen_by_tier']['paid'] );

		/* ---- Sheet 3: Credits ---- */
		$cr   = array();
		$cr[] = array( array( 'v' => 'Credit movement', 's' => $T ) );
		$cr[] = array( array( 'v' => 'Window', 's' => 'bold' ), $data['window'] );
		$cr[] = array();
		$cr[] = array( array( 'v' => 'Bucket', 's' => $H ), array( 'v' => 'Credits', 's' => $H ) );

		$labels = array(
			'spend_image'        => 'Spent - image',
			'spend_video'        => 'Spent - video',
			'spend_presentation' => 'Spent - presentation',
			'spend_audio'        => 'Spent - audio',
			'spend_other'        => 'Spent - other',
			'added'              => 'Added (top-ups / grants)',
			'subscription_grant' => 'Added (subscription)',
			'refund'             => 'Refunded',
		);
		foreach ( $labels as $k => $label ) {
			if ( isset( $data['credits'][ $k ] ) ) {
				$cr[] = array( $label, array( 'v' => (float) $data['credits'][ $k ], 's' => 'num2' ) );
			}
		}
		foreach ( $data['credits'] as $k => $v ) {
			if ( ! isset( $labels[ $k ] ) ) {
				$cr[] = array( $k, array( 'v' => (float) $v, 's' => 'num2' ) );
			}
		}
		$cr[] = array();
		$cr[] = array( array( 'v' => 'Total credits spent', 's' => 'bold' ), array( 'v' => (float) $data['credits_spent'], 's' => 'bold' ) );

		/* ---- Sheet 4: Downloads & revenue ---- */
		$dl   = array();
		$dl[] = array( array( 'v' => 'Downloads and revenue', 's' => $T ) );
		$dl[] = array( array( 'v' => 'Window', 's' => 'bold' ), $data['window'] );
		$dl[] = array();
		$dl[] = array( array( 'v' => 'Metric', 's' => $H ), array( 'v' => 'Value', 's' => $H ) );
		$dl[] = array( 'Total downloads', (int) $data['downloads_all'] );
		$dl[] = array( 'Free downloads', (int) $data['downloads_free'] );
		$dl[] = array( 'Paid downloads', (int) $data['downloads_paid'] );
		$dl[] = array( 'Revenue', array( 'v' => (float) $data['revenue'], 's' => 'money' ) );

		/* ---- Sheet 5: Subscriptions ---- */
		$sb   = array();
		$sb[] = array( array( 'v' => 'Subscriptions', 's' => $T ) );
		$sb[] = array( array( 'v' => 'Window', 's' => 'bold' ), $data['window'] );
		$sb[] = array();
		$sb[] = array( array( 'v' => 'Purchases', 's' => 'bold' ), count( $purch ) );
		$sb[] = array( array( 'v' => 'Users who purchased', 's' => 'bold' ), count( $buyers ) );
		$sb[] = array( array( 'v' => 'Amount charged', 's' => 'bold' ), array( 'v' => (float) $tot_amt, 's' => 'money' ) );
		$sb[] = array();

		/* By plan */
		$sb[] = array(
			array( 'v' => 'Plan', 's' => $H ),
			array( 'v' => 'Purchases', 's' => $H ),
			array( 'v' => 'Amount charged', 's' => $H ),
			array( 'v' => 'Plan credits (expected)', 's' => $H ),
			array( 'v' => 'Credits added (actual)', 's' => $H ),
			array( 'v' => 'Difference', 's' => $H ),
		);
		if ( empty( $by_plan ) ) {
			$sb[] = array( 'No subscription purchases in this window' );
		} else {
			foreach ( $by_plan as $plan_key => $v ) {
				$sb[] = array(
					ucfirst( $plan_key ),
					(int) $v['n'],
					array( 'v' => (float) $v['amount'], 's' => 'money' ),
					(int) $v['expected'],
					(int) $v['granted'],
					(int) ( $v['granted'] - $v['expected'] ),
				);
			}
			$sb[] = array(
				array( 'v' => 'Total', 's' => 'bold' ),
				count( $purch ),
				array( 'v' => (float) $tot_amt, 's' => 'money' ),
				(int) $tot_exp,
				(int) $tot_got,
				(int) ( $tot_got - $tot_exp ),
			);
		}

		/* Every individual purchase */
		$sb[] = array();
		$sb[] = array( array( 'v' => 'Every purchase in this window', 's' => 'bold' ) );
		$sb[] = array(
			array( 'v' => 'Time', 's' => $H ),
			array( 'v' => 'User', 's' => $H ),
			array( 'v' => 'Email', 's' => $H ),
			array( 'v' => 'Plan', 's' => $H ),
			array( 'v' => 'Type', 's' => $H ),
			array( 'v' => 'From', 's' => $H ),
			array( 'v' => 'Amount', 's' => $H ),
			array( 'v' => 'Plan credits (expected)', 's' => $H ),
			array( 'v' => 'Credits added (actual)', 's' => $H ),
			array( 'v' => 'Difference', 's' => $H ),
			array( 'v' => 'Subscription ID', 's' => $H ),
		);
		if ( empty( $purch ) ) {
			$sb[] = array( 'No subscription purchases in this window' );
		} else {
			foreach ( $purch as $p ) {
				$sb[] = array(
					$p['time'],
					$p['user'],
					$p['email'],
					ucfirst( $p['plan'] ),
					$p['kind'],
					$p['from'] ? ucfirst( $p['from'] ) : '',
					array( 'v' => (float) $p['amount'], 's' => 'money' ),
					(int) $p['expected'],
					(int) $p['granted'],
					(int) ( $p['granted'] - $p['expected'] ),
					$p['sub_id'],
				);
			}
		}

		/* Raw plan-change events, including downgrades and expiries */
		$sb[] = array();
		$sb[] = array( array( 'v' => 'All plan changes logged (includes downgrades and expiries)', 's' => 'bold' ) );
		$sb[] = array( array( 'v' => 'Plan', 's' => $H ), array( 'v' => 'Events', 's' => $H ) );
		if ( empty( $data['subscriptions'] ) ) {
			$sb[] = array( 'No subscription changes in this window', '' );
		} else {
			foreach ( $data['subscriptions'] as $plan_key => $n ) {
				$sb[] = array( ucfirst( $plan_key ), (int) $n );
			}
		}

		/* ---- Sheet 6: Engagement ---- */
		$en   = array();
		$en[] = array( array( 'v' => 'Engagement and requests', 's' => $T ) );
		$en[] = array( array( 'v' => 'Window', 's' => 'bold' ), $data['window'] );
		$en[] = array();
		$en[] = array( array( 'v' => 'Metric', 's' => $H ), array( 'v' => 'Value', 's' => $H ) );
		$en[] = array( 'Copyright claims', (int) $data['copyright_claims'] );
		$en[] = array( 'Reviews', (int) $data['reviews'] );
		$en[] = array( 'Comments', (int) $data['comments'] );
		$en[] = array( 'Comments awaiting moderation', (int) $data['comments_pending'] );
		$en[] = array( 'Customise design requests', (int) $data['design_requests'] );
		$en[] = array( 'Creator uploads submitted', (int) $data['creator_uploads'] );

		/* ---- Sheet 7: Notes ---- */
		$nt   = array();
		$nt[] = array( array( 'v' => 'How these numbers are produced', 's' => $T ) );
		$nt[] = array();
		$nt[] = array( array( 'v' => 'Traffic basis', 's' => 'bold' ), $data['traffic_basis'] );
		$nt[] = array();
		$nt[] = array( array( 'v' => 'Definitions', 's' => $H ) );
		$defs = array(
			'Window: 20:00 on the previous day up to 20:00 on the report date, in the site timezone.',
			'Free vs paid downloads: a download is "paid" when it produced a row in the creator earnings table; every other served download is counted as free.',
			'AI generations: taken from the generated-files table, split by file type (image, video, presentation, audio).',
			'Free-tier vs paying users: based on the user\'s current subscription plan, not the plan at the moment of generation.',
			'Credits spent: the sum of credit movements categorised as spend. Categories are derived from each transaction\'s type and description.',
			'Subscriptions: counted from plan-history entries written by the payment webhook, grouped by plan.',
			'Subscription purchases: plan-history entries in this window whose new plan is a paid plan. Downgrades and expiries are excluded, and are listed separately at the bottom of the Subscriptions sheet.',
			'Plan credits (expected) is what the plan is configured to grant. Credits added (actual) is what was actually granted on that purchase. They differ on upgrades, where an existing pool carries over, and whenever credits were adjusted by hand, so both are shown side by side.',
			'Amount charged comes from the payment gateway and is stored on the purchase from 12 Sep 2026 onwards. Purchases recorded before that show 0.00.',
			'All events are mirrored into a dedicated log table when they happen, so past days can be re-generated even after source rows are pruned.',
		);
		foreach ( $defs as $d ) {
			$nt[] = array( array( 'v' => $d, 's' => $N ) );
		}
		if ( ! empty( $data['notes'] ) ) {
			$nt[] = array();
			$nt[] = array( array( 'v' => 'Caveats for this report', 's' => $H ) );
			foreach ( $data['notes'] as $n ) {
				$nt[] = array( array( 'v' => $n, 's' => $N ) );
			}
		}

		return array(
			array(
				'name' => 'Summary',
				'rows' => $summary,
				'cols' => array( 42, 30 ),
			),
			array(
				'name' => 'AI Generations',
				'rows' => $gen,
				'cols' => array( 32, 18 ),
			),
			array(
				'name' => 'Credits',
				'rows' => $cr,
				'cols' => array( 34, 18 ),
			),
			array(
				'name' => 'Downloads',
				'rows' => $dl,
				'cols' => array( 32, 20 ),
			),
			array(
				'name' => 'Subscriptions',
				'rows' => $sb,
				'cols' => array( 18, 20, 30, 12, 14, 12, 14, 24, 24, 12, 22 ),
			),
			array(
				'name' => 'Engagement',
				'rows' => $en,
				'cols' => array( 36, 16 ),
			),
			array(
				'name' => 'Notes',
				'rows' => $nt,
				'cols' => array( 110 ),
			),
		);
	}

	/**
	 * Write the workbook to disk and return its absolute path (or WP_Error).
	 */
	public function write_xlsx( $data ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new WP_Error( 'no_zip', 'PHP ZipArchive is not available on this server, so the .xlsx cannot be created.' );
		}

		$sheets = $this->build_sheets( $data );
		$dir    = $this->storage_dir();
		if ( is_wp_error( $dir ) ) {
			return $dir;
		}

		$file = trailingslashit( $dir ) . 'designstager-daily-' . $data['date'] . '.xlsx';

		$zip = new ZipArchive();
		if ( true !== $zip->open( $file, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
			return new WP_Error( 'zip_open', 'Could not create the workbook file.' );
		}

		$n = count( $sheets );

		// Content types
		$ct  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		$ct .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
		$ct .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
		$ct .= '<Default Extension="xml" ContentType="application/xml"/>';
		$ct .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
		$ct .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
		for ( $i = 1; $i <= $n; $i++ ) {
			$ct .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
		}
		$ct .= '</Types>';
		$zip->addFromString( '[Content_Types].xml', $ct );

		$zip->addFromString(
			'_rels/.rels',
			'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
			. '</Relationships>'
		);

		// Workbook + rels
		$wb  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		$wb .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
		$rel = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
		foreach ( $sheets as $i => $s ) {
			$idx  = $i + 1;
			$wb  .= '<sheet name="' . $this->xml( $this->sheet_name( $s['name'] ) ) . '" sheetId="' . $idx . '" r:id="rId' . $idx . '"/>';
			$rel .= '<Relationship Id="rId' . $idx . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $idx . '.xml"/>';
		}
		$wb  .= '</sheets></workbook>';
		$rel .= '<Relationship Id="rId' . ( $n + 1 ) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
		$rel .= '</Relationships>';
		$zip->addFromString( 'xl/workbook.xml', $wb );
		$zip->addFromString( 'xl/_rels/workbook.xml.rels', $rel );
		$zip->addFromString( 'xl/styles.xml', $this->styles_xml() );

		foreach ( $sheets as $i => $s ) {
			$zip->addFromString( 'xl/worksheets/sheet' . ( $i + 1 ) . '.xml', $this->sheet_xml( $s ) );
		}

		$zip->close();

		return $file;
	}

	private function sheet_name( $name ) {
		$name = preg_replace( '/[\\\\\/\?\*\[\]:]/', '', (string) $name );
		return mb_substr( $name, 0, 31 );
	}

	private function xml( $s ) {
		return htmlspecialchars( (string) $s, ENT_QUOTES | ENT_XML1, 'UTF-8' );
	}

	/** Style index map - must match the order of cellXfs in styles_xml(). */
	private function style_index( $key ) {
		$map = array(
			''       => 0,
			'bold'   => 1,
			'header' => 2,
			'title'  => 3,
			'num2'   => 4,
			'money'  => 5,
			'note'   => 6,
		);
		return isset( $map[ $key ] ) ? $map[ $key ] : 0;
	}

	private function styles_xml() {
		$x  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		$x .= '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
		$x .= '<numFmts count="1"><numFmt numFmtId="164" formatCode="&quot;' . "\u{20B9}" . '&quot;#,##0.00"/></numFmts>';
		$x .= '<fonts count="5">';
		$x .= '<font><sz val="11"/><name val="Calibri"/></font>';
		$x .= '<font><b/><sz val="11"/><name val="Calibri"/></font>';
		$x .= '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>';
		$x .= '<font><b/><sz val="14"/><name val="Calibri"/></font>';
		$x .= '<font><i/><sz val="10"/><color rgb="FF666666"/><name val="Calibri"/></font>';
		$x .= '</fonts>';
		$x .= '<fills count="3">';
		$x .= '<fill><patternFill patternType="none"/></fill>';
		$x .= '<fill><patternFill patternType="gray125"/></fill>';
		$x .= '<fill><patternFill patternType="solid"><fgColor rgb="FF2F5597"/><bgColor indexed="64"/></patternFill></fill>';
		$x .= '</fills>';
		$x .= '<borders count="2">';
		$x .= '<border><left/><right/><top/><bottom/><diagonal/></border>';
		$x .= '<border><left/><right/><top/><bottom style="thin"><color rgb="FFBFBFBF"/></bottom><diagonal/></border>';
		$x .= '</borders>';
		$x .= '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>';
		$x .= '<cellXfs count="7">';
		$x .= '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>';                                     // 0 default
		$x .= '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>';                        // 1 bold
		$x .= '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1"/>';          // 2 header
		$x .= '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>';                        // 3 title
		$x .= '<xf numFmtId="2" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>';                // 4 num2
		$x .= '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>';              // 5 money
		$x .= '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment wrapText="1" vertical="top"/></xf>'; // 6 note
		$x .= '</cellXfs>';
		$x .= '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>';
		$x .= '</styleSheet>';
		return $x;
	}

	private function col_letter( $i ) {
		$s = '';
		$i++;
		while ( $i > 0 ) {
			$m = ( $i - 1 ) % 26;
			$s = chr( 65 + $m ) . $s;
			$i = intdiv( $i - 1 - $m, 26 );
		}
		return $s;
	}

	private function sheet_xml( $sheet ) {
		$x  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		$x .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

		if ( ! empty( $sheet['cols'] ) ) {
			$x .= '<cols>';
			foreach ( $sheet['cols'] as $i => $w ) {
				$x .= '<col min="' . ( $i + 1 ) . '" max="' . ( $i + 1 ) . '" width="' . (float) $w . '" customWidth="1"/>';
			}
			$x .= '</cols>';
		}

		$x .= '<sheetData>';

		foreach ( $sheet['rows'] as $r => $row ) {
			$rn = $r + 1;
			$x .= '<row r="' . $rn . '">';
			if ( is_array( $row ) ) {
				foreach ( $row as $c => $cell ) {
					$ref   = $this->col_letter( $c ) . $rn;
					$value = $cell;
					$style = '';

					if ( is_array( $cell ) ) {
						$value = isset( $cell['v'] ) ? $cell['v'] : '';
						$style = isset( $cell['s'] ) ? $cell['s'] : '';
					}

					if ( null === $value || '' === $value ) {
						if ( '' !== $style ) {
							$x .= '<c r="' . $ref . '" s="' . $this->style_index( $style ) . '"/>';
						}
						continue;
					}

					$si = ' s="' . $this->style_index( $style ) . '"';

					if ( is_int( $value ) || is_float( $value ) ) {
						$x .= '<c r="' . $ref . '"' . $si . '><v>' . ( is_float( $value ) ? rtrim( rtrim( number_format( $value, 4, '.', '' ), '0' ), '.' ) : $value ) . '</v></c>';
					} else {
						$x .= '<c r="' . $ref . '"' . $si . ' t="inlineStr"><is><t xml:space="preserve">' . $this->xml( $value ) . '</t></is></c>';
					}
				}
			}
			$x .= '</row>';
		}

		$x .= '</sheetData></worksheet>';
		return $x;
	}

	/** Private, protected directory for generated workbooks. */
	private function storage_dir() {
		$up = wp_upload_dir();
		if ( ! empty( $up['error'] ) ) {
			return new WP_Error( 'uploads', $up['error'] );
		}
		$dir = trailingslashit( $up['basedir'] ) . 'ds-daily-reports';
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		// Keep the folder unbrowsable.
		if ( ! file_exists( $dir . '/index.html' ) ) {
			@file_put_contents( $dir . '/index.html', '' );
		}
		if ( ! file_exists( $dir . '/.htaccess' ) ) {
			@file_put_contents( $dir . '/.htaccess', "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" );
		}
		return $dir;
	}

	/* --------------------------------------------------------------------- *
	 * Daily run
	 * --------------------------------------------------------------------- */

	/**
	 * Cron entry point. Sweeps, builds the workbook, mails it.
	 *
	 * @param string $date Optional Y-m-d override (defaults to today, site time).
	 * @param bool   $send Whether to email the result.
	 * @return array|WP_Error
	 */
	public function run_daily( $date = '', $send = true ) {
		// Make sure everything that happened in the window is mirrored first.
		$this->sweep_all();

		if ( empty( $date ) ) {
			$date = ( new DateTime( 'now', $this->tz() ) )->format( 'Y-m-d' );
		}

		$data = $this->collect( $date );
		$file = $this->write_xlsx( $data );

		if ( is_wp_error( $file ) ) {
			return $file;
		}

		$sent = false;
		if ( $send ) {
			$settings = $this->settings();
			if ( ! empty( $settings['enabled'] ) ) {
				$sent = $this->send_mail( $data, $file, $settings['recipients'] );
			}
		}

		$this->cleanup_old_files();

		update_option(
			'ds_daily_report_last_run',
			array(
				'date' => $date,
				'file' => basename( $file ),
				'sent' => $sent ? 1 : 0,
				'at'   => ( new DateTime( 'now', $this->tz() ) )->format( 'Y-m-d H:i:s' ),
			),
			false
		);

		return array(
			'data' => $data,
			'file' => $file,
			'sent' => $sent,
		);
	}

	private function send_mail( $data, $file, $recipients ) {
		$to = array_filter( array_map( 'trim', preg_split( '/[,;\s]+/', (string) $recipients ) ), 'is_email' );
		if ( empty( $to ) ) {
			return false;
		}

		$site    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$subject = sprintf( '%s - daily report %s', $site, $data['date'] );

		$gen_total = array_sum( $data['generations'] );

		$g = static function ( $arr, $k ) {
			return isset( $arr[ $k ] ) ? (int) $arr[ $k ] : 0;
		};

		$lines = array(
			'Daily activity for ' . $data['window'] . ' (' . $data['timezone'] . ')',
			'',
			'VISITORS & USERS',
			'  Visitors ................. ' . (int) $data['visitors'],
			'  Page views ............... ' . (int) $data['views'],
			'  New signups .............. ' . (int) $data['signups'],
			'  Email-verified ........... ' . (int) $data['signups_verified'],
			'',
			'DOWNLOADS',
			'  Total .................... ' . (int) $data['downloads_all'],
			'  Free ..................... ' . (int) $data['downloads_free'],
			'  Paid ..................... ' . (int) $data['downloads_paid'],
			'  Revenue .................. ' . number_format( (float) $data['revenue'], 2 ),
			'',
			'AI GENERATIONS (' . (int) $gen_total . ' total)',
			'  Image .................... ' . $g( $data['generations'], 'image' ),
			'  Video .................... ' . $g( $data['generations'], 'video' ),
			'  Presentation ............. ' . $g( $data['generations'], 'presentation' ),
			'  Audio .................... ' . $g( $data['generations'], 'audio' ),
			'  By free-tier users ....... ' . (int) $data['gen_by_tier']['free'],
			'  By paying users .......... ' . (int) $data['gen_by_tier']['paid'],
			'',
			'CREDITS',
			'  Spent .................... ' . number_format( (float) $data['credits_spent'], 2 ),
			'',
			'SUBSCRIPTIONS',
		);

		if ( empty( $data['subscriptions'] ) ) {
			$lines[] = '  No changes in this window';
		} else {
			foreach ( $data['subscriptions'] as $plan => $n ) {
				$lines[] = '  ' . str_pad( ucfirst( $plan ), 24, '.' ) . ' ' . (int) $n;
			}
		}

		$lines[] = '';
		$lines[] = 'ENGAGEMENT';
		$lines[] = '  Copyright claims ......... ' . (int) $data['copyright_claims'];
		$lines[] = '  Reviews .................. ' . (int) $data['reviews'];
		$lines[] = '  Comments ................. ' . (int) $data['comments'] . ' (' . (int) $data['comments_pending'] . ' awaiting moderation)';
		$lines[] = '  Design requests .......... ' . (int) $data['design_requests'];
		$lines[] = '  Creator uploads .......... ' . (int) $data['creator_uploads'];
		$lines[] = '';
		$lines[] = 'The attached workbook has the full breakdown, one sheet per area.';

		if ( ! empty( $data['notes'] ) ) {
			$lines[] = '';
			$lines[] = 'Notes:';
			foreach ( $data['notes'] as $n ) {
				$lines[] = '  - ' . $n;
			}
		}

		$body = implode( "\n", $lines );

		return wp_mail( $to, $subject, $body, array( 'Content-Type: text/plain; charset=UTF-8' ), array( $file ) );
	}

	private function cleanup_old_files() {
		$settings = $this->settings();
		$keep     = max( 7, (int) $settings['keep_files'] );
		$dir      = $this->storage_dir();
		if ( is_wp_error( $dir ) ) {
			return;
		}
		$cutoff = time() - ( $keep * DAY_IN_SECONDS );
		foreach ( (array) glob( trailingslashit( $dir ) . 'designstager-daily-*.xlsx' ) as $f ) {
			if ( is_file( $f ) && filemtime( $f ) < $cutoff ) {
				@unlink( $f );
			}
		}
	}

	/* --------------------------------------------------------------------- *
	 * Admin screen
	 * --------------------------------------------------------------------- */

	public function admin_menu() {
		add_submenu_page(
			'tools.php',
			'DS Daily Report',
			'DS Daily Report',
			'manage_options',
			'ds-daily-report',
			array( $this, 'render_admin' )
		);
	}

	private function action_url( $action, $extra = array() ) {
		$args = array_merge(
			array(
				'action'   => 'ds_daily_report_action',
				'ds_do'    => $action,
				'_wpnonce' => wp_create_nonce( 'ds_daily_report' ),
			),
			$extra
		);
		return add_query_arg( $args, admin_url( 'admin-post.php' ) );
	}

	public function render_admin() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$settings = $this->settings();
		$marks    = $this->get_marks();
		$last     = get_option( 'ds_daily_report_last_run', array() );
		$next     = wp_next_scheduled( self::CRON_HOOK );
		$table    = $this->table();

		$total_events = $this->table_exists( $table ) ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" ) : 0;

		// Timezone sanity check on the source table.
		$gen_table = $wpdb->prefix . 'ds_generated_files';
		$newest    = $this->table_exists( $gen_table ) ? $wpdb->get_var( "SELECT MAX(created_at) FROM `{$gen_table}`" ) : '';
		$now_local = ( new DateTime( 'now', $this->tz() ) )->format( 'Y-m-d H:i:s' );

		echo '<div class="wrap"><h1>DS Daily Report</h1>';

		if ( isset( $_GET['ds_msg'] ) ) {
			echo '<div class="notice notice-info is-dismissible"><p>' . esc_html( wp_unslash( $_GET['ds_msg'] ) ) . '</p></div>';
		}

		echo '<h2>Status</h2><table class="widefat striped" style="max-width:900px"><tbody>';
		printf( '<tr><td style="width:280px"><strong>Next scheduled run</strong></td><td>%s</td></tr>', $next ? esc_html( wp_date( 'D d M Y, H:i', $next ) ) : '<em>not scheduled</em>' );
		printf( '<tr><td><strong>Recipients</strong></td><td>%s</td></tr>', esc_html( $settings['recipients'] ) );
		printf( '<tr><td><strong>Daily email</strong></td><td>%s</td></tr>', ! empty( $settings['enabled'] ) ? 'On' : 'Off' );
		printf( '<tr><td><strong>Events mirrored</strong></td><td>%s</td></tr>', number_format_i18n( $total_events ) );
		printf(
			'<tr><td><strong>Last run</strong></td><td>%s</td></tr>',
			! empty( $last['at'] ) ? esc_html( $last['at'] . ' - ' . $last['file'] . ( ! empty( $last['sent'] ) ? ' (emailed)' : ' (not emailed)' ) ) : '<em>never</em>'
		);
		printf( '<tr><td><strong>Site time now</strong></td><td>%s</td></tr>', esc_html( $now_local ) );
		printf(
			'<tr><td><strong>Newest generated-file row</strong></td><td>%s</td></tr>',
			$newest ? esc_html( $newest ) . ' <span style="color:#666">(should be close to site time above - a large gap means that table stores UTC and the window needs shifting)</span>' : '<em>none</em>'
		);
		echo '</tbody></table>';

		echo '<h2>Detected sources</h2><table class="widefat striped" style="max-width:900px"><tbody>';
		$sources = array(
			'Generated files'  => $wpdb->prefix . 'ds_generated_files',
			'Creator earnings' => $wpdb->prefix . 'ds_creator_earnings',
			'Download Monitor' => $this->dlm_log_table(),
			'SureForms'        => $wpdb->prefix . 'srfm_entries',
			'LatePoint'        => $wpdb->prefix . 'latepoint_bookings',
			'WP Statistics'    => $wpdb->prefix . 'statistics_visitor',
		);
		foreach ( $sources as $label => $t ) {
			$ok = $t && $this->table_exists( $t );
			printf(
				'<tr><td style="width:280px"><strong>%s</strong></td><td>%s</td></tr>',
				esc_html( $label ),
				$ok ? '<span style="color:#0a0">found</span> <code>' . esc_html( $t ) . '</code>' : '<span style="color:#a00">not found</span>'
			);
		}
		echo '</tbody></table>';

		echo '<h2>Actions</h2><p>';
		printf( '<a class="button button-primary" href="%s">Run now and email</a> ', esc_url( $this->action_url( 'run_send' ) ) );
		printf( '<a class="button" href="%s">Build today\'s file (no email)</a> ', esc_url( $this->action_url( 'run_only' ) ) );
		printf( '<a class="button" href="%s">Sweep events now</a>', esc_url( $this->action_url( 'sweep' ) ) );
		echo '</p>';

		echo '<h2>Download a past day</h2>';
		echo '<form method="get" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="ds_daily_report_action">';
		echo '<input type="hidden" name="ds_do" value="download">';
		wp_nonce_field( 'ds_daily_report' );
		echo '<input type="date" name="ds_date" value="' . esc_attr( ( new DateTime( 'now', $this->tz() ) )->format( 'Y-m-d' ) ) . '" required> ';
		echo '<button class="button">Build and download</button>';
		echo '<p class="description">Builds the report for the window ending at 20:00 on the chosen date.</p>';
		echo '</form>';

		echo '<h2>Settings</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="ds_daily_report_action">';
		echo '<input type="hidden" name="ds_do" value="save">';
		wp_nonce_field( 'ds_daily_report' );
		echo '<table class="form-table"><tbody>';
		echo '<tr><th scope="row"><label for="ds_rec">Send to</label></th><td>';
		echo '<input type="text" class="regular-text" id="ds_rec" name="ds_recipients" value="' . esc_attr( $settings['recipients'] ) . '">';
		echo '<p class="description">Comma-separated email addresses.</p></td></tr>';
		echo '<tr><th scope="row">Daily email</th><td><label><input type="checkbox" name="ds_enabled" value="1" ' . checked( ! empty( $settings['enabled'] ), true, false ) . '> Send automatically at 20:00 every day</label></td></tr>';
		echo '<tr><th scope="row"><label for="ds_keep">Keep files for</label></th><td><input type="number" min="7" max="730" id="ds_keep" name="ds_keep" value="' . esc_attr( (int) $settings['keep_files'] ) . '"> days</td></tr>';
		echo '</tbody></table>';
		submit_button( 'Save settings' );
		echo '</form>';

		echo '</div>';
	}

	public function handle_admin_post() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not allowed.' );
		}
		check_admin_referer( 'ds_daily_report' );

		$do  = isset( $_REQUEST['ds_do'] ) ? sanitize_key( wp_unslash( $_REQUEST['ds_do'] ) ) : '';
		$msg = '';

		switch ( $do ) {
			case 'save':
				$rec = isset( $_POST['ds_recipients'] ) ? sanitize_text_field( wp_unslash( $_POST['ds_recipients'] ) ) : '';
				update_option(
					self::OPT_SETTINGS,
					array(
						'recipients' => $rec,
						'enabled'    => ! empty( $_POST['ds_enabled'] ) ? 1 : 0,
						'keep_files' => isset( $_POST['ds_keep'] ) ? max( 7, (int) $_POST['ds_keep'] ) : 90,
					),
					false
				);
				$msg = 'Settings saved.';
				break;

			case 'sweep':
				$this->sweep_all();
				$msg = 'Sweep complete.';
				break;

			case 'run_send':
			case 'run_only':
				$res = $this->run_daily( '', 'run_send' === $do );
				if ( is_wp_error( $res ) ) {
					$msg = 'Error: ' . $res->get_error_message();
				} else {
					$msg = 'Report built: ' . basename( $res['file'] ) . ( $res['sent'] ? ' and emailed.' : '.' );
					if ( 'run_send' === $do && ! $res['sent'] ) {
						$msg .= ' The email could not be sent - check WP Mail SMTP logs.';
					}
				}
				break;

			case 'download':
				$date = isset( $_REQUEST['ds_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ds_date'] ) ) : '';
				if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
					$msg = 'Invalid date.';
					break;
				}
				$this->sweep_all();
				$data = $this->collect( $date );
				$file = $this->write_xlsx( $data );
				if ( is_wp_error( $file ) ) {
					$msg = 'Error: ' . $file->get_error_message();
					break;
				}
				nocache_headers();
				header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
				header( 'Content-Disposition: attachment; filename="' . basename( $file ) . '"' );
				header( 'Content-Length: ' . filesize( $file ) );
				readfile( $file );
				exit;
		}

		wp_safe_redirect( add_query_arg( 'ds_msg', rawurlencode( $msg ), admin_url( 'tools.php?page=ds-daily-report' ) ) );
		exit;
	}
}

DS_Daily_Report::instance();