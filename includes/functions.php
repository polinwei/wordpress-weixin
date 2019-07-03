<?php

use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;

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
 * 取得所有文章
 */
function getAllPost(){
    global $wpdb; 
    $query = "SELECT DISTINCT id, post_date, post_title, post_content, guid as post_url
              ,(SELECT display_name FROM ".$wpdb->prefix ."users WHERE ".$wpdb->prefix ."users.id =  ".$wpdb->prefix ."posts.post_author) AS 'post_author'
              ,(SELECT meta_value FROM ".$wpdb->prefix ."postmeta WHERE ".$wpdb->prefix ."postmeta.meta_key='_wp_attached_file' and  ".$wpdb->prefix ."postmeta.post_id=
                (SELECT meta_value FROM  ".$wpdb->prefix ."postmeta WHERE  ".$wpdb->prefix ."postmeta.meta_key = '_thumbnail_id' AND  ".$wpdb->prefix ."postmeta.post_id =  ".$wpdb->prefix ."posts.ID)) AS 'image'
              ,(SELECT group_concat( ".$wpdb->prefix ."terms.name separator ', ') FROM  ".$wpdb->prefix ."terms
                  INNER JOIN  ".$wpdb->prefix ."term_taxonomy on  ".$wpdb->prefix ."terms.term_id =  ".$wpdb->prefix ."term_taxonomy.term_id
                  INNER JOIN  ".$wpdb->prefix ."term_relationships wpr on wpr.term_taxonomy_id =  ".$wpdb->prefix ."term_taxonomy.term_taxonomy_id
                  WHERE taxonomy= 'category' and  ".$wpdb->prefix ."posts.ID = wpr.object_id
                ) AS 'Categories'
              ,(SELECT group_concat( ".$wpdb->prefix ."terms.name separator ', ') 
                  FROM  ".$wpdb->prefix ."terms
                  INNER JOIN  ".$wpdb->prefix ."term_taxonomy on  ".$wpdb->prefix ."terms.term_id =  ".$wpdb->prefix ."term_taxonomy.term_id
                  INNER JOIN  ".$wpdb->prefix ."term_relationships wpr on wpr.term_taxonomy_id =  ".$wpdb->prefix ."term_taxonomy.term_taxonomy_id
                  WHERE taxonomy= 'post_tag' and  ".$wpdb->prefix ."posts.ID = wpr.object_id
                ) AS 'Tags'
              FROM  ".$wpdb->prefix ."posts
              WHERE post_type = 'post' 
              AND post_status = 'publish'
              ORDER BY
              id,categories,post_date";
  
    $result = $wpdb->get_results($query);
    echo json_encode( $result);
  }

