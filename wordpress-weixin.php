<?php
/**
 * Plugin Name: Weixin - Wechat
 * Plugin URI: https://github.com/polinwei/wordpress-weixin
 * Description: 微信公眾號-發送訊息系統平台
 * Version: 1.0.0
 * Author: Polin WEI
 * Author URI: http://polinwei.blogspot.com/
 * License: MIT
 * Text Domain: Weixin-Wechat
 */

defined( 'ABSPATH' ) or die( 'You cannot be here.' );

if(defined('WP_DEBUG')&&WP_DEBUG===true){
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);
}

define( 'WPWX_VERSION', '1.0.0' );
define( 'WPWX_PLUGIN', __FILE__ );
define( 'WPWX_PLUGIN_BASENAME', plugin_basename( WPWX_PLUGIN ));
define( 'WPWX_PLUGIN_NAME',	trim( dirname( WPWX_PLUGIN_BASENAME ), '/' ) );
define( 'WPWX_PLUGIN_DIR', untrailingslashit( dirname( WPWX_PLUGIN ) ) );
define( 'APP_ROOT_DIR', substr(WPWX_PLUGIN,0,stripos(WPWX_PLUGIN,"wp-content")-1) );

// Deprecated, not used in the plugin core. Use wpwx_plugin_url() instead that in functions.php.
define( 'WPWX_PLUGIN_URL',  untrailingslashit( plugins_url( '', WPWX_PLUGIN ) ) );

// Include 微信外掛API
global $app, $server, $user, $wcdConfig, $ewcConfig, $wpwx_db_version;
$wpwx_db_version = '1.0';
require_once WPWX_PLUGIN_DIR . '/vendor/autoload.php';
use EasyWeChat\Factory;

$wcdConfig= (include WPWX_PLUGIN_DIR . '/includes/WeChatDeveloper-config.php');
$ewcConfig= (include WPWX_PLUGIN_DIR . '/includes/EasyWeChat-config.php');

$app= Factory::officialAccount($ewcConfig);
$server = $app->server;
$user = $app->user;

/** 啟用外掛時，呼叫 functions.php 裡的 wpwx_install 函數建立 table */
require_once WPWX_PLUGIN_DIR . '/includes/functions.php';
register_activation_hook( __FILE__, 'wpwx_install' );
// 啟用外掛時, 激活一個事件: wpwx_activation, functions.php 裡要有 wpwx_activation 函數
register_activation_hook( __FILE__, 'wpwx_activation' );
// 停用外掛時, 移除一個事件: wpwx_deactivation, functions.php 裡要有 wpwx_deactivation 函數
register_deactivation_hook( __FILE__, 'wpwx_deactivation' );


if ( is_admin() ) {
	require_once WPWX_PLUGIN_DIR . '/admin/admin.php';
}

