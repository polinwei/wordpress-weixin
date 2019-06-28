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

// Deprecated, not used in the plugin core. Use wpwx_plugin_url() instead that in functions.php.
define( 'WPWX_PLUGIN_URL',  untrailingslashit( plugins_url( '', WPWX_PLUGIN ) ) );

// Include 微信外掛API
require_once WPWX_PLUGIN_DIR . '/vendor/autoload.php';
use EasyWeChat\Factory;


$wcdConfig= (include WPWX_PLUGIN_DIR . '/includes/WeChatDeveloper-config.php');
$ewcConfig= (include WPWX_PLUGIN_DIR . '/includes/EasyWeChat-config.php');

$app= Factory::officialAccount($ewcConfig);
$server = $app->server;
$user = $app->user;
global $app, $server, $user, $wcdConfig, $ewcConfig;

require_once WPWX_PLUGIN_DIR . '/includes/functions.php';

if ( is_admin() ) {
	require_once WPWX_PLUGIN_DIR . '/admin/admin.php';
}

