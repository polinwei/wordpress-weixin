<?php
defined( 'ABSPATH' ) or die( 'You cannot be here.' );

add_action( 'admin_menu', 'wpwx_admin_menu' );
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

  // 權限等級設為2 (即身分為作者以上的使用者都能看到這個頁面)，page為weixin-wechat-message，callback為wpwx_admin_message_page
  add_submenu_page( 
    'weixin-wechat', 
    __('Weixin Wechat', 'wpwx'), 
    __('文章發佈到微信', 'wpwx'), 
    '2', 
    'weixin-wechat-message', 
    'wpwx_admin_message_page' );

  // 權限等級設為2 (即身分為作者以上的使用者都能看到這個頁面)，page為weixin-wechat-users，callback為wpwx_admin_weixin_users_page
  add_submenu_page( 
    'weixin-wechat', 
    __('Weixin Wechat', 'wpwx'), 
    __('訊息發給微信粉絲', 'wpwx'), 
    '2', 
    'weixin-wechat-users', 
    'wpwx_admin_weixin_users_page' );     

  // 下面是管理者才有的權限
  add_submenu_page( 
    'weixin-wechat', 
    __('Weixin Wechat', 'wpwx'), 
    __('微信素材管理', 'wpwx'), 
    'manage_options', 
    'weixin-wechat-media', 
    'wpwx_admin_media_page' );

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

define( 'WPWX_AJAX_WEIXIN_ACTION_NONCE', 'wpwx-ajax-weixin-action-' );
function wpwx_admin_message_page(){
  include("message-page.php"); 
}
function wpwx_admin_weixin_users_page(){
  include("wx-users-page.php");
}

define( 'WPWX_AJAX_SETTING_ACTION_NONCE', 'wpwx-ajax-setting-action-' );
function wpwx_admin_setting_page(){  
  include("setting-page.php");  
}

function wpwx_admin_media_page(){
  include("media-page.php");
}

define( 'MY_AJAX_EXAMPLE_ACTION_NONCE', 'my-ajax-example-action-' );
function wpwx_admin_ajax_example(){  
  include("ajax-example.php");
}


