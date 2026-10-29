<?php
/**
 * Plugin Name:       Hetao29 Sign-In for WeCom
 * Description:       Enterprise WeChat (WeCom) login with QR code and in-app authorization.
 * Version:           1.2.1
 * Requires PHP:      7.4
 * Requires at least: 5.0
 * Author:            hetao29
 * Author URI:        https://github.com/hetao29/
 * License:           GNU General Public License v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hetao29-sign-in-for-wecom
 * Domain Path:       /languages
 *
 * @package           Hetao29SignInForWeCom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/hetao29-sign-in-for-wecom-callback.php';
add_action( 'admin_post_nopriv_hetao29_wecom_login_callback', array( 'Hetao29_Wecom_Login_Callback', 'handle' ) );
add_action( 'admin_post_hetao29_wecom_login_callback', array( 'Hetao29_Wecom_Login_Callback', 'handle' ) );
if ( !class_exists( 'Hetao29_Wecom_Login' ) ) {

	class Hetao29_Wecom_Login{
		var $setting_page = 'hetao29_wecom_settings'; //注册选项 设置显示在哪个页面
		var $setting_section = 'hetao29_wecom_options_section';
		var $option_key = "hetao29_wecom_options";
		var $options = [];
		private $oauth_state = '';

		function __construct(){
				$this->options= get_option($this->option_key, array());
			if(!empty($this->options['agentid']) && !empty($this->options['corpid']) && !empty($this->options['corpsecret'])){
				if(!empty($this->options['disable_system_login']) && $this->options['disable_system_login']=="on"){
					remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
					add_action('login_init', array( $this, 'login_init' ) );
				}else{
					add_action( 'login_form', array($this,'login_form' ));
				}
			}
				if ( ! empty( $this->options['agentid'] ) && ! empty( $this->options['corpid'] ) && ! empty( $this->options['corpsecret'] ) ) {
					add_action( 'login_enqueue_scripts', array( $this, 'login_enqueue_scripts' ) );
				}
				add_action( 'login_init', array( $this, 'prepare_oauth_state' ), 0 );
				add_action( 'init', array( $this, 'load_bundled_translation' ) );
			add_action('admin_init',array($this,'register_setting'));
			add_action('admin_menu', array($this,'register_setting_menu'));
			add_action('plugin_action_links_' . plugin_basename( __FILE__ ), function( $links ) {
					array_unshift(
						$links,
						sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'options-general.php?page=' . $this->setting_page ) ), esc_html__( 'Settings', 'hetao29-sign-in-for-wecom' ) )
					);
					return $links;
				}
			);
		}
		function login_enqueue_scripts() {
				wp_enqueue_script( 'hetao29-wecom-jssdk', 'https://wwcdn.weixin.qq.com/node/open/js/wecom-jssdk-2.3.4.js', array(), '2.3.4', true );
				wp_enqueue_script( 'hetao29-wecom-login', plugin_dir_url( __FILE__ ) . 'wecom-login.js', array( 'jquery', 'hetao29-wecom-jssdk' ), '1.2.1', true );
				wp_add_inline_script( 'hetao29-wecom-login', 'window.hetao29WecomLoginConfig = ' . wp_json_encode( array(
					'appid' => $this->options['corpid'],
					'agentid' => $this->options['agentid'],
					'redirect_uri' => $this->get_callback_url(),
					'state' => $this->oauth_state,
				) ) . ';', 'before' );
		}
		function login_form() {
				if($this->isQiyeWeixin()){
				echo sprintf('<p style="padding-bottom: 10px;"><a style="cursor: pointer;text-decoration: underline;" href="%s">%s</a></p>',
						esc_url($this->get_login_url()),
						esc_html__("Login with Enterprise WeChat",'hetao29-sign-in-for-wecom')
				);
			}else{
					echo sprintf('<p style="padding-bottom: 10px;"><a style="cursor: pointer;text-decoration: underline;" href="#" id="login-a">%s</a></p>',
						esc_html__("Login with Enterprise WeChat",'hetao29-sign-in-for-wecom')
				);
			}
			}
		function load_bundled_translation() {
			$domain = 'hetao29-sign-in-for-wecom';
			$locale = determine_locale();
			$pack = WP_LANG_DIR . '/plugins/' . $domain . '-' . $locale;
			$bundled = plugin_dir_path( __FILE__ ) . 'languages/' . $domain . '-' . $locale . '.mo';
			if ( ! file_exists( $pack . '.mo' ) && ! file_exists( $pack . '.l10n.php' ) && is_readable( $bundled ) ) {
				load_textdomain( $domain, $bundled );
			}
		}
			function prepare_oauth_state() {
				$this->oauth_state = bin2hex( random_bytes( 32 ) );
				$issued_at = time();
				$payload = $this->oauth_state . '.' . $issued_at;
				$signature = hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
				setcookie( 'hetao29_wecom_state', $payload . '.' . $signature, array(
					'expires' => $issued_at + 600,
					'path' => wp_parse_url( site_url( '/' ), PHP_URL_PATH ) ?: '/',
					'secure' => is_ssl(),
					'httponly' => true,
					'samesite' => 'Lax',
				) );
			}
		function login_init(){
			if($this->isQiyeWeixin() && !isset($_GET['loggedout'])){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					add_filter( 'allowed_redirect_hosts', array( $this, 'allow_wecom_redirect_host' ) );
					wp_safe_redirect( $this->get_login_url() );
					exit;
			}else{
				login_header();
				echo '<div id="login-b" style="justify-content: center; display: flex; width: 100%;"></div>';
				login_footer();
			}
				exit;
		}
			function allow_wecom_redirect_host( $hosts ) {
				$hosts[] = 'open.weixin.qq.com';
				return $hosts;
			}
			function get_callback_url() {
				return add_query_arg(
					array(
						'action'   => 'hetao29_wecom_login_callback',
					),
					admin_url( 'admin-post.php' )
				);
			}
		function register_setting_menu(){
			add_options_page(
					__('Enterprise WeChat Login Settings','hetao29-sign-in-for-wecom'),
					__('Enterprise WeChat Login','hetao29-sign-in-for-wecom'),
				'manage_options',
				$this->setting_page,
				array($this,'register_setting_page')#回调方法名称， 主要是在这里面设置页面
			);
		}
		function register_setting_page(){
			if (!current_user_can('manage_options')) {
				return;
			}
?>
    <div class="wrap">
<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	    <form action="options.php" method="post">
<?php
			#输出必要的字段
			settings_fields($this->setting_page);

			#输出显示的区域
			do_settings_sections($this->setting_page);

			#输出按钮
			submit_button();
?>

	    </form>
    </div>
<?php
		}
		function register_setting(){
			//注册一个选项，用于装载所有插件设置项
				register_setting( $this->setting_page, $this->option_key, array(
					'type'              => 'array',
					'sanitize_callback' => array( $this, 'sanitize_options' ),
				) );
			//设置字段
			add_settings_section(
				$this->setting_section,
				sprintf(
					'%s <a target="_blank" href="%s">%s</a>',
						esc_html__('Enter your Enterprise WeChat application details. See the login instructions for help.','hetao29-sign-in-for-wecom'),
					'https://developer.work.weixin.qq.com/document/path/98151',
					esc_html__('Login instructions','hetao29-sign-in-for-wecom')
				),
				array($this,'setting_section_function'),
				$this->setting_page
			);

			add_settings_field(
				'hetao29_wecom_corpid',
				sprintf('<span style="color:#EE3F4D">*</span>%s<a style="text-decoration: none;" href="%s" target="_blank"><span class="dashicons dashicons-editor-help" style="font-size: 18px;"></span></a>',esc_html__('CorpId/AppId','hetao29-sign-in-for-wecom'),'https://developer.work.weixin.qq.com/document/path/91022'),
				array($this,'setting_corpid'),
				$this->setting_page,
				$this->setting_section
			);
			add_settings_field(
				'hetao29_wecom_agentid',
				sprintf('<span style="color:#EE3F4D">*</span>%s<a style="text-decoration: none;" href="%s" target="_blank"><span class="dashicons dashicons-editor-help" style="font-size: 18px;"></span></a>',esc_html__('AgentId','hetao29-sign-in-for-wecom'),'https://developer.work.weixin.qq.com/document/path/91022'),
				array($this,'setting_agentid'),
				$this->setting_page,
				$this->setting_section,
			);
			add_settings_field(
				'hetao29_wecom_corpsecret',
					sprintf('<span style="color:#EE3F4D">*</span>%s<a style="text-decoration: none;" href="https://developer.work.weixin.qq.com/document/path/91039" target="_blank"><span class="dashicons dashicons-editor-help" style="font-size: 18px;"></span></a>',esc_html__('CorpSecret','hetao29-sign-in-for-wecom'),'https://developer.work.weixin.qq.com/document/path/91039'),
				array($this,'setting_corpsecret'),
				$this->setting_page,
				$this->setting_section,
			);
			add_settings_field(
				'hetao29_wecom_auto_register',
				esc_html__('Auto Register','hetao29-sign-in-for-wecom'),
				array($this,'setting_auto_register'),
				$this->setting_page,
				$this->setting_section,
			);
			add_settings_field(
				'hetao29_wecom_auto_register_role',
				esc_html__('Default Role of New User','hetao29-sign-in-for-wecom'),
				array($this,'setting_auto_register_role'),
				$this->setting_page,
				$this->setting_section,
			);
			add_settings_field(
				'hetao29_wecom_disable_system_login',
				esc_html__('Disable Default Login','hetao29-sign-in-for-wecom'),
				array($this,'setting_disable_system_login'),
				$this->setting_page,
				$this->setting_section,
			);
		}
		function setting_section_function(){
		}
			function sanitize_options( $input ) {
				if ( ! is_array( $input ) ) {
					return array();
				}
				$clean = array();
				foreach ( array( 'corpid', 'agentid', 'corpsecret' ) as $key ) {
					$clean[ $key ] = isset( $input[ $key ] ) && is_string( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
				}
				$clean['auto_register'] = isset( $input['auto_register'] ) && 'on' === $input['auto_register'] ? 'on' : '';
				$clean['disable_system_login'] = isset( $input['disable_system_login'] ) && 'on' === $input['disable_system_login'] ? 'on' : '';
					$role = isset( $input['auto_register_role'] ) && is_string( $input['auto_register_role'] ) ? sanitize_key( $input['auto_register_role'] ) : '';
					$clean['auto_register_role'] = array_key_exists( $role, get_editable_roles() ) ? $role : 'subscriber';
				return $clean;
			}
		function setting_auto_register(){
				echo sprintf('<input type="checkbox" %s name="%s[auto_register]"/>', checked( ! empty( $this->options['auto_register'] ), true, false ), esc_attr( $this->option_key ) );
		}
		function setting_auto_register_role(){
			$selected = $this->options['auto_register_role'] ?? 'subscriber';
			echo '<select name="' . esc_attr( $this->option_key ) . '[auto_register_role]">';
			foreach ( get_editable_roles() as $role => $details ) {
				echo sprintf( '<option value="%s" %s>%s</option>',
					esc_attr( $role ),
					selected( $selected, $role, false ),
					esc_html( translate_user_role( $details['name'] ) )
				);
			}
			echo '</select>';
		}
		function setting_disable_system_login(){
				echo sprintf('<input type="checkbox" %s name="%s[disable_system_login]" id="disable_system_login"/><label for="disable_system_login" style="font-weight:bold;color:#EE3F4D">%s</label>',
					checked( ! empty( $this->options['disable_system_login'] ), true, false ),
					esc_attr( $this->option_key ),
					esc_html__('Warning: This replaces password login. Disable the plugin if Enterprise WeChat login is unavailable.','hetao29-sign-in-for-wecom'),
			);
		}
		function setting_corpid(){
				echo sprintf('<input type="text" name="%s[corpid]" value="%s"/>', esc_attr( $this->option_key ), esc_attr($this->options['corpid']??''));
		}
		function setting_corpsecret(){
				echo sprintf('<input type="text" style="width:60%%" name="%s[corpsecret]" value="%s"/>', esc_attr( $this->option_key ), esc_attr($this->options['corpsecret']??''));
		}
		function setting_agentid(){
				echo sprintf('<input type="text" name="%s[agentid]" value="%s"/>', esc_attr( $this->option_key ), esc_attr($this->options['agentid']??''));
		}
		function isQiyeWeixin(){
				$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) && is_string( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
			if(preg_match("/(MicroMessenger)/i",$ua) && preg_match("/(wxwork)/i",$ua)){
				return true;
			}
			return false;
		}
		function get_login_url(){
				$url = $this->get_callback_url();
			return "https://open.weixin.qq.com/connect/oauth2/authorize?".http_build_query([
				"appid"=>$this->options['corpid'],
					"redirect_uri"=>$url,
				"response_type"=>"code",
				"scope"=>"snsapi_base",
					"state"=>$this->oauth_state,
				"agentid"=>$this->options['agentid'],
			])."#wechat_redirect";
		}
	}

		new Hetao29_Wecom_Login();
}
