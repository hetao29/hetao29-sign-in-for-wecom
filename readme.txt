=== Hetao29 Sign-In for WeCom ===
Contributors: hetao29
Tags: wecom, wechat, login, oauth, sso
Requires at least: 5.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WeCom (企业微信登录) for WordPress: QR code and in-app sign-in, optional registration and password login controls.

== Description ==

This plugin adds Enterprise WeChat (WeCom) sign-in to the login page. Visitors can log in by scanning a QR code or authorizing inside the WeCom app. Administrators can allow automatic account creation and optionally replace the standard username and password login form.

企业微信登录插件：支持企业微信扫码登录、应用内授权登录、自动注册用户，以及按需关闭 WordPress 密码登录。

The plugin sends the authorization code to the WeCom API to retrieve the user's enterprise ID. It signs in the WordPress user whose username matches that ID exactly. With automatic registration enabled, an unmatched ID creates an account using the ID as its username and the configured default role.

== Installation ==

1. Install and activate the plugin.
2. Open Settings > Enterprise WeChat Login and enter the WeCom Corp ID, Agent ID, and app secret.
3. Authorize your site's domain in WeCom. The plugin generates an OAuth callback URL under `wp-admin/admin-post.php`.
4. Test sign-in before enabling the option to replace the standard login form.
5. To allow an existing user to sign in, make sure their WordPress username matches their WeCom User ID exactly. Automatic registration uses the configured Default Role of New User (subscriber by default).

== Screenshots ==

1. WeCom QR code sign-in on the WordPress login page.
2. WeCom application settings, automatic registration, and password login options.

== External services ==

Sign-in requires WeCom. The login panel loads the WeCom JavaScript SDK from `wwcdn.weixin.qq.com`. During sign-in, the plugin sends the Corp ID and app secret to the WeCom token API and sends the resulting access token and authorization code to the WeCom user info API. See the [WeCom developer documentation](https://developer.work.weixin.qq.com/) and [privacy policy](https://work.weixin.qq.com/nl/privacy).

== Changelog ==

= 1.2.1 =
* Validate OAuth state with a signed, expiring cookie instead of transient storage.
* Match WeCom User IDs directly to WordPress usernames, including automatic registration.

= 1.2.0 =
* Bind sign-in requests to a browser with one-time OAuth state and require explicit account mapping.
* Restore bundled Simplified Chinese translations and the configurable role for automatically registered users.
* Rename the plugin and prefix stored settings and registered identifiers.

= 1.1.1 =
* Use a plugin slug without the reserved wp prefix and improve the plugin description.

= 1.1.0 =
* Improve login integration and settings.
