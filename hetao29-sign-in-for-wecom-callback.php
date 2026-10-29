<?php
/** Handles the WeCom OAuth callback through admin-post.php. */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hetao29_Wecom_Login_Callback {
	private static function get_token() {
		$options = get_option( 'hetao29_wecom_options', array() );
		if ( empty( $options['corpid'] ) || empty( $options['agentid'] ) || empty( $options['corpsecret'] ) ) {
			self::redirect_to_login();
		}
		$corp_id = $options['corpid'] ?? '';
		$secret  = $options['corpsecret'] ?? '';
		if ( ! $corp_id || ! $secret ) {
			return false;
		}

		$key   = 'hetao29_wecom_token_' . md5( $corp_id . ':' . $secret );
		$token = get_transient( $key );
		if ( false !== $token ) {
			return $token;
		}

		$response = wp_remote_get( add_query_arg( array( 'corpid' => $corp_id, 'corpsecret' => $secret ), 'https://qyapi.weixin.qq.com/cgi-bin/gettoken' ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$data = json_decode( wp_remote_retrieve_body( $response ) );
		if ( empty( $data->access_token ) ) {
			return false;
		}
		set_transient( $key, $data->access_token, HOUR_IN_SECONDS );
		return $data->access_token;
	}

	private static function get_user_info( $code ) {
		$token = self::get_token();
		if ( ! $token ) {
			return false;
		}
		$response = wp_remote_get( add_query_arg( array( 'access_token' => $token, 'code' => $code ), 'https://qyapi.weixin.qq.com/cgi-bin/auth/getuserinfo' ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	private static function valid_oauth_state( $state, $cookie_state ) {
		if ( ! preg_match( '/^[a-f0-9]{64}$/', $state ) ) {
			return false;
		}
		$parts = explode( '.', $cookie_state );
		if ( 3 !== count( $parts ) || ! hash_equals( $parts[0], $state ) ||
			! ctype_digit( $parts[1] ) || ! preg_match( '/^[a-f0-9]{64}$/', $parts[2] ) ) {
			return false;
		}
		$issued_at = (int) $parts[1];
		$now = time();
		if ( $issued_at > $now || $issued_at + 600 < $now ) {
			return false;
		}
		return hash_equals( hash_hmac( 'sha256', $parts[0] . '.' . $parts[1], wp_salt( 'auth' ) ), $parts[2] );
	}

	public static function handle() {
		$state = isset( $_GET['state'] ) && is_string( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
		$cookie_state = isset( $_COOKIE['hetao29_wecom_state'] ) && is_string( $_COOKIE['hetao29_wecom_state'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['hetao29_wecom_state'] ) ) : '';
		$code  = isset( $_GET['code'] ) && is_string( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
		if ( '' === $code || ! self::valid_oauth_state( $state, $cookie_state ) ) {
			self::redirect_to_login();
		}
		setcookie( 'hetao29_wecom_state', '', array(
			'expires' => time() - 3600,
			'path' => wp_parse_url( site_url( '/' ), PHP_URL_PATH ) ?: '/',
			'secure' => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		) );

		$info = self::get_user_info( $code );
		$userid = isset( $info->userid ) && is_string( $info->userid ) ? $info->userid : '';
		if ( '' === $userid || strlen( $userid ) > 60 || sanitize_user( $userid, false ) !== $userid ) {
			self::redirect_to_login();
		}

		$options = get_option( 'hetao29_wecom_options', array() );
		$user = get_user_by( 'login', $userid );
		if ( $user && ! hash_equals( $user->user_login, $userid ) ) {
			self::redirect_to_login();
		}
		if ( ! $user ) {
			if ( empty( $options['auto_register'] ) || 'on' !== $options['auto_register'] ) {
				self::redirect_to_login();
			}
			$userdata = array(
				'user_login' => $userid,
				'user_pass' => wp_generate_password( 32, true, true ),
					'role' => ! empty( $options['auto_register_role'] ) && is_string( $options['auto_register_role'] ) && get_role( $options['auto_register_role'] ) ? $options['auto_register_role'] : 'subscriber',
			);
			$user_id = wp_insert_user( $userdata );
			if ( is_wp_error( $user_id ) ) {
				self::redirect_to_login();
			}
			$user = get_user_by( 'id', $user_id );
		}

		$authenticated = apply_filters( 'wp_authenticate_user', $user, '' );
		if ( is_wp_error( $authenticated ) || ! $authenticated instanceof WP_User ) {
			self::redirect_to_login();
		}
		$user = $authenticated;
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );
		do_action( 'wp_login', $user->user_login, $user );
		wp_safe_redirect( admin_url() );
		exit;
	}

	private static function redirect_to_login() {
		wp_safe_redirect( add_query_arg( 'loggedout', 'true', wp_login_url() ) );
		exit;
	}
}
