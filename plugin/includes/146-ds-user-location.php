<?php
/**
 * Code Snippets export — DS User Location
 *
 * Snippet ID : 855
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-146
 * Modified   : 2026-09-18 05:17:26
 * Load order : 146 of 147
 */
/* ---------- 1. Capture IP ---------- */
function ds_get_client_ip() {
	foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = trim( explode( ',', $_SERVER[ $key ] )[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}
	return '';
}

add_action( 'wp_login', function ( $login, $user ) {
	update_user_meta( $user->ID, 'ds_last_ip', ds_get_client_ip() );
	update_user_meta( $user->ID, 'ds_last_login', current_time( 'mysql' ) );
}, 10, 2 );

add_action( 'user_register', function ( $user_id ) {
	$ip = ds_get_client_ip();
	update_user_meta( $user_id, 'ds_signup_ip', $ip );
	update_user_meta( $user_id, 'ds_last_ip', $ip );
} );

/* ---------- 2. Geolocate (lazy + cached, so logins stay fast) ---------- */
function ds_lookup_ip_location( $ip ) {
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
		return array( 'ip' => $ip, 'label' => 'Local / private IP' );
	}

	$res = wp_remote_get( 'https://ipapi.co/' . rawurlencode( $ip ) . '/json/', array( 'timeout' => 5 ) );

	// Don't cache failures - let it retry on the next page load.
	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
		return false;
	}

	$data = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $data ) || ! empty( $data['error'] ) ) {
		return false;
	}

	$parts = array_filter( array(
		! empty( $data['city'] )         ? $data['city']         : '',
		! empty( $data['region'] )       ? $data['region']       : '',
		! empty( $data['country_name'] ) ? $data['country_name'] : '',
	) );

	return array(
		'ip'      => $ip,
		'label'   => $parts ? implode( ', ', $parts ) : 'Unknown',
		'country' => ! empty( $data['country_code'] ) ? $data['country_code'] : '',
	);
}

/* ---------- 3. The Location column on users.php ---------- */
add_filter( 'manage_users_columns', function ( $cols ) {
	$cols['ds_location'] = 'Location';
	return $cols;
} );

add_filter( 'manage_users_custom_column', function ( $out, $col, $user_id ) {
	if ( 'ds_location' !== $col ) {
		return $out;
	}

	$ip = get_user_meta( $user_id, 'ds_last_ip', true );
	if ( ! $ip ) {
		return '<span style="color:#999;">&mdash; not recorded yet</span>';
	}

	$cache = get_user_meta( $user_id, 'ds_location_cache', true );

	// Re-lookup only when the IP changed.
	if ( ! is_array( $cache ) || empty( $cache['ip'] ) || $cache['ip'] !== $ip ) {
		$fresh = ds_lookup_ip_location( $ip );
		if ( $fresh ) {
			update_user_meta( $user_id, 'ds_location_cache', $fresh );
			$cache = $fresh;
		} else {
			$cache = array( 'label' => 'Lookup unavailable' );
		}
	}

	$html  = esc_html( $cache['label'] );
	$html .= '<br><span style="color:#888;font-size:11px;">' . esc_html( $ip ) . '</span>';

	$last = get_user_meta( $user_id, 'ds_last_login', true );
	if ( $last ) {
		$html .= '<br><span style="color:#888;font-size:11px;">last login: '
			. esc_html( date_i18n( 'd M Y', strtotime( $last ) ) ) . '</span>';
	}

	return $html;
}, 10, 3 );