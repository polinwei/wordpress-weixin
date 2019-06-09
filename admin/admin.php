<?php



add_action( 'admin_menu', 'wpwx_admin_menu', 8 );

function wpwx_admin_menu() {
	global $_wp_last_object_menu;

  $_wp_last_object_menu++;
  add_menu_page(
    __('Weixin Wechat', 'wpwx'), 
    '微信訊息整合',
    'manage_options', 
    'weixin-wechat', 
    'wpwx_admin_page', 
    wpwx_plugin_url() .'/images/weixin-logo.png');

    

}

function wpwx_admin_page(){
  echo 'wpwx admin page:'.WPWX_PLUGIN_DIR;
}

?>