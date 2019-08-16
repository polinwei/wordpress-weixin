<?php
/**
 * 驗證微信的 token
 */
$path = $_SERVER['DOCUMENT_ROOT'];
include_once $path . '/wp-config.php';
include_once $path . '/wp-load.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';

define( 'WPWX_PLUGIN', __FILE__ );
define( 'WPWX_PLUGIN_BASENAME', plugin_basename( WPWX_PLUGIN ));
define( 'WPWX_PLUGIN_NAME',	trim( dirname( WPWX_PLUGIN_BASENAME ), '/' ) );
define( 'WPWX_PLUGIN_DIR', untrailingslashit( dirname( WPWX_PLUGIN ) ) );
define( 'APP_ROOT_DIR', substr(WPWX_PLUGIN,0,stripos(WPWX_PLUGIN,"wp-content")-1) );

require_once WPWX_PLUGIN_DIR . '/vendor/autoload.php';
use EasyWeChat\Factory;

$ewcConfig= (include WPWX_PLUGIN_DIR . '/includes/EasyWeChat-config.php');
$app= Factory::officialAccount($ewcConfig);
$server = $app->server;

$app->server->push(function ($message) {
  return get_option( 'wpwx_Welcome');
});
$response = $app->server->serve();
$response->send(); exit;

/**
 *   改用 EasyWeChat 來回應

// 由微信平台自已設定的 token 值
$token     = get_option( 'wpwx_Token');

// 由微信平台 post 送來的參數
$signature =isset($_GET["signature"])?$_GET["signature"]:'';
$timestamp =isset($_GET["timestamp"])?$_GET["timestamp"]:'';
$nonce = isset($_GET["nonce"])?$_GET["nonce"]:'';
$echostr   = isset($_GET['echostr'])?$_GET["echostr"]:'';
$post_data = "{'signature':$signature, 'timestamp:', $timestamp, 'nonce:': $nonce, 'echostr': $echostr }";
//組合參數作 shal 運算
$tmpArr = array($token, $timestamp, $nonce);
sort($tmpArr,SORT_STRING);
$tmpStr = implode( $tmpArr );
$tmpStr = sha1( $tmpStr );

//驗證 
if( $tmpStr == $signature ){
  echo $echostr;  
}else{  
  //wp_send_json_error( array('code' => 500, 'data' => $post_data, 'msg' => '錯誤的請求')  );
  wp_redirect(home_url());
}
exit();
 */

?>   