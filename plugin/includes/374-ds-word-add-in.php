<?php
/**
 * Code Snippets export — DS Word Add-in
 *
 * Snippet ID : 1259
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-22 07:19:41
 * Load order : 374 of 374
 */
/**
 * DS Word Add-in — DesignStager for Microsoft Word
 * - Task pane:  https://YOUR-SITE/?ds_word=taskpane  (AI Document, AI writing, images, icons, templates)
 * - Sign-in:    https://YOUR-SITE/?ds_word=connect   (opened by Word in a pop-up; the user clicks Allow)
 * - Manifest:   Settings → DesignStager Word → Download manifest.xml
 * The add-in signs in with its own tokens ("dsw_…", sent in the X-DSW-Token header). They are
 * stored hashed, work only for the routes listed in allowed_routes(), and can be removed by the
 * user any time (https://YOUR-SITE/?ds_word=manage).
 * Needs: "DS AI Document — Engine" and "DS AI Document — Backend".
 * Version: 1.0.0-08b2340d
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'DS_Word_Addin' ) ) :

final class DS_Word_Addin {
	const VER    = '1.0.0-08b2340d';
	const META   = '_ds_word_tokens';
	const PREFIX = 'dsw_';
	const OPT    = 'ds_word_settings';

	private static $uid = 0, $idx = null, $bad = false, $checked = false;

	public static function init() {
		add_filter( 'determine_current_user', array( __CLASS__, 'auth' ), 31 );
		add_filter( 'rest_authentication_errors', array( __CLASS__, 'auth_errors' ), 98 );
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'restrict' ), 6, 3 );
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'init', array( __CLASS__, 'pages' ), 2 );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_filter( 'wp_privacy_personal_data_erasers', function ( $e ) {
			$e['ds-word'] = array( 'eraser_friendly_name' => 'DesignStager Word add-in', 'callback' => function ( $email ) {
				$u = get_user_by( 'email', $email );
				if ( $u ) {
					delete_user_meta( $u->ID, DS_Word_Addin::META );
				}
				return array( 'items_removed' => (bool) $u, 'items_retained' => false, 'messages' => array(), 'done' => true );
			} );
			return $e;
		} );
	}

	public static function settings() {
		$s = get_option( self::OPT, array() );
		$s = is_array( $s ) ? $s : array();
		if ( empty( $s['id'] ) ) {
			$s['id'] = wp_generate_uuid4();
			update_option( self::OPT, $s );
		}
		return wp_parse_args( $s, array( 'enabled' => 1, 'version' => '1.0.0.0' ) );
	}
	private static function url( $what, $extra = array() ) {
		return add_query_arg( array_merge( array( 'ds_word' => $what ), $extra ), home_url( '/' ) );
	}

	/* ---------------- tokens ---------------- */

	private static function hash( $t ) { return hash_hmac( 'sha256', $t, wp_salt( 'auth' ) ); }
	private static function tokens( $uid ) { $t = get_user_meta( $uid, self::META, true ); return is_array( $t ) ? $t : array(); }
	private static function create_token( $uid ) {
		$tok    = self::PREFIX . (int) $uid . '_' . bin2hex( random_bytes( 24 ) );
		$list   = self::tokens( $uid );
		$list[] = array( 'id' => substr( md5( $tok ), 0, 10 ), 'hash' => self::hash( $tok ), 'created' => time(), 'last' => time() );
		update_user_meta( $uid, self::META, array_slice( $list, -10 ) );
		return $tok;
	}
	private static function header_token() {
		$t = isset( $_SERVER['HTTP_X_DSW_TOKEN'] ) ? trim( wp_unslash( $_SERVER['HTTP_X_DSW_TOKEN'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		return 0 === strpos( $t, self::PREFIX ) ? $t : '';
	}
	public static function auth( $user_id ) {
		if ( self::$checked ) {
			return self::$uid ? self::$uid : $user_id;
		}
		$t = self::header_token();
		if ( '' === $t ) {
			return $user_id;
		}
		self::$checked = true;
		if ( ! preg_match( '/^dsw_(\d+)_([a-f0-9]{48})$/', $t, $m ) ) {
			self::$bad = true;
			return 0;
		}
		$uid  = (int) $m[1];
		$h    = self::hash( $t );
		$list = self::tokens( $uid );
		foreach ( $list as $i => $row ) {
			if ( isset( $row['hash'] ) && hash_equals( $row['hash'], $h ) ) {
				if ( time() - (int) $row['last'] > 180 * DAY_IN_SECONDS ) {
					break; // unused for 6 months: expired
				}
				if ( time() - (int) $row['last'] > HOUR_IN_SECONDS ) {
					$list[ $i ]['last'] = time();
					update_user_meta( $uid, self::META, $list );
				}
				self::$uid = $uid;
				self::$idx = $i;
				if ( ! defined( 'DS_WORD_ADDIN_REQUEST' ) ) {
					define( 'DS_WORD_ADDIN_REQUEST', true );
				}
				return $uid;
			}
		}
		self::$bad = true;
		return 0;
	}
	public static function auth_errors( $r ) {
		if ( ! empty( $r ) ) {
			return $r;
		}
		get_current_user_id();
		return self::$bad ? new WP_Error( 'ds_word_signed_out', 'Your DesignStager sign-in has expired. Please sign in again.', array( 'status' => 401 ) ) : $r;
	}
	private static function allowed_routes() {
		return apply_filters( 'ds_word_allowed_routes', array(
			'#^/ds/v1/(credits|my-profile|mkt-slide-images|mkt-auto-image|generate-image|poll-image|doc-start|doc-section|doc-finish|doc-assist|doc-cost|my-docs|save-doc|load-doc|delete-doc)$#',
			'#^/dsw/v1/(me|revoke|file-link)$#',
		) );
	}
	public static function restrict( $result, $server, $request ) {
		if ( ! self::$uid ) {
			return $result;
		}
		foreach ( self::allowed_routes() as $re ) {
			if ( preg_match( $re, $request->get_route() ) ) {
				return $result;
			}
		}
		return new WP_Error( 'ds_word_route', 'This is not available in the Word add-in.', array( 'status' => 403 ) );
	}

	/* ---------------- REST ---------------- */

	public static function routes() {
		$tok = function () { return DS_Word_Addin::$uid ? true : new WP_Error( 'ds_word_signed_out', 'Please sign in to DesignStager.', array( 'status' => 401 ) ); };
		register_rest_route( 'dsw/v1', '/me', array( 'methods' => 'GET', 'permission_callback' => $tok, 'callback' => function () {
			$u    = wp_get_current_user();
			$r    = rest_do_request( new WP_REST_Request( 'GET', '/ds/v1/credits' ) );
			$d    = $r->is_error() ? array() : (array) $r->get_data();
			$cred = isset( $d['credits'] ) ? (float) $d['credits'] : ( function_exists( 'ds_get_credits' ) ? (float) ds_get_credits( $u->ID ) : 0 );
			$s    = class_exists( 'DS_AI_Doc' ) ? DS_AI_Doc::settings() : array( 'assist_cost' => 1, 'max_pages' => 20 );
			return array( 'name' => $u->display_name, 'credits' => $cred, 'plan' => isset( $d['plan'] ) ? sanitize_key( $d['plan'] ) : 'free', 'plan_label' => isset( $d['plan_label'] ) ? sanitize_text_field( $d['plan_label'] ) : '',
				'pricing' => function_exists( 'ds_doc_pricing' ) ? ds_doc_pricing() : null, 'assistCost' => (int) $s['assist_cost'], 'maxPages' => (int) $s['max_pages'],
				'site' => home_url( '/' ), 'docUrl' => function_exists( 'ds_doc_page_url' ) ? ds_doc_page_url() : home_url( '/ai-document/' ), 'manage' => DS_Word_Addin::url( 'manage' ) );
		} ) );
		register_rest_route( 'dsw/v1', '/revoke', array( 'methods' => 'POST', 'permission_callback' => $tok, 'callback' => function () {
			$list = DS_Word_Addin::tokens( DS_Word_Addin::$uid );
			unset( $list[ DS_Word_Addin::$idx ] );
			update_user_meta( DS_Word_Addin::$uid, DS_Word_Addin::META, array_values( $list ) );
			return array( 'revoked' => true );
		} ) );
		// AI images are private files on the site; give the add-in a short-lived direct link.
		register_rest_route( 'dsw/v1', '/file-link', array( 'methods' => 'POST', 'permission_callback' => $tok, 'callback' => function ( $req ) {
			$url = (string) $req->get_param( 'url' );
			if ( preg_match( '#/wp-json/ds-hub/v1/file/(\d+)#', $url, $m ) && function_exists( 'ds_hub_get_generated_file' ) && function_exists( 'ds_hub_get_download_url' ) ) {
				$f = ds_hub_get_generated_file( (int) $m[1] );
				if ( ! $f || ( function_exists( 'ds_hub_user_owns_file' ) && ! ds_hub_user_owns_file( $f, get_current_user_id() ) ) ) {
					return new WP_Error( 'ds_word_file', 'File not found.', array( 'status' => 404 ) );
				}
				return array( 'url' => ds_hub_get_download_url( $f ) );
			}
			return array( 'url' => esc_url_raw( $url ) );
		} ) );
	}

	/* ---------------- pages: task pane, sign-in, manage, manifest, icons ---------------- */

	public static function pages() {
		if ( ! isset( $_GET['ds_word'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$p = sanitize_key( wp_unslash( $_GET['ds_word'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$s = self::settings();
		if ( empty( $s['enabled'] ) && 'manifest' !== $p ) {
			wp_die( 'The Word add-in is turned off.', 'DesignStager', array( 'response' => 503 ) );
		}
		nocache_headers();
		do_action( 'litespeed_control_set_nocache', 'ds word add-in' );
		if ( 'taskpane' === $p ) {
			self::taskpane();
		} elseif ( 'commands' === $p ) {
			echo '<!doctype html><html><head><meta charset="utf-8"><script src="https://appsforoffice.microsoft.com/lib/1/hosted/office.js"></script></head><body><script>Office.onReady(function(){});</script></body></html>';
		} elseif ( 'connect' === $p ) {
			self::connect();
		} elseif ( 'manage' === $p ) {
			self::manage();
		} elseif ( 'manifest' === $p ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'Only administrators can download the manifest.', 'DesignStager', array( 'response' => 403 ) );
			}
			header( 'Content-Type: application/xml; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="designstager-word-manifest.xml"' );
			echo self::manifest(); // phpcs:ignore WordPress.Security.EscapeOutput
		} elseif ( 'icon' === $p ) {
			self::icon( isset( $_GET['s'] ) ? (int) $_GET['s'] : 32 ); // phpcs:ignore WordPress.Security.NonceVerification
		} else {
			status_header( 404 );
		}
		exit;
	}

	private static function shell( $title, $body, $office = false ) {
		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'Referrer-Policy: no-referrer' );
		echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . esc_html( $title ) . '</title>' .
			( $office ? '<script src="https://appsforoffice.microsoft.com/lib/1/hosted/office.js"></script>' : '' ) .
			'<style>body{margin:0;font:14px/1.5 "Segoe UI",system-ui,sans-serif;color:#1c2333;background:#f4f6fb}.w{max-width:440px;margin:24px auto;background:#fff;border-radius:14px;padding:24px;box-shadow:0 4px 24px rgba(20,30,60,.08)}h1{font-size:20px;margin:0 0 10px}.b{display:inline-block;border:0;border-radius:9px;padding:11px 20px;font:600 14px "Segoe UI",sans-serif;cursor:pointer;margin:6px 6px 0 0}.p{background:#2F6FED;color:#fff}.s{background:#eef1f7;color:#1c2333}ul{padding-left:20px}.ok{font-size:40px;color:#16a34a}.m{color:#5b6475;font-size:13px}table{width:100%;border-collapse:collapse}td,th{padding:6px;border-bottom:1px solid #e5e8ef;text-align:left;font-size:13px}</style></head><body><div class="w">' .
			$body . '</div></body></html>';
		exit;
	}

	private static function connect() {
		$here = self::url( 'connect' );
		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( wp_login_url( $here ) );
			exit;
		}
		$u = wp_get_current_user();
		header( 'X-Frame-Options: DENY' );
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			check_admin_referer( 'ds_word_allow' );
			if ( empty( $_POST['allow'] ) ) {
				self::shell( 'Not connected', '<h1>Not connected</h1><p>You did not connect Word. You can close this window.</p><script>Office.onReady(function(){try{Office.context.ui.messageParent(JSON.stringify({cancel:true}));}catch(e){}});</script>', true );
			}
			$tok = self::create_token( $u->ID );
			self::shell( 'Connected', '<div class="ok">&#10003;</div><h1>Word is connected</h1><p>Signed in as <b>' . esc_html( $u->display_name ) . '</b>. This window closes by itself.</p><p class="m" id="m"></p>' .
				'<script>Office.onReady(function(){try{Office.context.ui.messageParent(JSON.stringify({token:' . wp_json_encode( $tok ) . '}));}catch(e){document.getElementById("m").textContent="Open this from the DesignStager add-in in Word to finish connecting.";}});</script>', true );
		}
		$body = '<h1>Connect Microsoft Word</h1><p>Signed in as <b>' . esc_html( $u->display_name ) . '</b>.</p><p>The DesignStager add-in for Word will be able to:</p><ul><li>See your name, plan and credit balance</li><li>Create AI documents, AI text and AI images using your credits</li><li>Use marketplace images and open your saved documents</li></ul><p>It will <b>not</b> see your password or payment details, or change your account.</p>' .
			'<form method="post">' . wp_nonce_field( 'ds_word_allow', '_wpnonce', true, false ) . '<button class="b p" name="allow" value="1">Allow</button><button class="b s" name="cancel" value="1">Cancel</button></form>' .
			'<p class="m"><a href="' . esc_url( self::url( 'manage' ) ) . '" target="_blank" rel="noopener">Manage connected apps</a></p>';
		self::shell( 'Connect Word', $body, true );
	}

	private static function manage() {
		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( wp_login_url( self::url( 'manage' ) ) );
			exit;
		}
		$uid = get_current_user_id();
		$msg = '';
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			check_admin_referer( 'ds_word_manage' );
			delete_user_meta( $uid, self::META );
			$msg = '<p><b>All Word connections were removed.</b></p>';
		}
		$rows = '';
		foreach ( self::tokens( $uid ) as $t ) {
			$rows .= '<tr><td>Microsoft Word</td><td>' . esc_html( wp_date( get_option( 'date_format' ), (int) $t['created'] ) ) . '</td><td>' . esc_html( wp_date( get_option( 'date_format' ), (int) $t['last'] ) ) . '</td></tr>';
		}
		$body = '<h1>Connected Word add-ins</h1>' . $msg . ( $rows ? '<table><tr><th>App</th><th>Connected</th><th>Last used</th></tr>' . $rows . '</table><form method="post">' . wp_nonce_field( 'ds_word_manage', '_wpnonce', true, false ) . '<button class="b s">Disconnect all</button></form>' : '<p>No Word connections.</p>' );
		self::shell( 'Connected apps', $body );
	}

	public static function manifest() {
		$s    = self::settings();
		$host = wp_parse_url( home_url(), PHP_URL_SCHEME ) . '://' . wp_parse_url( home_url(), PHP_URL_HOST );
		$e    = function ( $v ) { return esc_attr( $v ); };
		$tp   = self::url( 'taskpane' );
		$ic   = function ( $n ) { return esc_attr( DS_Word_Addin::url( 'icon', array( 's' => $n ) ) ); };
		$support = function_exists( 'ds_doc_page_url' ) ? home_url( '/contact-us/' ) : home_url( '/' );
		return '<?xml version="1.0" encoding="UTF-8"?>
<OfficeApp xmlns="http://schemas.microsoft.com/office/appforoffice/1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:bt="http://schemas.microsoft.com/office/officeappbasictypes/1.0" xmlns:ov="http://schemas.microsoft.com/office/taskpaneappversionoverrides" xsi:type="TaskPaneApp">
  <Id>' . $e( $s['id'] ) . '</Id>
  <Version>' . $e( $s['version'] ) . '</Version>
  <ProviderName>DesignStager</ProviderName>
  <DefaultLocale>en-US</DefaultLocale>
  <DisplayName DefaultValue="DesignStager for Word"/>
  <Description DefaultValue="Create designed documents with AI, rewrite and translate text, and add images, icons and templates from DesignStager."/>
  <IconUrl DefaultValue="' . $ic( 32 ) . '"/>
  <HighResolutionIconUrl DefaultValue="' . $ic( 64 ) . '"/>
  <SupportUrl DefaultValue="' . $e( $support ) . '"/>
  <AppDomains>
    <AppDomain>' . $e( $host ) . '</AppDomain>
  </AppDomains>
  <Hosts>
    <Host Name="Document"/>
  </Hosts>
  <Requirements>
    <Sets DefaultMinVersion="1.1">
      <Set Name="WordApi" MinVersion="1.1"/>
    </Sets>
  </Requirements>
  <DefaultSettings>
    <SourceLocation DefaultValue="' . $e( $tp ) . '"/>
  </DefaultSettings>
  <Permissions>ReadWriteDocument</Permissions>
  <VersionOverrides xmlns="http://schemas.microsoft.com/office/taskpaneappversionoverrides" xsi:type="VersionOverridesV1_0">
    <Hosts>
      <Host xsi:type="Document">
        <DesktopFormFactor>
          <GetStarted>
            <Title resid="DSW.GetStarted.Title"/>
            <Description resid="DSW.GetStarted.Description"/>
            <LearnMoreUrl resid="DSW.Site.Url"/>
          </GetStarted>
          <FunctionFile resid="DSW.Commands.Url"/>
          <ExtensionPoint xsi:type="PrimaryCommandSurface">
            <OfficeTab id="TabHome">
              <Group id="DSW.Group">
                <Label resid="DSW.Group.Label"/>
                <Icon>
                  <bt:Image size="16" resid="DSW.Icon.16"/>
                  <bt:Image size="32" resid="DSW.Icon.32"/>
                  <bt:Image size="80" resid="DSW.Icon.80"/>
                </Icon>
                <Control xsi:type="Button" id="DSW.Open">
                  <Label resid="DSW.Open.Label"/>
                  <Supertip>
                    <Title resid="DSW.Open.Label"/>
                    <Description resid="DSW.Open.Tip"/>
                  </Supertip>
                  <Icon>
                    <bt:Image size="16" resid="DSW.Icon.16"/>
                    <bt:Image size="32" resid="DSW.Icon.32"/>
                    <bt:Image size="80" resid="DSW.Icon.80"/>
                  </Icon>
                  <Action xsi:type="ShowTaskpane">
                    <TaskpaneId>DSWPane</TaskpaneId>
                    <SourceLocation resid="DSW.Taskpane.Url"/>
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
        <bt:Image id="DSW.Icon.16" DefaultValue="' . $ic( 16 ) . '"/>
        <bt:Image id="DSW.Icon.32" DefaultValue="' . $ic( 32 ) . '"/>
        <bt:Image id="DSW.Icon.80" DefaultValue="' . $ic( 80 ) . '"/>
      </bt:Images>
      <bt:Urls>
        <bt:Url id="DSW.Commands.Url" DefaultValue="' . $e( self::url( 'commands' ) ) . '"/>
        <bt:Url id="DSW.Taskpane.Url" DefaultValue="' . $e( $tp ) . '"/>
        <bt:Url id="DSW.Site.Url" DefaultValue="' . $e( home_url( '/' ) ) . '"/>
      </bt:Urls>
      <bt:ShortStrings>
        <bt:String id="DSW.GetStarted.Title" DefaultValue="DesignStager is ready"/>
        <bt:String id="DSW.Group.Label" DefaultValue="DesignStager"/>
        <bt:String id="DSW.Open.Label" DefaultValue="DesignStager"/>
      </bt:ShortStrings>
      <bt:LongStrings>
        <bt:String id="DSW.GetStarted.Description" DefaultValue="Open DesignStager from the Home tab to create AI documents, rewrite text and add images."/>
        <bt:String id="DSW.Open.Tip" DefaultValue="Create designed documents with AI, rewrite text and add images, icons and templates."/>
      </bt:LongStrings>
    </Resources>
  </VersionOverrides>
</OfficeApp>';
	}

	/** Add-in icons from the site icon (or a simple document mark when there is none). */
	private static function icon( $size ) {
		$size = in_array( $size, array( 16, 32, 64, 80, 128 ), true ) ? $size : 32;
		header( 'Content-Type: image/png' );
		header( 'Cache-Control: public, max-age=604800' );
		$src = function_exists( 'get_site_icon_url' ) ? get_site_icon_url( 512 ) : '';
		$im  = imagecreatetruecolor( $size, $size );
		imagesavealpha( $im, true );
		imagefill( $im, 0, 0, imagecolorallocatealpha( $im, 0, 0, 0, 127 ) );
		$done = false;
		if ( $src ) {
			$path = str_replace( wp_get_upload_dir()['baseurl'], wp_get_upload_dir()['basedir'], $src );
			$data = is_file( $path ) ? file_get_contents( $path ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
			$s2   = $data ? @imagecreatefromstring( $data ) : false; // phpcs:ignore WordPress.PHP.NoSilencedErrors
			if ( $s2 ) {
				imagecopyresampled( $im, $s2, 0, 0, 0, 0, $size, $size, imagesx( $s2 ), imagesy( $s2 ) );
				$done = true;
			}
		}
		if ( ! $done ) {
			$k    = $size / 32;
			$blue = imagecolorallocate( $im, 47, 111, 237 );
			$wh   = imagecolorallocate( $im, 255, 255, 255 );
			$or   = imagecolorallocate( $im, 240, 83, 43 );
			imagefilledrectangle( $im, (int) ( 5 * $k ), (int) ( 2 * $k ), (int) ( 27 * $k ), (int) ( 30 * $k ), $blue );
			for ( $i = 0; $i < 4; $i++ ) {
				imagefilledrectangle( $im, (int) ( 9 * $k ), (int) ( ( 9 + $i * 5 ) * $k ), (int) ( ( $i === 3 ? 18 : 23 ) * $k ), (int) ( ( 10.6 + $i * 5 ) * $k ), $wh );
			}
			imagefilledrectangle( $im, (int) ( 19 * $k ), (int) ( 22 * $k ), (int) ( 27 * $k ), (int) ( 30 * $k ), $or );
		}
		imagepng( $im );
		imagedestroy( $im );
	}

	private static function taskpane() {
		header( 'Content-Type: text/html; charset=utf-8' );
		$v   = rawurlencode( self::VER );
		$cfg = array( 'rest' => esc_url_raw( rest_url() ), 'connect' => self::url( 'connect' ), 'site' => home_url( '/' ), 'ver' => self::VER );
		echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>DesignStager</title>' .
			'<script src="https://appsforoffice.microsoft.com/lib/1/hosted/office.js"></script>' .
			'<style>' . self::css() . '</style></head><body><div id="dsw"><div class="dsw-load">Loading DesignStager…</div></div>' .
			'<script>window.DSW_CFG=' . wp_json_encode( $cfg ) . ';</script>' .
			'<script src="' . esc_url( add_query_arg( array( 'ds_doc_asset' => 'engine', 'v' => $v ), home_url( '/' ) ) ) . '"></script>' .
			'<script>' . self::js() . '</script></body></html>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	private static function js() {
		return <<<'DSJS'
/* DesignStager for Word — task pane. Needs window.DSDOC (engine), window.DSW_CFG and office.js. */
(function () {
  'use strict';
  var D = window.DSDOC, CFG = window.DSW_CFG || {};
  var root = document.getElementById('dsw');
  if (!D) { root.innerHTML = '<div class="dsw-load">DesignStager could not load. Check your connection and reopen the add-in.</div>'; return; }
  D.injectCSS();

  function $(s, r) { return (r || root).querySelector(s); }
  function $$(s, r) { return Array.prototype.slice.call((r || root).querySelectorAll(s)); }
  function el(t, c, h) { var e = document.createElement(t); if (c) e.className = c; if (h != null) e.innerHTML = h; return e; }
  var esc = D.esc;
  var S = { tab: 'doc', token: '', me: null, inWord: false, wordApi13: false, type: 'report', tpl: 'meridian', theme: '', pages: 5, lang: 'English', tone: 'Professional', result: null, docs: [] };
  var LANGS = ['English', 'Hindi', 'Kannada', 'Tamil', 'Telugu', 'Marathi', 'Bengali', 'Gujarati', 'Malayalam', 'Spanish', 'French', 'German', 'Portuguese', 'Italian', 'Arabic', 'Japanese', 'Chinese'];
  var TONES = ['Professional', 'Friendly', 'Persuasive', 'Academic', 'Simple and clear', 'Formal', 'Inspiring'];

  try { S.token = localStorage.getItem('dsw_token') || ''; var pr = JSON.parse(localStorage.getItem('dsw_prefs') || '{}'); ['type', 'tpl', 'theme', 'lang', 'tone', 'pages'].forEach(function (k) { if (pr[k] != null) S[k] = pr[k]; }); } catch (e) { /* storage blocked */ }
  function savePrefs() { try { localStorage.setItem('dsw_prefs', JSON.stringify({ type: S.type, tpl: S.tpl, theme: S.theme, lang: S.lang, tone: S.tone, pages: S.pages })); } catch (e) { /* ignore */ } }
  function setToken(t) { S.token = t || ''; try { if (t) localStorage.setItem('dsw_token', t); else localStorage.removeItem('dsw_token'); } catch (e) { /* ignore */ } }

  /* ---------- status / busy ---------- */
  function status(kind, text) {
    var s = $('#dsw-status'); if (!s) return;
    s.className = 'dsw-status ' + (kind || ''); s.innerHTML = (kind === 'busy' ? '<span class="dsw-spin"></span>' : '') + esc(text || '');
    s.style.display = text ? 'flex' : 'none';
    clearTimeout(s.__t); if (kind === 'ok') s.__t = setTimeout(function () { s.style.display = 'none'; }, 3500);
  }

  /* ---------- API ---------- */
  function api(method, path, body) {
    var h = { 'Content-Type': 'application/json' };
    if (S.token) h['X-DSW-Token'] = S.token;
    return fetch(CFG.rest + path, { method: method, headers: h, body: body ? JSON.stringify(body) : undefined, credentials: 'omit' })
      .then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { return { r: r, j: j }; }); })
      .then(function (x) {
        var j = x.j || {};
        if (x.r.status === 401) { setToken(''); S.me = null; draw(); var e1 = new Error(j.message || 'Please sign in to DesignStager.'); e1.status = 401; throw e1; }
        if (!x.r.ok || j.success === false) { var e = new Error(j.message || 'Something went wrong (' + x.r.status + ').'); e.status = x.r.status; e.data = j; throw e; }
        return j;
      });
  }
  function needSignIn() { if (S.token && S.me) return false; status('err', 'Please sign in to DesignStager first.'); return true; }
  function credits(v) { if (v == null || !S.me) return; S.me.credits = +v; var n = $('#dsw-cred'); if (n) n.textContent = (Math.round(S.me.credits * 100) / 100).toLocaleString(); }

  /* ---------- sign-in through an Office dialog ---------- */
  function signIn() {
    if (!S.inWord || !Office.context.ui || !Office.context.ui.displayDialogAsync) { window.open(CFG.connect, '_blank'); status('', 'Finish signing in in the window that opened, then reopen the add-in.'); return; }
    status('busy', 'Waiting for you to sign in…');
    Office.context.ui.displayDialogAsync(CFG.connect, { height: 75, width: 34, promptBeforeOpen: false }, function (res) {
      if (res.status !== Office.AsyncResultStatus.Succeeded) { status('err', 'The sign-in window could not open. Allow pop-ups for Word and try again.'); return; }
      var dlg = res.value;
      dlg.addEventHandler(Office.EventType.DialogMessageReceived, function (arg) {
        var m = {}; try { m = JSON.parse(arg.message); } catch (e) { /* ignore */ }
        dlg.close();
        if (m.token) { setToken(m.token); status('ok', 'Signed in.'); boot(); } else status('', 'Sign-in was cancelled.');
      });
      dlg.addEventHandler(Office.EventType.DialogEventReceived, function () { status('', ''); });
    });
  }
  function signOut() {
    var t = S.token;
    if (t) api('POST', 'dsw/v1/revoke').catch(function () {});
    setToken(''); S.me = null; draw(); status('ok', 'Signed out.');
  }

  /* ---------- Word helpers ---------- */
  function noWord() { status('err', 'Open this add-in inside Microsoft Word to insert content.'); return Promise.reject(new Error('not in Word')); }
  function wordRun(fn) { if (!S.inWord || typeof Word === 'undefined') return noWord(); return Word.run(fn); }
  function insertDocx(b64, where) {
    return wordRun(function (ctx) {
      if (where === 'new') { ctx.application.createDocument(b64).open(); return ctx.sync(); }
      if (where === 'cursor') { ctx.document.getSelection().insertFileFromBase64(b64, 'Replace'); return ctx.sync(); }
      ctx.document.body.insertFileFromBase64(b64, 'End'); return ctx.sync();
    });
  }
  function selectionText() {
    return wordRun(function (ctx) { var r = ctx.document.getSelection(); r.load('text'); return ctx.sync().then(function () { return r.text || ''; }); });
  }
  function plain(t) { return String(t || '').replace(/\*\*([^*]+)\*\*/g, '$1').replace(/\*([^*]+)\*/g, '$1'); }
  function putText(text, where) {
    return wordRun(function (ctx) {
      var r = ctx.document.getSelection();
      if (where === 'after') r.insertText('\n' + plain(text), 'After'); else r.insertText(plain(text), 'Replace');
      return ctx.sync();
    });
  }
  function putPicture(dataUri, widthPt) {
    var b64 = String(dataUri).split(',')[1] || '';
    if (!S.inWord) return noWord();
    if (typeof Word !== 'undefined' && Office.context.requirements.isSetSupported('WordApi', '1.2')) {
      return Word.run(function (ctx) {
        var pic = ctx.document.getSelection().insertInlinePictureFromBase64(b64, 'Replace');
        if (widthPt) { pic.load('width,height'); return ctx.sync().then(function () { var k = widthPt / (pic.width || widthPt); pic.width = widthPt; pic.height = Math.round((pic.height || widthPt) * k); return ctx.sync(); }); }
        return ctx.sync();
      });
    }
    return new Promise(function (res, rej) { Office.context.document.setSelectedDataAsync(b64, { coercionType: Office.CoercionType.Image }, function (r) { r.status === 'succeeded' ? res() : rej(new Error('The picture could not be added.')); }); });
  }
  function toPng(src) {
    return new Promise(function (res, rej) {
      var i = new Image(); i.crossOrigin = 'anonymous';
      i.onload = function () { var k = Math.min(1, 1800 / Math.max(i.naturalWidth, i.naturalHeight)), c = document.createElement('canvas'); c.width = Math.round(i.naturalWidth * k); c.height = Math.round(i.naturalHeight * k); var x = c.getContext('2d'); x.fillStyle = '#fff'; x.fillRect(0, 0, c.width, c.height); x.drawImage(i, 0, 0, c.width, c.height); res(c.toDataURL('image/jpeg', 0.9)); };
      i.onerror = function () { rej(new Error('The picture could not be loaded.')); }; i.src = src;
    });
  }

  /* ---------- pictures (marketplace) ---------- */
  var used = {};
  function sharp(pid, url) { return api('POST', 'ds/v1/mkt-auto-image', { post_id: pid, url: url }).then(function (r) { var d = r.data || r.image || r.data_uri || ''; if (!/^data:image\//.test(d)) throw new Error('No picture came back.'); return d; }); }
  function findPhoto(q) {
    return api('GET', 'ds/v1/mkt-slide-images?s=' + encodeURIComponent(q || 'business') + '&cat=images&per_page=8&page=1').then(function (r) {
      var items = (r.items || []).filter(function (x) { return x.id && x.full; });
      if (S.me && S.me.plan === 'free') items = items.filter(function (x) { return x.tier === 'free'; }).concat(items.filter(function (x) { return x.tier !== 'free'; }));
      var it = items.filter(function (x) { return !used[x.id]; })[0] || items[0];
      if (!it) return null;
      used[it.id] = 1;
      return sharp(it.id, it.full).then(function (d) { return { pid: it.id, url: it.full, data: d }; });
    }).catch(function () { return null; });
  }
  function fillPictures(doc) {
    var jobs = [], dz = D.design(doc.tpl);
    if ((dz.cover === 'photo' || dz.layout === 'flyerPhoto') && doc.cover && !doc.cover.src) jobs.push(function () { return findPhoto(doc.cover.q || doc.title).then(function (x) { if (x) { doc.cover.src = x.data; doc.cover.pid = x.pid; doc.cover.url = x.url; } }); });
    (doc.blocks || []).forEach(function (b) { if (b.t === 'image' && !b.src) jobs.push(function () { return (b.pid ? sharp(b.pid, b.url).then(function (d) { return { data: d }; }, function () { return null; }) : findPhoto(b.q || doc.title)).then(function (x) { if (x) { b.src = x.data; if (x.pid) { b.pid = x.pid; b.url = x.url; } } }); }); });
    var k = 0; function w() { if (k >= jobs.length) return Promise.resolve(); return jobs[k++]().then(w); }
    return Promise.all([w(), w(), w()]);
  }

  /* ---------- layout ---------- */
  function draw() {
    root.innerHTML =
      '<header class="dsw-hd"><div class="dsw-logo"><span class="dsw-mk"></span>DesignStager</div>' +
      (S.me ? '<div class="dsw-acc"><span title="' + esc(S.me.name) + '">' + esc(S.me.name) + '</span><b>★ <span id="dsw-cred">' + (Math.round(S.me.credits * 100) / 100) + '</span></b></div>' : '<button class="dsw-btn pri sm" data-a="signin" type="button">Sign in</button>') + '</header>' +
      '<nav class="dsw-tabs"><button data-tab="doc" type="button">AI Document</button><button data-tab="write" type="button">Write</button><button data-tab="media" type="button">Media</button><button data-tab="tpl" type="button">Templates</button></nav>' +
      '<div class="dsw-status" id="dsw-status"></div><main id="dsw-main"></main>' +
      '<footer class="dsw-ft"><a href="' + esc(CFG.site) + 'privacy-policy/" target="_blank" rel="noopener">Privacy</a> · <a href="' + esc(CFG.site) + 'contact-us/" target="_blank" rel="noopener">Support</a>' +
      (S.me ? ' · <a href="#" data-a="signout">Sign out</a>' : '') + '</footer>';
    $$('.dsw-tabs button').forEach(function (b) { b.classList.toggle('on', b.dataset.tab === S.tab); });
    drawTab();
  }
  function drawTab() {
    var m = $('#dsw-main'); if (!m) return;
    m.innerHTML = '';
    if (S.tab === 'doc') tabDoc(m);
    if (S.tab === 'write') tabWrite(m);
    if (S.tab === 'media') tabMedia(m);
    if (S.tab === 'tpl') tabTemplates(m);
  }
  root.addEventListener('click', function (e) {
    var t = e.target.closest('[data-tab]'); if (t) { S.tab = t.dataset.tab; $$('.dsw-tabs button').forEach(function (b) { b.classList.toggle('on', b === t); }); status('', ''); drawTab(); return; }
    var a = e.target.closest('[data-a]'); if (!a) return;
    if (a.dataset.a === 'signin') { e.preventDefault(); signIn(); }
    if (a.dataset.a === 'signout') { e.preventDefault(); signOut(); }
  });

  function mini(box, doc, w) {
    box.innerHTML = ''; var inner = el('div'); box.appendChild(inner);
    var sz = D.size(doc); D.render(inner, doc, { edit: false, gap: 8 });
    inner.style.width = sz.w + 'px'; inner.style.transformOrigin = '0 0'; inner.style.transform = 'scale(' + ((w || box.clientWidth || 120) / sz.w) + ')';
  }
  function sample(type, tpl, theme) { var d = D.sample(type); d.tpl = tpl; d.theme = theme || ''; d.lang = S.lang; if (S.me) d.author = S.me.name; return d; }
  function designStrip(box, type, onPick) {
    box.innerHTML = '';
    D.designsFor(type).forEach(function (dz) {
      var b = el('button', 'dsw-dz' + (dz.id === S.tpl ? ' on' : '')); b.type = 'button'; b.title = dz.name + ' — ' + (dz.desc || '');
      var pv = el('div', 'pv'); b.appendChild(pv); b.appendChild(el('span', '', esc(dz.name)));
      box.appendChild(b);
      setTimeout(function () { mini(pv, sample(dz.types.indexOf(type) >= 0 ? type : dz.types[0], dz.id, S.theme), 86); }, 0);
      b.onclick = function () { S.tpl = dz.id; $$('.dsw-dz', box).forEach(function (x) { x.classList.toggle('on', x === b); }); savePrefs(); onPick && onPick(); };
    });
  }
  function themeSelect() {
    return '<select id="dsw-theme"><option value="">Design colours</option>' + D.THEMES.map(function (t) { return '<option value="' + t.id + '"' + (t.id === S.theme ? ' selected' : '') + '>' + esc(t.name) + '</option>'; }).join('') + '</select>';
  }
  function cost(pages) { var p = (S.me && S.me.pricing) || { base: 5, baseN: 5, extra: 1 }; return pages <= p.baseN ? p.base : Math.round(p.base + (pages - p.baseN) * p.extra); }

  /* ---------- tab: AI Document ---------- */
  function tabDoc(m) {
    if (S.result) return drawResult(m);
    var types = D.TYPES.filter(function (t) { return t.ai; });
    if (!types.some(function (t) { return t.id === S.type; })) S.type = 'report';
    m.innerHTML = '<p class="dsw-lead">Describe your document. DesignStager writes it and designs it for Word.</p>' +
      '<label>Document type</label><select id="dsw-type">' + types.map(function (t) { return '<option value="' + t.id + '"' + (t.id === S.type ? ' selected' : '') + '>' + esc(t.name) + '</option>'; }).join('') + '</select>' +
      '<label>What is it about?</label><textarea id="dsw-topic" maxlength="3000" placeholder="E.g. A proposal to set up solar panels for a school in Mysuru"></textarea>' +
      '<div class="dsw-2"><div id="dsw-pg"><label>Pages</label><select id="dsw-pages"></select></div><div><label>Language</label><select id="dsw-lang">' + LANGS.map(function (l) { return '<option' + (l === S.lang ? ' selected' : '') + '>' + l + '</option>'; }).join('') + '</select></div></div>' +
      '<div class="dsw-2"><div><label>Tone</label><select id="dsw-tone">' + TONES.map(function (l) { return '<option' + (l === S.tone ? ' selected' : '') + '>' + l + '</option>'; }).join('') + '</select></div><div><label>Colours</label>' + themeSelect() + '</div></div>' +
      '<label>Design</label><div class="dsw-strip" id="dsw-designs"></div>' +
      '<div class="dsw-cost" id="dsw-cost"></div><button class="dsw-btn pri full" id="dsw-gen" type="button">✨ Create document</button>' +
      '<details class="dsw-my" id="dsw-my"><summary>My documents</summary><div id="dsw-mylist" class="dsw-mylist"></div></details>';
    function pages() {
      var t = D.type(S.type), sel = $('#dsw-pages', m);
      $('#dsw-pg', m).style.display = t.fixed ? 'none' : '';
      if (!t.fixed) { var h = ''; for (var i = 2; i <= ((S.me && S.me.maxPages) || 20); i++) h += '<option' + (i === +S.pages ? ' selected' : '') + '>' + i + '</option>'; sel.innerHTML = h; }
      var n = t.fixed ? 1 : +S.pages;
      $('#dsw-cost', m).innerHTML = (t.fixed ? '1 document' : n + ' pages') + ' · <b>' + cost(n) + ' credits</b>' + (S.me ? ' (you have ' + Math.round(S.me.credits) + ')' : '');
    }
    function strip() { if (D.design(S.tpl).types.indexOf(S.type) < 0) S.tpl = D.designsFor(S.type)[0].id; designStrip($('#dsw-designs', m), S.type); }
    $('#dsw-type', m).onchange = function (e) { S.type = e.target.value; pages(); strip(); savePrefs(); };
    $('#dsw-pages', m).onchange = function (e) { S.pages = +e.target.value; pages(); savePrefs(); };
    $('#dsw-lang', m).onchange = function (e) { S.lang = e.target.value; savePrefs(); };
    $('#dsw-tone', m).onchange = function (e) { S.tone = e.target.value; savePrefs(); };
    $('#dsw-theme', m).onchange = function (e) { S.theme = e.target.value; strip(); savePrefs(); };
    $('#dsw-gen', m).onclick = generate;
    $('#dsw-my', m).addEventListener('toggle', function (e) { if (e.target.open) loadMyDocs(); });
    pages(); strip();
  }
  function generate() {
    if (needSignIn()) return;
    var topic = ($('#dsw-topic').value || '').trim();
    if (topic.length < 3) { status('err', 'Please describe what the document is about.'); return; }
    var t = D.type(S.type), n = t.fixed ? 1 : +S.pages, c = cost(n);
    if (S.me.credits < c) { status('err', 'You need ' + c + ' credits for this document. You have ' + Math.round(S.me.credits) + '.'); return; }
    var btn = $('#dsw-gen'); btn.disabled = true;
    var doc = { v: 1, type: S.type, tpl: S.tpl, theme: S.theme, size: 'A4', lang: S.lang, title: '', subtitle: '', kicker: t.name, author: S.me.name, org: '', date: new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }), cover: {}, blocks: [] };
    var job = null, refund = 0;
    status('busy', 'Planning your document…');
    var ex = t.fixed ? D.sample(S.type).blocks.map(function (b) { var x = D.clone(b); delete x.id; return x; }) : null;
    api('POST', 'ds/v1/doc-start', { type: S.type, topic: topic, pages: n, language: S.lang, tone: S.tone, mode: 'ai', example: ex }).then(function (r) {
      job = r.job; credits(r.credits_balance);
      if (r.fixed) { doc.title = r.doc.title || t.name; doc.subtitle = r.doc.subtitle || ''; doc.blocks = (r.doc.blocks || []).map(function (b) { b.id = D.uid(); return b; }); return; }
      var o = r.outline || {}, secs = o.sections || [], res = new Array(secs.length), done = 0, fail = 0, i = 0;
      doc.title = o.title || topic.slice(0, 80); doc.subtitle = o.subtitle || ''; doc.kicker = o.kicker || doc.kicker; doc.cover = { q: o.cover_q || '' };
      function one(k, again) {
        return api('POST', 'ds/v1/doc-section', { job: job, index: k }).then(function (x) { res[k] = (x.blocks || []).map(function (b) { b.id = D.uid(); return b; }); done++; }, function (e) { if (!again && e.status !== 404) return one(k, true); fail++; })
          .then(function () { status('busy', 'Writing section ' + Math.min(secs.length, done + fail) + ' of ' + secs.length + '…'); });
      }
      function lane() { if (i >= secs.length) return Promise.resolve(); var k = i++; return one(k).then(lane); }
      return Promise.all([lane(), lane()]).then(function () {
        if (!done) throw new Error('The AI could not write this document. Your credits were refunded.');
        res.forEach(function (x) { if (x) doc.blocks = doc.blocks.concat(x); });
      });
    }).then(function () {
      status('busy', 'Adding pictures…');
      return fillPictures(doc);
    }).then(function () {
      return job ? api('POST', 'ds/v1/doc-finish', { job: job }).then(function (r) { refund = +r.refund || 0; credits(r.credits_balance); }).catch(function () {}) : null;
    }).then(function () {
      return api('POST', 'ds/v1/save-doc', { doc: forSave(doc) }).then(function (r) { doc.__id = r.doc_id; }).catch(function () {});
    }).then(function () {
      S.result = doc; status('ok', refund ? 'Done. ' + refund + ' credits refunded for a part that could not be written.' : 'Your document is ready.'); drawTab();
    }).catch(function (e) {
      if (job) api('POST', 'ds/v1/doc-finish', { job: job }).then(function (r) { credits(r.credits_balance); }).catch(function () {});
      status('err', e.message);
    }).then(function () { var b = $('#dsw-gen'); if (b) b.disabled = false; });
  }
  function forSave(doc) { var d = D.clone(doc); delete d.__id; (d.blocks || []).forEach(function (b) { if (b.src && /^data:/.test(b.src)) delete b.src; }); if (d.cover) delete d.cover.src; return d; }
  function drawResult(m) {
    var doc = S.result;
    m.innerHTML = '<div class="dsw-res"><div class="pv" id="dsw-rpv"></div><h3>' + esc(doc.title || 'Document') + '</h3><p class="dsw-lead">' + esc(D.design(doc.tpl).name) + ' design</p>' +
      (S.wordApi13 ? '<button class="dsw-btn pri full" data-r="new" type="button">Open as a new document</button>' : '') +
      '<button class="dsw-btn ' + (S.wordApi13 ? '' : 'pri ') + 'full" data-r="end" type="button">Add to the end of this document</button><button class="dsw-btn full" data-r="cursor" type="button">Insert at the cursor</button>' +
      (doc.__id && S.me ? '<button class="dsw-btn full" data-r="web" type="button">Edit on DesignStager ↗</button>' : '') +
      '<button class="dsw-btn ghost full" data-r="back" type="button">← Create another</button>' +
      '<p class="dsw-note">“Open as a new document” keeps the full design: cover, page decoration and headers. Inserting into an existing document keeps the text, tables and pictures; the cover and page decoration may be left out by Word.</p></div>';
    setTimeout(function () { mini($('#dsw-rpv', m), doc, 140); }, 0);
    m.onclick = function (e) {
      var b = e.target.closest('[data-r]'); if (!b) return;
      var r = b.dataset.r;
      if (r === 'back') { S.result = null; drawTab(); return; }
      if (r === 'web') { var u = ((S.me && S.me.docUrl) || CFG.site) + (/\?/.test((S.me && S.me.docUrl) || '') ? '&' : '?') + 'doc=' + doc.__id; if (Office.context.ui && Office.context.ui.openBrowserWindow) Office.context.ui.openBrowserWindow(u); else window.open(u, '_blank'); return; }
      status('busy', 'Building the Word document…');
      D.toDocx(doc, { base64: true }).then(function (b64) { status('busy', 'Adding it to Word…'); return insertDocx(b64, r); })
        .then(function () { status('ok', r === 'new' ? 'Opened in a new document.' : 'Added to your document.'); })
        .catch(function (e) { if (e && e.message !== 'not in Word') status('err', (e && e.message) || 'Word could not add the document.'); });
    };
  }
  function loadMyDocs() {
    var box = $('#dsw-mylist'); if (!box) return;
    if (!S.me) { box.innerHTML = '<p class="dsw-note">Sign in to see your documents.</p>'; return; }
    box.innerHTML = '<span class="dsw-spin dark"></span>';
    api('GET', 'ds/v1/my-docs?limit=20').then(function (r) {
      var docs = r.docs || [];
      box.innerHTML = docs.length ? '' : '<p class="dsw-note">No saved documents yet.</p>';
      docs.forEach(function (d) {
        var b = el('button', 'dsw-myit', '<b>' + esc(d.title || 'Untitled') + '</b><span>' + esc(D.type(d.type).name) + ' · ' + new Date(d.updated * 1000).toLocaleDateString() + '</span>'); b.type = 'button';
        b.onclick = function () {
          status('busy', 'Opening…');
          api('POST', 'ds/v1/load-doc', { doc_id: d.id }).then(function (x) { var doc = x.doc || {}; doc.__id = d.id; (doc.blocks || []).forEach(function (bb) { if (!bb.id) bb.id = D.uid(); }); return fillPictures(doc).then(function () { S.result = doc; status('', ''); drawTab(); }); }).catch(function (e) { status('err', e.message); });
        };
        box.appendChild(b);
      });
    }).catch(function (e) { box.innerHTML = '<p class="dsw-note">' + esc(e.message) + '</p>'; });
  }

  /* ---------- tab: Write ---------- */
  var ACTS = [['improve', 'Improve writing'], ['rewrite', 'Rewrite'], ['shorten', 'Shorter'], ['expand', 'Longer'], ['grammar', 'Fix grammar'], ['summarize', 'Summarise'], ['bullets', 'Bullet points'], ['continue', 'Continue writing']];
  function tabWrite(m) {
    m.innerHTML = '<p class="dsw-lead">Select text in your document, then choose what to do with it.</p><div class="dsw-acts" id="dsw-acts">' + ACTS.map(function (a) { return '<button type="button" data-w="' + a[0] + '">' + a[1] + '</button>'; }).join('') + '</div>' +
      '<div class="dsw-2"><div><label>Change tone</label><select id="dsw-wtone"><option value="">Choose…</option>' + TONES.map(function (t) { return '<option>' + t + '</option>'; }).join('') + '</select></div>' +
      '<div><label>Translate to</label><select id="dsw-wlang"><option value="">Choose…</option>' + LANGS.map(function (t) { return '<option>' + t + '</option>'; }).join('') + '</select></div></div>' +
      '<label>Or tell the AI what to do</label><textarea id="dsw-ask" placeholder="E.g. Make this more persuasive"></textarea><button class="dsw-btn full" data-w="custom" type="button">✨ Apply to selection</button>' +
      '<label>Write something new at the cursor</label><textarea id="dsw-new" placeholder="E.g. A friendly paragraph thanking our customers"></textarea><button class="dsw-btn full" data-w="write" type="button">✨ Write</button>' +
      '<div class="dsw-out" id="dsw-out" hidden><label>Result</label><div class="txt" id="dsw-otext"></div><div class="dsw-2"><button class="dsw-btn pri" data-o="replace" type="button">Replace selection</button><button class="dsw-btn" data-o="after" type="button">Insert below</button></div></div>' +
      '<p class="dsw-note">Each action uses ' + ((S.me && S.me.assistCost) || 1) + ' credit.</p>';
    var last = '';
    function run(action, extra) {
      if (needSignIn()) return;
      var p = action === 'write' ? Promise.resolve('') : selectionText();
      p.then(function (text) {
        if (action !== 'write' && !String(text).trim()) { status('err', 'Select some text in your document first.'); return; }
        status('busy', 'Writing…');
        return api('POST', 'ds/v1/doc-assist', Object.assign({ action: action, text: text, language: S.lang }, extra || {})).then(function (r) {
          credits(r.credits_balance); last = r.text || ''; status('ok', 'Done. Check the result below.');
          $('#dsw-out', m).hidden = false; $('#dsw-otext', m).textContent = plain(last);
          if (action === 'write' || action === 'continue') $('#dsw-out [data-o="replace"]', m).textContent = action === 'write' ? 'Insert at cursor' : 'Replace selection';
          $('#dsw-out', m).scrollIntoView({ behavior: 'smooth' });
        });
      }).catch(function (e) { if (e && e.message !== 'not in Word') status('err', e.message); });
    }
    m.onclick = function (e) {
      var w = e.target.closest('[data-w]');
      if (w) {
        var a = w.dataset.w;
        if (a === 'custom') { var q = $('#dsw-ask', m).value.trim(); if (!q) { status('err', 'Tell the AI what to do.'); return; } run('custom', { instruction: q }); return; }
        if (a === 'write') { var q2 = $('#dsw-new', m).value.trim(); if (!q2) { status('err', 'Describe what to write.'); return; } run('write', { instruction: q2 }); return; }
        run(a); return;
      }
      var o = e.target.closest('[data-o]'); if (!o || !last) return;
      putText(last, o.dataset.o === 'after' ? 'after' : 'replace').then(function () { status('ok', 'Added to your document.'); }).catch(function () {});
    };
    $('#dsw-wtone', m).onchange = function (e) { if (e.target.value) run('tone', { tone: e.target.value }); e.target.value = ''; };
    $('#dsw-wlang', m).onchange = function (e) { if (e.target.value) run('translate', { language: e.target.value }); e.target.value = ''; };
  }

  /* ---------- tab: Media ---------- */
  function tabMedia(m) {
    m.innerHTML = '<div class="dsw-sub"><button type="button" data-mt="img" class="on">Images</button><button type="button" data-mt="ai">AI image</button><button type="button" data-mt="icon">Icons</button></div><div id="dsw-mbody"></div>';
    var mode = 'img';
    m.querySelector('.dsw-sub').onclick = function (e) { var b = e.target.closest('[data-mt]'); if (!b) return; mode = b.dataset.mt; $$('[data-mt]', m).forEach(function (x) { x.classList.toggle('on', x === b); }); body(); };
    function body() {
      var bx = $('#dsw-mbody', m);
      if (mode === 'img') {
        bx.innerHTML = '<div class="dsw-search"><input id="dsw-q" placeholder="Search photos, e.g. teamwork"><button class="dsw-btn pri sm" id="dsw-go" type="button">Go</button></div><div class="dsw-grid" id="dsw-res"></div>';
        var go = function () {
          if (needSignIn()) return;
          var res = $('#dsw-res', m); res.innerHTML = '<span class="dsw-spin dark"></span>';
          api('GET', 'ds/v1/mkt-slide-images?s=' + encodeURIComponent($('#dsw-q', m).value.trim() || 'business') + '&cat=images&per_page=24&page=1').then(function (r) {
            res.innerHTML = '';
            (r.items || []).filter(function (x) { return x.thumb || x.full; }).forEach(function (it) {
              var b = el('button', '', '<img loading="lazy" alt="" src="' + esc(it.thumb || it.full) + '">' + (it.tier && it.tier !== 'free' ? '<i>PRO</i>' : '')); b.type = 'button';
              b.onclick = function () {
                if (it.tier && it.tier !== 'free' && S.me && S.me.plan === 'free') { status('err', 'This picture needs a paid DesignStager plan.'); return; }
                status('busy', 'Adding the picture…');
                sharp(it.id, it.full).then(function (d) { return putPicture(d, 420); }).then(function () { status('ok', 'Picture added.'); }).catch(function (e) { if (e && e.message !== 'not in Word') status('err', e.message); });
              };
              res.appendChild(b);
            });
            if (!res.children.length) res.innerHTML = '<p class="dsw-note">No pictures found.</p>';
          }).catch(function (e) { res.innerHTML = '<p class="dsw-note">' + esc(e.message) + '</p>'; });
        };
        $('#dsw-go', m).onclick = go; $('#dsw-q', m).onkeydown = function (e) { if (e.key === 'Enter') go(); };
        if (S.me) go();
      } else if (mode === 'ai') {
        bx.innerHTML = '<label>Describe the picture</label><textarea id="dsw-ap" placeholder="E.g. A bright modern classroom with students and a teacher"></textarea><div class="dsw-2"><select id="dsw-ar"><option value="16:9">Wide 16:9</option><option value="4:3">4:3</option><option value="1:1">Square</option></select><button class="dsw-btn pri" id="dsw-ag" type="button">✨ Create</button></div><div class="dsw-grid" id="dsw-ares"></div><p class="dsw-note">Uses your credits, like AI Images on the website.</p>';
        $('#dsw-ag', m).onclick = function () {
          if (needSignIn()) return;
          var p = $('#dsw-ap', m).value.trim(); if (!p) { status('err', 'Describe the picture first.'); return; }
          var req = { prompt: p, ratio: $('#dsw-ar', m).value, count: 1 }, out = $('#dsw-ares', m);
          status('busy', 'Creating your picture…'); out.innerHTML = '';
          api('POST', 'ds/v1/generate-image', req).then(function (r) {
            if (r.credits_left != null) credits(r.credits_left);
            var urls = [].concat(r.images || []).map(function (x) { return typeof x === 'string' ? x : x && x.url; }).filter(Boolean);
            if (urls.length) return urls;
            var pid = r.prediction_id || (r.prediction_ids && r.prediction_ids[0]); if (!pid) throw new Error('No picture came back. Please try again.');
            var n = 0;
            return new Promise(function (res, rej) { (function poll() { api('POST', 'ds/v1/poll-image', Object.assign({ prediction_id: pid }, req)).then(function (x) { if (x.status === 'succeeded') return res([].concat(x.images || []).map(function (y) { return typeof y === 'string' ? y : y && y.url; }).filter(Boolean)); if (x.status === 'failed' || x.status === 'error' || ++n > 60) return rej(new Error(x.error || 'The picture could not be created.')); setTimeout(poll, 2500); }).catch(rej); })(); });
          }).then(function (urls) {
            return Promise.all(urls.map(function (u) { return api('POST', 'dsw/v1/file-link', { url: u }).then(function (x) { return x.url; }); }));
          }).then(function (links) {
            status('ok', 'Click the picture to add it.');
            links.forEach(function (u) { var b = el('button', '', '<img alt="" src="' + esc(u) + '">'); b.type = 'button'; b.onclick = function () { status('busy', 'Adding the picture…'); toPng(u).then(function (d) { return putPicture(d, 420); }).then(function () { status('ok', 'Picture added.'); }).catch(function (e) { if (e && e.message !== 'not in Word') status('err', e.message); }); }; out.appendChild(b); });
          }).catch(function (e) { status('err', e.message); });
        };
      } else {
        bx.innerHTML = '<div class="dsw-search"><input id="dsw-iq" placeholder="Search icons, e.g. growth"><input type="color" id="dsw-ic" value="#2F6FED"><button class="dsw-btn pri sm" id="dsw-igo" type="button">Go</button></div><div class="dsw-grid icons" id="dsw-ires"></div><p class="dsw-note">Free open-source icons from Iconify.</p>';
        var goI = function () {
          var q = $('#dsw-iq', m).value.trim() || 'business', res = $('#dsw-ires', m);
          res.innerHTML = '<span class="dsw-spin dark"></span>';
          fetch('https://api.iconify.design/search?limit=64&query=' + encodeURIComponent(q)).then(function (r) { return r.json(); }).then(function (j) {
            var names = j.icons || [], groups = {};
            res.innerHTML = names.length ? '' : '<p class="dsw-note">No icons found.</p>';
            names.forEach(function (n) { var p = n.split(':'); (groups[p[0]] = groups[p[0]] || []).push(p[1]); });
            Object.keys(groups).forEach(function (pfx) {
              fetch('https://api.iconify.design/' + pfx + '.json?icons=' + groups[pfx].join(',')).then(function (r) { return r.json(); }).then(function (d) {
                groups[pfx].forEach(function (nm) {
                  var ic = d.icons && d.icons[nm]; if (!ic) return;
                  var w = ic.width || d.width || 24, h = ic.height || d.height || 24;
                  var svg = function (c) { return '<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256" viewBox="0 0 ' + w + ' ' + h + '">' + String(ic.body).replace(/currentColor/g, c) + '</svg>'; };
                  var b = el('button', '', '<img alt="" src="data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg('#1c2333')) + '">'); b.type = 'button'; b.title = nm.replace(/-/g, ' ');
                  b.onclick = function () {
                    var c = document.createElement('canvas'); c.width = 256; c.height = 256; var im = new Image();
                    im.onload = function () { c.getContext('2d').drawImage(im, 0, 0, 256, 256); putPicture(c.toDataURL('image/png'), 48).then(function () { status('ok', 'Icon added.'); }).catch(function () {}); };
                    im.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg($('#dsw-ic', m).value));
                  };
                  res.appendChild(b);
                });
              }).catch(function () {});
            });
          }).catch(function () { res.innerHTML = '<p class="dsw-note">Icons could not be loaded.</p>'; });
        };
        $('#dsw-igo', m).onclick = goI; $('#dsw-iq', m).onkeydown = function (e) { if (e.key === 'Enter') goI(); };
        goI();
      }
    }
    body();
  }

  /* ---------- tab: Templates (free, no sign-in needed) ---------- */
  function tabTemplates(m) {
    var groups = []; D.TYPES.forEach(function (t) { if (groups.indexOf(t.group) < 0) groups.push(t.group); });
    var g = S.tplGroup || groups[0];
    m.innerHTML = '<p class="dsw-lead">Designed templates for Word. Free to use — edit the text after adding.</p><div class="dsw-chips" id="dsw-chips">' + groups.map(function (x) { return '<button type="button" data-g="' + esc(x) + '"' + (x === g ? ' class="on"' : '') + '>' + esc(x) + '</button>'; }).join('') + '</div>' +
      '<label>Colours</label>' + themeSelect() + '<div class="dsw-tgrid" id="dsw-tgrid"></div>';
    function grid() {
      var box = $('#dsw-tgrid', m); box.innerHTML = '';
      D.DESIGNS.filter(function (d) { return D.type(d.types[0]).group === g; }).forEach(function (dz) {
        var c = el('div', 'dsw-tc'), pv = el('div', 'pv'); c.appendChild(pv);
        c.appendChild(el('b', '', esc(dz.name))); c.appendChild(el('span', '', esc(D.type(dz.types[0]).name)));
        var bar = el('div', 'acts'); bar.innerHTML = (S.wordApi13 ? '<button type="button" data-t="new">Open</button>' : '') + '<button type="button" data-t="end">Insert</button>';
        c.appendChild(bar); box.appendChild(c);
        setTimeout(function () { mini(pv, sample(dz.types[0], dz.id, S.theme), 130); }, 0);
        bar.onclick = function (e) {
          var b = e.target.closest('[data-t]'); if (!b) return;
          var doc = sample(dz.types[0], dz.id, S.theme);
          status('busy', 'Building the template…');
          D.toDocx(doc, { base64: true }).then(function (b64) { return insertDocx(b64, b.dataset.t); }).then(function () { status('ok', b.dataset.t === 'new' ? 'Opened in a new document.' : 'Template added.'); }).catch(function (er) { if (er && er.message !== 'not in Word') status('err', er.message); });
        };
      });
    }
    $('#dsw-chips', m).onclick = function (e) { var b = e.target.closest('[data-g]'); if (!b) return; g = S.tplGroup = b.dataset.g; $$('[data-g]', m).forEach(function (x) { x.classList.toggle('on', x === b); }); grid(); };
    $('#dsw-theme', m).onchange = function (e) { S.theme = e.target.value; savePrefs(); grid(); };
    grid();
  }

  /* ---------- start ---------- */
  function boot() {
    if (!S.token) { S.me = null; draw(); return; }
    api('GET', 'dsw/v1/me').then(function (me) { S.me = me; draw(); }).catch(function () { draw(); });
  }
  function ready(info) {
    S.inWord = !!(info && info.host);
    try { S.wordApi13 = S.inWord && Office.context.requirements.isSetSupported('WordApi', '1.3'); } catch (e) { S.wordApi13 = false; }
    boot();
  }
  if (window.Office && Office.onReady) Office.onReady(ready); else ready(null);
})();

DSJS;
	}
	private static function css() {
		return <<<'DSCSS'
html,body{margin:0;background:#F6F7FB;color:#1C2333;font:13px/1.45 "Segoe UI",system-ui,-apple-system,sans-serif}
*{box-sizing:border-box}
.dsw-load{padding:40px 16px;text-align:center;color:#5B6475}
.dsw-hd{display:flex;align-items:center;gap:8px;padding:10px 12px;background:#0E1628;color:#fff;position:sticky;top:0;z-index:5}
.dsw-logo{display:flex;align-items:center;gap:7px;font-weight:700;font-size:14px;flex:1}
.dsw-mk{width:18px;height:22px;border-radius:3px;background:linear-gradient(135deg,#2F6FED,#7C4DFF);position:relative}
.dsw-mk::after{content:"";position:absolute;right:-3px;bottom:-3px;width:9px;height:9px;background:#F0532B;border-radius:2px}
.dsw-acc{display:flex;flex-direction:column;align-items:flex-end;max-width:55%;font-size:11.5px;line-height:1.2}
.dsw-acc span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;opacity:.85}
.dsw-acc b{color:#FFC163}
.dsw-tabs{display:flex;background:#fff;border-bottom:1px solid #E3E6EE;position:sticky;top:44px;z-index:4}
.dsw-tabs button{flex:1;border:0;background:none;padding:10px 2px;font:600 12px "Segoe UI",sans-serif;color:#5B6475;border-bottom:2px solid transparent;cursor:pointer}
.dsw-tabs button.on{color:#2F6FED;border-bottom-color:#2F6FED}
main{padding:12px}
.dsw-lead{margin:0 0 8px;color:#5B6475}
label{display:block;font-size:11px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:#5B6475;margin:10px 0 4px}
select,textarea,input[type=text],input:not([type]){width:100%;border:1px solid #D5DAE5;border-radius:7px;padding:7px 8px;font:13px "Segoe UI",sans-serif;background:#fff;color:#1C2333}
textarea{min-height:76px;resize:vertical}
select:focus,textarea:focus,input:focus{outline:2px solid #2F6FED;outline-offset:-1px}
.dsw-2{display:grid;grid-template-columns:1fr 1fr;gap:8px;align-items:end}
.dsw-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;border:1px solid #D5DAE5;background:#fff;color:#1C2333;border-radius:8px;padding:8px 12px;font:600 12.5px "Segoe UI",sans-serif;cursor:pointer;margin-top:8px}
.dsw-btn:hover{border-color:#2F6FED}
.dsw-btn.pri{background:#2F6FED;border-color:#2F6FED;color:#fff}
.dsw-btn.ghost{border-color:transparent;background:none;color:#2F6FED}
.dsw-btn.full{width:100%}
.dsw-btn.sm{padding:5px 10px;margin:0}
.dsw-btn[disabled]{opacity:.6;cursor:default}
.dsw-status{display:none;align-items:center;gap:8px;margin:10px 12px 0;padding:8px 10px;border-radius:8px;background:#EEF3FE;color:#1E3A8A;font-size:12.5px}
.dsw-status.err{background:#FDECEC;color:#8A1C1C}.dsw-status.ok{background:#E9F7EF;color:#14532D}.dsw-status.busy{background:#EEF3FE;color:#1E3A8A}
.dsw-spin{display:inline-block;width:14px;height:14px;border:2px solid rgba(30,58,138,.25);border-top-color:#1E3A8A;border-radius:50%;animation:dsws .8s linear infinite;flex:none}
.dsw-spin.dark{margin:16px auto;display:block}
@keyframes dsws{to{transform:rotate(360deg)}}
.dsw-strip{display:flex;gap:7px;overflow-x:auto;padding-bottom:6px}
.dsw-dz{flex:none;width:96px;border:1px solid #D5DAE5;background:#fff;border-radius:8px;padding:4px;cursor:pointer;text-align:left}
.dsw-dz .pv{height:120px;overflow:hidden;position:relative;border-radius:4px;background:#fff}
.dsw-dz .pv>div{position:absolute;left:0;top:0}
.dsw-dz span{display:block;font-size:11px;font-weight:600;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dsw-dz.on{border-color:#2F6FED;box-shadow:0 0 0 1px #2F6FED}
.dsw-cost{margin-top:10px;font-size:12px;color:#5B6475}.dsw-cost b{color:#B45309}
.dsw-my{margin-top:14px;border-top:1px solid #E3E6EE;padding-top:8px}
.dsw-my summary{cursor:pointer;font-weight:600}
.dsw-mylist{display:flex;flex-direction:column;gap:6px;margin-top:8px}
.dsw-myit{display:flex;flex-direction:column;align-items:flex-start;border:1px solid #E3E6EE;background:#fff;border-radius:8px;padding:8px 10px;cursor:pointer;text-align:left;font:13px "Segoe UI",sans-serif}
.dsw-myit span{font-size:11px;color:#5B6475}
.dsw-res .pv{height:220px;overflow:hidden;position:relative;border-radius:6px;background:#E9ECF3;margin-bottom:8px}
.dsw-res .pv>div{position:absolute;left:50%;top:6px;margin-left:-70px}
.dsw-res h3{margin:4px 0 0;font-size:15px}
.dsw-note{font-size:11.5px;color:#6B7280;margin:10px 0 0}
.dsw-acts{display:flex;flex-wrap:wrap;gap:6px}
.dsw-acts button,.dsw-sub button,.dsw-chips button{border:1px solid #D5DAE5;background:#fff;border-radius:16px;padding:5px 10px;font:12px "Segoe UI",sans-serif;cursor:pointer;color:#1C2333}
.dsw-acts button:hover{border-color:#2F6FED;color:#2F6FED}
.dsw-sub,.dsw-chips{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px}
.dsw-sub button.on,.dsw-chips button.on{background:#2F6FED;border-color:#2F6FED;color:#fff}
.dsw-out{margin-top:12px;background:#fff;border:1px solid #E3E6EE;border-radius:8px;padding:8px 10px}
.dsw-out .txt{white-space:pre-wrap;max-height:220px;overflow:auto;font-size:12.5px}
.dsw-search{display:flex;gap:6px;align-items:center}
.dsw-search input[type=color]{width:34px;height:32px;padding:0;border:1px solid #D5DAE5;border-radius:6px;flex:none}
.dsw-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:8px}
.dsw-grid button{border:1px solid #E3E6EE;background:#fff;border-radius:7px;padding:0;height:86px;overflow:hidden;position:relative;cursor:pointer}
.dsw-grid img{width:100%;height:100%;object-fit:cover;display:block}
.dsw-grid i{position:absolute;right:4px;top:4px;background:#F59E0B;color:#111;font:700 9px sans-serif;padding:1px 5px;border-radius:5px;font-style:normal}
.dsw-grid.icons{grid-template-columns:repeat(5,1fr)}.dsw-grid.icons button{height:48px;padding:9px}.dsw-grid.icons img{object-fit:contain}
.dsw-tgrid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px}
.dsw-tc{background:#fff;border:1px solid #E3E6EE;border-radius:8px;padding:5px}
.dsw-tc .pv{height:150px;overflow:hidden;position:relative;border-radius:4px}
.dsw-tc .pv>div{position:absolute;left:0;top:0}
.dsw-tc b{display:block;font-size:12px;margin-top:4px}.dsw-tc span{display:block;font-size:10.5px;color:#6B7280}
.dsw-tc .acts{display:flex;gap:4px;margin-top:5px}
.dsw-tc .acts button{flex:1;border:1px solid #D5DAE5;background:#fff;border-radius:6px;padding:4px;font:600 11.5px "Segoe UI",sans-serif;cursor:pointer}
.dsw-tc .acts button:first-child{background:#2F6FED;border-color:#2F6FED;color:#fff}
.dsw-ft{padding:12px;text-align:center;font-size:11.5px;color:#6B7280}
.dsw-ft a{color:#2F6FED;text-decoration:none}

DSCSS;
	}

	/* ---------------- settings ---------------- */

	public static function admin_menu() {
		add_options_page( 'DesignStager Word', 'DesignStager Word', 'manage_options', 'ds-word-addin', array( __CLASS__, 'settings_page' ) );
	}
	public static function settings_page() {
		$s = self::settings();
		if ( isset( $_POST['dsw_save'] ) && check_admin_referer( 'dsw_settings' ) ) {
			$s['enabled'] = empty( $_POST['enabled'] ) ? 0 : 1;
			$ver          = isset( $_POST['version'] ) ? sanitize_text_field( wp_unslash( $_POST['version'] ) ) : '1.0.0.0';
			$s['version'] = preg_match( '/^\d+\.\d+\.\d+\.\d+$/', $ver ) ? $ver : $s['version'];
			update_option( self::OPT, $s );
			echo '<div class="notice notice-success"><p>Saved.</p></div>';
		}
		echo '<div class="wrap"><h1>DesignStager for Word</h1><form method="post">';
		wp_nonce_field( 'dsw_settings' );
		echo '<table class="form-table"><tr><th>Enabled</th><td><label><input type="checkbox" name="enabled" value="1" ' . checked( $s['enabled'], 1, false ) . '> The Word add-in can be used</label></td></tr>';
		echo '<tr><th>Add-in ID</th><td><code>' . esc_html( $s['id'] ) . '</code><p class="description">Keep this the same for every update you submit to Microsoft.</p></td></tr>';
		echo '<tr><th>Manifest version</th><td><input name="version" value="' . esc_attr( $s['version'] ) . '" class="regular-text"><p class="description">Raise it (for example 1.0.1.0) each time you upload a new manifest to Microsoft.</p></td></tr>';
		echo '<tr><th>Manifest</th><td><a class="button button-primary" href="' . esc_url( self::url( 'manifest' ) ) . '">Download manifest.xml</a><p class="description">Upload it in Word to test (Insert → Add-ins → Upload My Add-in), then submit the same file in Microsoft Partner Center.</p></td></tr>';
		echo '<tr><th>Task pane</th><td><a href="' . esc_url( self::url( 'taskpane' ) ) . '" target="_blank">' . esc_html( self::url( 'taskpane' ) ) . '</a></td></tr>';
		echo '</table><p><button class="button" name="dsw_save" value="1">Save changes</button></p></form></div>';
	}
}
DS_Word_Addin::init();
endif;
