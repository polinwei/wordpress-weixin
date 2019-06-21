<?php
defined( 'ABSPATH' ) or die( 'You cannot be here.' );
return [  
  'token'   => get_option( 'wpwx_Token'),
  'appid'  => get_option( 'wpwx_AppID'),         // AppID
  'appsecret'  => get_option( 'wpwx_AppSecret'),     // AppSecret      
  'encodingaeskey' => '',
  // 配置商户支付参数（可选，在使用支付功能时需要）
  'mch_id'         => "",
  'mch_key'        => '',
  // 配置商户支付双向证书目录（可选，在使用退款|打款|红包时需要）
  'ssl_key'        => '',
  'ssl_cer'        => '',
  // 缓存目录配置（可选，需拥有读写权限）
  'cache_path'     => WPWX_PLUGIN_DIR .'/tmp',
];
