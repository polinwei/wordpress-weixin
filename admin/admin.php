<?php
defined( 'ABSPATH' ) or die( 'You cannot be here.' );

add_action( 'admin_menu', 'wpwx_admin_menu', 8 );
function wpwx_admin_menu() {
	global $_wp_last_object_menu;

  $_wp_last_object_menu++;
  
  add_menu_page(
    __('Weixin Wechat', 'wpwx'), 
    __('微信訊息整合', 'wpwx'),
    'weixin-wechat-message', 
    'weixin-wechat', 
    'wpwx_admin_message_page', 
    wpwx_plugin_url() .'/images/weixin-logo.png');

  add_submenu_page( 
    'weixin-wechat', 
    __('Weixin Wechat', 'wpwx'), 
    __('微信訊息發佈', 'wpwx'), 
    'manage_options', 
    'weixin-wechat-message', 
    'wpwx_admin_message_page' );



    add_submenu_page( 
      'weixin-wechat', 
      __('Weixin Wechat', 'wpwx'), 
      __('微信粉絲', 'wpwx'), 
      'manage_options', 
      'weixin-wechat-users', 
      'wpwx_admin_weixin_users_page' );     

      add_submenu_page( 
        'weixin-wechat', 
        __('Weixin Wechat', 'wpwx'), 
        __('微信參數設定', 'wpwx'), 
        'manage_options', 
        'weixin-wechat-setting', 
        'wpwx_admin_setting_page' );       
      

    add_submenu_page( 
      'weixin-wechat', 
      __('Weixin Wechat', 'wpwx'), 
      __('Ajax Example', 'wpwx'), 
      'manage_options', 
      'weixin-wechat-ajax-example', 
      'wpwx_admin_ajax_example' );  

}

function wpwx_admin_message_page(){
  //echo 'wpwx admin message page:'.__FILE__;
  //include("wx-token.php");
  include("message-page.php"); 
}

define( 'WPWX_AJAX_SETTING_ACTION_NONCE', 'wpwx-ajax-setting-action-' );
function wpwx_admin_setting_page(){  
  include("setting-page.php");  
}

function wpwx_admin_weixin_users_page(){
  include("wx-users-page.php");
}

define( 'MY_AJAX_EXAMPLE_ACTION_NONCE', 'my-ajax-example-action-' );
function wpwx_admin_ajax_example(){  
  include("ajax-example.php");
}


