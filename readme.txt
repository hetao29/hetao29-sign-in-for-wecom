=== Login Integration for Enterprise WeChat ===
Contributors: Hetao
Tags: wecom, wechat, login, oauth, sso
Requires at least: 5.0
Tested up to: 7.1
Requires PHP: 7.0
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enterprise WeChat (WeCom) login with QR code and in-app authorization, optional user registration, and configurable password login.

== Description ==

This plugin adds Enterprise WeChat (WeCom) sign-in to the login page. Visitors can log in by scanning a QR code or authorizing inside the WeCom app. Administrators can allow automatic account creation and optionally replace the standard username and password login form.

The plugin sends the authorization code to the WeCom API to retrieve the user's enterprise ID. If the ID matches an existing local username, that account is signed in. With automatic registration enabled, the plugin creates an account for a new ID.

== Installation ==

1. Install and activate the plugin.
2. Open Settings > Enterprise WeChat Login and enter the WeCom Corp ID, Agent ID, and app secret.
3. Authorize your site's domain in WeCom. The plugin generates an OAuth callback URL under `wp-admin/admin-post.php`.
4. Test sign-in before enabling the option to replace the standard login form.

== External services ==

Sign-in requires WeCom. The login panel loads the WeCom JavaScript SDK from `wwcdn.weixin.qq.com`. During sign-in, the plugin sends the Corp ID and app secret to the WeCom token API and sends the resulting access token and authorization code to the WeCom user info API. See the [WeCom developer documentation](https://developer.work.weixin.qq.com/) and [privacy policy](https://work.weixin.qq.com/nl/privacy).

== Changelog ==

= 1.1.1 =
* Use a plugin slug without the reserved wp prefix and improve the plugin description.

= 1.1.0 =
* Improve login integration and settings.
