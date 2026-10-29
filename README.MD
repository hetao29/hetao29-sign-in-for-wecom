# Hetao29 Sign-In for WeCom

Sign in to WordPress with a WeCom (Enterprise WeChat) account. The plugin supports QR code login, in-app authorization, optional automatic user registration, and an option to replace the standard password login form.

企业微信登录插件：支持企微扫码登录、应用内授权登录、自动注册用户和按需关闭密码登录。企微返回的 UserID 直接对应 WordPress 用户名。

## Setup

1. Install and activate the plugin (PHP 7.4 or newer).
2. Open **Settings → Enterprise WeChat Login** and enter the Corp ID, Agent ID, and app secret for a WeCom application.
3. Authorize your site's domain in WeCom. The plugin generates an OAuth callback URL under `wp-admin/admin-post.php`.
4. Verify sign-in before enabling **Disable Default Login**.
5. An existing WordPress account signs in when its username matches the WeCom User ID exactly. Automatic registration creates the account with that User ID as its username and uses the **Default Role of New User** setting (subscriber by default).

## External services

The login page loads the WeCom JavaScript SDK from `wwcdn.weixin.qq.com`. Authentication sends the Corp ID and app secret to the WeCom token API, then sends the access token and authorization code to the WeCom user info API. See the [WeCom developer documentation](https://developer.work.weixin.qq.com/) and [privacy policy](https://work.weixin.qq.com/nl/privacy).
