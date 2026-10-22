<?php
/** Handles the WeCom OAuth callback through admin-post.php. */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Login_Integration_For_Enterprise_Wechat_Callback {
	private static function get_token() {
		$options = get_option( 'wp_login_enterprise_wechat_option', array() );
		$corp_id = $options['corpid'] ?? '';
		$secret  = $options['corpsecret'] ?? '';
		if ( ! $corp_id || ! $secret ) {
			return false;
		}

		$key   = 'wecom_token_' . md5( $corp_id . ':' . $secret );
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

	public static function handle() {
		$nonce = isset( $_GET['_wpnonce'] ) && is_string( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		$code  = isset( $_GET['code'] ) && is_string( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wecom_login' ) || '' === $code ) {
			self::redirect_to_login();
		}

		$info     = self::get_user_info( $code );
		$username = isset( $info->userid ) && is_string( $info->userid ) ? sanitize_user( $info->userid, true ) : '';
		if ( '' === $username ) {
			self::redirect_to_login();
		}

		$user = get_user_by( 'login', $username );
		if ( ! $user ) {
			$options = get_option( 'wp_login_enterprise_wechat_option', array() );
			if ( empty( $options['auto_register'] ) || 'on' !== $options['auto_register'] ) {
				self::redirect_to_login();
			}
			$userdata = array( 'user_login' => $username, 'user_pass' => wp_generate_password( 32, true, true ) );
			if ( ! empty( $options['auto_register_role'] ) && get_role( $options['auto_register_role'] ) ) {
				$userdata['role'] = $options['auto_register_role'];
			}
			$user_id = wp_insert_user( $userdata );
			if ( is_wp_error( $user_id ) ) {
				self::redirect_to_login();
			}
			$user = get_user_by( 'id', $user_id );
		}

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );
		wp_safe_redirect( admin_url() );
		exit;
	}

	private static function redirect_to_login() {
		wp_safe_redirect( add_query_arg( 'loggedout', 'true', wp_login_url() ) );
		exit;
	}
}
