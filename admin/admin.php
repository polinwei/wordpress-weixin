<?php



add_action( 'admin_menu', 'wpwx_admin_menu', 8 );

function wpwx_admin_menu() {
	global $_wp_last_object_menu;

  
  add_menu_page(
    __('Weixin Wechat', 'wpwx'), 
    __('微信訊息整合', 'wpwx'),
    'manage_options', 
    'weixin-wechat', 
    'wpwx_admin_message_page', 
    wpwx_plugin_url() .'/images/weixin-logo.png');

  add_submenu_page( 
    'weixin-wechat', 
    __('Weixin Wechat', 'wpwx'), 
    __('微信參數設定', 'wpwx'), 
    'manage_options', 
    'weixin-wechat-setting', 
    'wpwx_admin_setting_page' );  

}

function wpwx_admin_message_page(){
  echo 'wpwx admin message page:'.WPWX_PLUGIN;
}

function wpwx_admin_setting_page(){
  echo 'wpwx_admin_setting_page:'.WPWX_PLUGIN;
  include("setting_page.php");
}

?>