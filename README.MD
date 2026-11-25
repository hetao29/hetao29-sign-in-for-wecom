# Hetao29 Sign-In for WeCom

Sign in to WordPress with a WeCom (Enterprise WeChat) account. The plugin supports QR code login, in-app authorization, optional automatic user registration, and an option to replace the standard password login form.

企业微信登录插件：支持企微扫码登录、应用内授权登录、自动注册用户和按需关闭密码登录。企微返回的 UserID 直接对应 WordPress 用户名。

## Setup

1. Install and activate the plugin (PHP 7.4 or newer).
2. Open **Settings → Enterprise WeChat Login** and enter the Corp ID, Agent ID, and app secret for a WeCom application.
3. Authorize your site's domain in WeCom. The plugin generates an OAuth callback URL under `wp-admin/admin-post.php`.
4. Verify sign-in before enabling **Disable Default Login**.
5. An existing WordPress account signs in when its username matches the WeCom User ID exactly. Automatic registration creates the account with that User ID as its username and uses the **Default Role of New User** setting (subscriber by default).

## Screenshots

1. WeCom QR code sign-in on the WordPress login page.

   ![WeCom QR code sign-in on the WordPress login page](../assets/screenshot-1.png)

2. WeCom application settings, automatic registration, and password login options.

   ![WeCom application settings](../assets/screenshot-2.png)

## External services

The login page loads the WeCom JavaScript SDK from `wwcdn.weixin.qq.com`. Authentication sends the Corp ID and app secret to the WeCom token API, then sends the access token and authorization code to the WeCom user info API. See the [WeCom developer documentation](https://developer.work.weixin.qq.com/) and [privacy policy](https://work.weixin.qq.com/nl/privacy).

## Compatibility and verification

Version 1.2.2 was tested on WordPress 7.2 RC3 with PHP 8.4.28. The plugin does not integrate with media uploads, block editor components, the Abilities API, REST endpoints, admin menu icon overrides, or the Interactivity API. Its login script uses native browser APIs and does not require jQuery or jQuery Migrate.

On a **disposable WordPress installation** with this plugin activated, run from this repository (replace `/path/to/test-wordpress`):

```sh
WECOM_RUN_TESTS=1 wp eval-file tests/compatibility.php --path=/path/to/test-wordpress
node --test tests/frontend.test.js
wp plugin check hetao29-sign-in-for-wecom --path=/path/to/test-wordpress --require=/path/to/test-wordpress/wp-content/plugins/plugin-check/cli.php --mode=update
```

The PHP suite uses real WordPress authentication and user APIs with simulated WeCom HTTP responses. It covers signed state, token caching and refresh, failed responses, exact username matching, authentication filters, automatic registration, and core login actions. It creates and deletes test users. The JavaScript suite covers SDK failures, retry, DOM readiness, and preserving the password form. Install Plugin Check on the disposable site before running its command.

Before publishing, also verify QR-code and in-app authorization using a real WeCom application and authorized callback domain, and repeat runtime testing on PHP 8.6. These require an external application and a PHP runtime that were not available in the local test environment. Update the readme in the WordPress.org stable tag when publishing; local metadata changes do not update the directory automatically.
