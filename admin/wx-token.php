<?php
/**
 * 驗證微信的 token
 */
$path = $_SERVER['DOCUMENT_ROOT'];
include_once $path . '/wp-config.php';
include_once $path . '/wp-load.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';
global $wpdb;

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

//$posts = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type='post'");
?>   