/**
 * 建立 weixin token verified 的回覆頁面
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
// 轉到這個頁面: /admin/wx-token.php 
function wpwxtoken_route_callback( ) {
    include (WPWX_PLUGIN_DIR . '/admin/wx-token.php');
}

// 設定 wechat 基本參數
add_action( 'wp_ajax_wpwx_ajax_setting_action', 'wpwx_ajax_setting_action' );
// add_action('wp_ajax_nopriv_wpwx_ajax_setting_action', 'wpwx_ajax_setting_action'); //不需登入即可使用
function wpwx_ajax_setting_action() {
    global $wpdb; // this is how you get access to the database

    $AppID    = $_POST['AppID'];
    $AppSecret    = $_POST['AppSecret'];
    $Token    = $_POST['Token'];
    $nonce = $_POST['nonce'];    
    if ( wp_verify_nonce( $nonce, WPWX_AJAX_SETTING_ACTION_NONCE . date('ymdH') ) ) {
        // 先刪後增加
        delete_option( 'wpwx_AppID' );
        delete_option( 'wpwx_AppSecret' );
        delete_option( 'wpwx_Token' );
        add_option( 'wpwx_AppID', $AppID );
        add_option( 'wpwx_AppSecret', $AppSecret );
        add_option( 'wpwx_Token', $Token );
        $data = "{'AppID': $AppID,'AppSecret' : $AppSecret, ,'Token' : $Token}";
        wp_send_json_success(array('code' => 200, 'data' => $data));        
        echo 0;
    } else {
        wp_send_json_error(array('code' => 500, 'data' => '', 'msg' => '錯誤的請求'));
        echo - 1;
    }
    wp_die(); // this is required to terminate immediately and return a proper response
}
// 傳送圖文消息
add_action( 'wp_ajax_wpwx_ajax_ewcSendNews_action', 'wpwx_ajax_ewcSendNews_action' );
function wpwx_ajax_ewcSendNews_action(){

    global $wpdb; // this is how you get access to the database
    global $app;  // EasyWeChat app
    
    $post = $_POST['post'];
    $openids = $_POST['openids'];
    $nonce = $_POST['nonce'];
    $imageUrl = get_option('siteurl').'/wp-content/uploads/'.$post['image'];
    
    if ( wp_verify_nonce( $nonce, WPWX_AJAX_WEIXIN_ACTION_NONCE . date('ymdH') ) ) {
        $items = [
            new NewsItem([
                'title'       => $post['post_title'],
                'description' => strip_tags($post['post_content']),
                'url'         => $post['post_url'],
                'image'       => $imageUrl,
            ]),
        ];
        $news = new News($items);        
        foreach($openids as $user){
            $result = $app->customer_service->message($news)->to($user['openid'])->send();            
        }
        $data = "{'post_id': $post[id], 'openid':$openids, 'img': $imageUrl , 'url': $post[post_url]}";
        wp_send_json_success( array('code' => 200, 'data' => $data  ) );        
        echo 0;
    } else {
        wp_send_json_error(array('code' => 500, 'data' => '', 'msg' => '錯誤的請求'));
        echo - 1;
    }
    wp_die(); // this is required to terminate immediately and return a proper response
}

// 傳送訊息
add_action( 'wp_ajax_wpwx_ajax_ewcSendMessage_action', 'wpwx_ajax_ewcSendMessage_action' );
function wpwx_ajax_ewcSendMessage_action(){
    global $wpdb; // this is how you get access to the database
    global $app;  // EasyWeChat app
    
    $user = $_POST['user'];
    $message = $_POST['message'];
    $openid = $user['openid'];
    $nonce = $_POST['nonce'];

    if ( wp_verify_nonce( $nonce, WPWX_AJAX_WEIXIN_ACTION_NONCE . date('ymdH') ) ) {
        $msg = new Text($message);
        $result = $app->customer_service->message($msg)->to($openid)->send();

        wp_send_json_success( array('code' => 200, 'data' => $result  ) );        
        echo 0;

    }else {
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
/**
 * EasyWeChat Function
 */
function ewcSendMsg($openid=''){
    global $app; 

    $message = new Text('Hello world! Polin WEI ! This is on Function');

    $result = $app->customer_service->message($message)->to($openid)->send();

}

function ewcSendNews($openid=''){
    global $app;
    $items = [
        new NewsItem([
            'title'       => '網站第一篇文章',
            'description' => '歡迎使用 WordPress。這是這個網站的第一篇文章，試試為這篇文章進行編輯或直接刪除，然後開始撰寫新文章！',
            'url'         => 'http://im.globeunion.com/2019/06/19/hello-world/',
            'image'       => 'http://im.globeunion.com/wp-content/uploads/2019/06/city-street-1246870_640.jpg',
        ]),
    ];
    $news = new News($items);
    $result = $app->customer_service->message($news)->to($openid)->send();
}

function ewcGetAllUsers(){
    global $app;
    $userList = "";
    $users = $app->user->list();

    foreach ($users['data']['openid'] as $openid) {               
        $user = $app->user->get( $openid );
        $subscribe_time = date("Y/m/d", intval($user['subscribe_time']) );
        $user_detail = "{ 'openid': '$user[openid]', 'nickname':'$user[nickname]', 'sex':'$user[sex]', 
            'language':'$user[language]', 'city':'$user[city]', 'province':'$user[province]', 'country':'$user[country]',
            'headimgurl':'$user[headimgurl]', 'subscribe_time':'$subscribe_time' }";
        $userList .=  $user_detail . ",";
    }

    echo $userList;
}

function ewcGetAllOpenids(){
    global $app;
    $users = $app->user->list();
    $openids='';
    $userNames='';
    foreach ($users['data']['openid'] as $openid) {               
        $user = $app->user->get( $openid );
        $tmp = "'$user[openid]'" ;
        $openids .= $tmp . "," ;
        $tmp = "'$user[nickname]'" ;
        $userNames .= $tmp . "," ;
    }
    return array($userNames, $openids);

}

/**
 * WeChatDeveloper function
 */
function wcdSendMessage($wcdConfig,$openid=''){
    try {
        // 实例对应的接口对象
        $msg = new \WeChat\Custom($wcdConfig);
        $data = [
        "touser"=> $openid, 
        "msgtype"=> "text", 
        "text"=> ["content"=> "Hello Polin 魏"]    
        
        ];      
        $msg->send($data);
        
    } catch (Exception $e) {  
        // 出错啦，处理下吧
        echo $e->getMessage() . PHP_EOL;
        
    }  
}