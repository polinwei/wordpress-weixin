<?php

function wpwx_plugin_url( $path = '' ) {
	$url = plugins_url( $path, WPWX_PLUGIN );

	if ( is_ssl() && 'http:' == substr( $url, 0, 5 ) ) {
		$url = 'https:' . substr( $url, 5 );
	}

	return $url;
}

function wpwx_array_flatten( $input ) {
	if ( ! is_array( $input ) ) {
		return array( $input );
	}

	$output = array();

	foreach ( $input as $value ) {
		$output = array_merge( $output, wpwx_array_flatten( $value ) );
	}

	return $output;
}

/**
 * Create a Custom Action Hook
 */
//1. Define the hook:
function wpwx_hook() {
  // Set the action at priority of 10 and note that it accepts 2 arguments.
  do_action('wpwx_hook');
}
//2.  Attach your function to the hook using add_action:
add_action('wpwx_hook', 'wpwx_hook_function'); 
function wpwx_hook_function() {
	echo 'Hey, that is amazing.';
}

/**
 * 建立 weixin token verified 的回復頁面
 * 要先安裝 plugin: wp-router URL: https://github.com/jbrinley/WP-Router
 */
add_action( 'wp_router_generate_routes', 'add_wpwxtoken_route', 20 );
function add_wpwxtoken_route( $router ) { 
    $route_args = array(
                        'path' => '^wpwxtoken',
                        'query_vars' => array( ),
                        'page_callback' => 'wpwxtoken_route_callback',
                        'page_arguments' => array( ),
                        'access_callback' => true,
                        'title' => __( 'weixin token Route' ),
                        'template' => array(
                                    'page.php',
                                dirname( __FILE__ ) . '/page.php'
                        )
                );

    $router->add_route( 'wpwxtoken-route-id', $route_args );
}

function wpwxtoken_route_callback( ) {
    include (WPWX_PLUGIN_DIR . '/admin/wx-token.php');
}


add_action( 'wp_ajax_wpwx_ajax_setting_action', 'wpwx_ajax_setting_action' );
// add_action('wp_ajax_nopriv_wpwx_ajax_setting_action', 'wpwx_ajax_setting_action'); //不需登入即可使用
function wpwx_ajax_setting_action() {
    global $wpdb; // this is how you get access to the database

    $AppID    = $_POST['AppID'];
    $AppSecret    = $_POST['AppSecret'];
    $nonce = $_POST['nonce'];
    if ( wp_verify_nonce( $nonce, WPWX_AJAX_SETTING_ACTION_NONCE ) ) {
        // 先刪後增加
        delete_option( 'wpwx_AppID' );
        delete_option( 'wpwx_AppSecret' );
        add_option( 'wpwx_AppID', $AppID );
        add_option( 'wpwx_AppSecret', $AppSecret );
        $data = "{'AppID': $AppID,'AppSecret' : $AppSecret}";
        wp_send_json_success(array('code' => 200, 'data' => $data));        
        echo 0;
    } else {
        wp_send_json_error(array('code' => 500, 'data' => '', 'msg' => '錯誤的請求'));
        echo - 1;
    }
    wp_die(); // this is required to terminate immediately and return a proper response
}

/**
 * Ajax Example
 */
add_action( 'wp_ajax_my_ajax_example_action', 'my_ajax_example_action' );

function my_ajax_example_action() {
    global $wpdb; // this is how you get access to the database

    $id    = $_POST['id'];
    $nonce = $_POST['nonce'];
    if ( wp_verify_nonce( $nonce, MY_AJAX_EXAMPLE_ACTION_NONCE . $id ) ) {
        $response = intval( $id );
        $response += 10;
        echo $response;
    } else {
        echo - 1;
    }
    wp_die(); // this is required to terminate immediately and return a proper response
}