<?php

use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Article;

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

function wpwx_install() {
    global $wpdb,$app,$wpwx_db_version;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $charset_collate = $wpdb->get_charset_collate();
    // 素材資料
    $table_name = $wpdb->prefix . "wpwx_post_media";
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        media_id tinytext NOT NULL,
        media_type varchar(10) NOT NULL,
        media_name text NOT NULL,
        media_url varchar(255) NOT NULL,
        update_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,        
        post_id bigint(20) NOT NULL,
        post_guid varchar(255) DEFAULT '' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta( $sql );

    // 微信粉絲
    $table_name = $wpdb->prefix . "wpwx_openids";
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        openid tinytext NOT NULL,
        nickname varchar(50) DEFAULT '' NOT NULL,
        sex varchar(1) DEFAULT '' NOT NULL,
        language varchar(10) DEFAULT '' NOT NULL,
        city varchar(50) DEFAULT '' NOT NULL,
        province varchar(100) DEFAULT '' NOT NULL,
        country varchar(100) DEFAULT '' NOT NULL,
        headimgurl varchar(255) DEFAULT '' NOT NULL,
        subscribe_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta( $sql );

    add_option( "wpwx_db_version", $wpwx_db_version );

 }
// 抓取微信素材資料
 function getAllMedias() {
    global $wpdb,$app;

    $table_name = $wpdb->prefix . "wpwx_post_media"; 
    $mediaTotal = 0;
    // 資料先清空
    $wpdb->query(
        'DELETE  FROM '. $table_name
    );

    $list = $app->material->list('news');
    $mediaTotal = $list['total_count'];
    foreach ($list['item'] as $news) {
        foreach ( $news['content']['news_item'] as $item ){
            $wpdb->insert( 
                $table_name, 
                array( 
                    'media_id' => $news['media_id'], 
                    'media_type' => 'news', 
                    'update_time' => $news['update_time'],
                    'media_name' => $item['title'],
                    'media_url' => $item['url'],
                    'post_guid' => $item['content_source_url'],
                ) 
            );
        }
    }
    $list = $app->material->list('image');
    $mediaTotal += $list['total_count'];
    saveMediaInfo2Table($list,'image');

    $list = $app->material->list('video');
    $mediaTotal += $list['total_count'];
    saveMediaInfo2Table($list,'video');

    $list = $app->material->list('voice');
    $mediaTotal += $list['total_count'];
    saveMediaInfo2Table($list,'voice');

    return $mediaTotal;
 }

function saveMediaInfo2Table( $material_list , $type){
    global $wpdb;
    $table_name = $wpdb->prefix . "wpwx_post_media";

    foreach ($material_list['item'] as $media) {
        $wpdb->insert( 
            $table_name, 
            array( 
                'media_id' => $media['media_id'], 
                'media_type' => $type,
                'media_name' => $media['name'],
                'update_time' => $media['update_time'],
                'media_url' => $media['url'],
            ) 
        );
    }
}
// 抓取微信粉絲資料
function getAllOpenids() {
    global $wpdb,$app;

    // 微信粉絲
    $table_name = $wpdb->prefix . "wpwx_openids";
    // 資料先清空
    $wpdb->query(
        'DELETE  FROM '. $table_name
    );
    
    $users = $app->user->list();

    foreach ($users['data']['openid'] as $openid) {               
        $user = $app->user->get( $openid );     
        
        $subscribe_time = date("Y/m/d", intval($user['subscribe_time']) );
        $wpdb->insert( 
            $table_name, 
            array( 
                'openid'    => $user['openid'],
                'nickname'  => $user['nickname'],
                'sex'       => $user['sex'],
                'language'  => $user['language'],
                'city'      => $user['city'],
                'province'  => $user['province'],
                'country'   => $user['country'],
                'headimgurl'=> $user['headimgurl'],
                'subscribe_time'=> $subscribe_time,
            ) 
        );
    }
    return $users['total'];
}

// 從資料庫中抓取微信素材資料
function getAllMediasFromDB() {
    global $wpdb,$app;

    $table_name = $wpdb->prefix . "wpwx_post_media"; 
    $query = "SELECT * FROM " . $table_name;
    $result = $wpdb->get_results($query);
    echo json_encode( $result);

}

/**
 * 取得所有文章
 */
function getAllPost(){
    global $wpdb,$wp_roles; 
    $current_user = wp_get_current_user();
    $user_roles = $current_user->roles;
    $user_role = array_shift($user_roles);

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
              AND post_status = 'publish' ";
    if ( $wp_roles->roles[ $user_role ]['name'] == 'Author' ){        
        $query .= " AND post_author='$current_user->ID' ";
    }
    $query .= " ORDER BY id,categories,post_date";
    
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
    global $wpdb,$app; // this is how you get access to the database

    $AppID      = $_POST['AppID'];
    $AppSecret  = $_POST['AppSecret'];
    $Token      = $_POST['Token'];
    $IsDomestic = $_POST['IsDomestic'];
    $Welcome    = $_POST['Welcome'];
    $nonce      = $_POST['nonce'];    
    if ( wp_verify_nonce( $nonce, WPWX_AJAX_SETTING_ACTION_NONCE . date('ymdH') ) ) {
        // 先刪後增加
        delete_option( 'wpwx_AppID' );
        delete_option( 'wpwx_AppSecret' );
        delete_option( 'wpwx_Token' );
        delete_option( 'wpwx_IsDomestic' );
        delete_option( 'wpwx_Welcome' );
        add_option( 'wpwx_AppID', $AppID );
        add_option( 'wpwx_AppSecret', $AppSecret );
        add_option( 'wpwx_Token', $Token );
        add_option( 'wpwx_IsDomestic', $IsDomestic );
        add_option( 'wpwx_Welcome', $Welcome );
        
        wp_send_json_success(array('code' => 200, 'data' => $_POST ));      
        echo 0;
    } else {
        wp_send_json_error(array('code' => 500, 'data' => '', 'msg' => '錯誤的請求'));
        echo - 1;
    }
    wp_die(); // this is required to terminate immediately and return a proper response
}

// 刪除微信上所有的素材
add_action( 'wp_ajax_wpwx_ajax_delMedia_action', 'wpwx_ajax_delMedia_action' );
function wpwx_ajax_delMedia_action() {
    global $wpdb,$app; // this is how you get access to the database

    $nonce = $_POST['nonce'];
    if ( wp_verify_nonce( $nonce, WPWX_AJAX_SETTING_ACTION_NONCE . date('ymdH') ) ) {
        $table_name = $wpdb->prefix . "wpwx_post_media"; 
        $query = "SELECT * FROM " . $table_name;
        $results = $wpdb->get_results($query);

        if ($results) {
            foreach( $results as $media ) {
                // 刪除微信上永久素材
                $app->material->delete($media->media_id);
                // 刪除資料庫記錄
                $wpdb->query(
                    'DELETE  FROM '. $table_name . " WHERE media_id = " . $media->media_id
                ); 
            }
            wp_send_json_success(array('code' => 200, 'data' => $results , 'msg' => '均已刪除' ));
        } else {
            wp_send_json_success(array('code' => 200 ,'msg' => '沒有微信素材' ));
        }
        echo 0;

    } else {
        wp_send_json_error(array('code' => 500, 'data' => $_POST, 'msg' => '錯誤的請求'));
        echo -1;
    }
    
    wp_die(); // this is required to terminate immediately and return a proper response
}

// 刪除微信上單一筆素材
add_action( 'wp_ajax_wpwx_ajax_delOneMedia_action', 'wpwx_ajax_delOneMedia_action' );
function wpwx_ajax_delOneMedia_action() {
    global $wpdb,$app; // this is how you get access to the database

    $nonce = $_POST['nonce'];
    $media = $_POST['media'];

    if ( wp_verify_nonce( $nonce, WPWX_AJAX_SETTING_ACTION_NONCE . date('ymdH') ) ) {
        $table_name = $wpdb->prefix . "wpwx_post_media"; 
        // 刪除微信上永久素材
        $sendResult = $app->material->delete($media['media_id']);
        // 刪除資料庫記錄
        $wpdb->query(
            "DELETE FROM $table_name  WHERE media_id ='" . $media['media_id'] . "'"
        );
        
        $query = "SELECT * FROM " . $table_name;
        $mediaResult = $wpdb->get_results($query);
        wp_send_json_success(array('code' => 200, 'mediaData' => $mediaResult, 'data' => $sendResult , 'msg' => $media['media_id'].'-已刪除-' ));
        echo 0;
    } else {
        wp_send_json_error(array('code' => 500, 'data' => $_POST, 'msg' => '錯誤的請求'));
        echo -1;
    }
    wp_die(); // this is required to terminate immediately and return a proper response
}

// 同步微信上的粉絲與素材
add_action( 'wp_ajax_wpwx_ajax_syncwx_action', 'wpwx_ajax_syncwx_action' );
function wpwx_ajax_syncwx_action() {
    global $wpdb; // this is how you get access to the database
    global $app;  // EasyWeChat app

    $mediaTotal = 0 ;
    $userTotal = 0;
    $nonce = $_POST['nonce'];
    if ( wp_verify_nonce( $nonce, WPWX_AJAX_SETTING_ACTION_NONCE . date('ymdH') ) ) {
        $mediaTotal = getAllMedias();
        $userTotal = getAllOpenids();
        wp_send_json_success(array('code' => 200 ,'data' =>"{ 'mediaTotal':$mediaTotal, 'userTotal':$userTotal }" , 'msg' => '微信素材與粉絲同步完成' ));
    } else {
        wp_send_json_error(array('code' => 500, 'data' => $_POST, 'msg' => '錯誤的請求'));
        echo - 1;
    }
    wp_die(); // this is required to terminate immediately and return a proper response 

}
// 從資料庫取得微信粉絲
function dbGetAllOpenids() {
    global $wpdb;
    // 微信粉絲
    $table_name = $wpdb->prefix . "wpwx_openids";
    $query = "SELECT * FROM " . $table_name;
    $users = $wpdb->get_results($query);
    $openids='';
    $userNames='';
    foreach($users as $user){
        $tmp = "'$user->openid'" ;
        $openids .= $tmp . "," ;
        $tmp = "'$user->nickname'" ;
        $userNames .= $tmp . "," ;
    }
    return array($userNames, $openids);
}

// 線上取得微信粉絲
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
// 從資料庫取得所有微信粉絲
function dbGetAllUsers(){
    global $wpdb;
    // 微信粉絲
    $table_name = $wpdb->prefix . "wpwx_openids";
    $query = "SELECT * FROM " . $table_name;
    $result = $wpdb->get_results($query);
    echo json_encode( $result);
}

// 線上取得所有微信粉絲
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
        $data = "{'post_id': $post[id], 'img': $imageUrl , 'url': $post[post_url]}";
        wp_send_json_success( array('code' => 200, 'data' => $data  ) );        
        echo 0;
    } else {
        wp_send_json_error(array('code' => 500, 'data' => '', 'msg' => '錯誤的請求'));
        echo -1;
    }
    wp_die(); // this is required to terminate immediately and return a proper response
}

// 傳送圖文消息: 海外微信帳號只能傳送 type:mpnew , 所以要先上傳
add_action( 'wp_ajax_wpwx_ajax_ewcSendMedia_action', 'wpwx_ajax_ewcSendMedia_action' );
function wpwx_ajax_ewcSendMedia_action(){
    global $wpdb; // this is how you get access to the database
    global $app;  // EasyWeChat app
    $table_name = $wpdb->prefix . "wpwx_post_media";
    $media_id = '';

    $post = $_POST['post'];
    $openids = $_POST['openids'];
    $nonce = $_POST['nonce'];
    $mediaType = $_POST['mediaType'];

    if ( wp_verify_nonce( $nonce, WPWX_AJAX_WEIXIN_ACTION_NONCE . date('ymdH') ) ) {

        // 先查是否已上傳
        $query = "SELECT * FROM " . $table_name . " WHERE post_guid='$post[post_url]'";        
        $result = $wpdb->get_results($query);

        if ( count($result)==0 ){
            // 上傳圖片到微信
            $post_material_thumb =  $app->material->uploadThumb(APP_ROOT_DIR . '/wp-content/uploads/'.$post['image'] );        
            // 寫入資料庫
            $wpdb->insert( 
                $table_name, 
                array( 
                    'media_id' => $post_material_thumb['media_id'], 
                    'media_type' => 'image',
                    'media_name' => $post['image'],
                    'update_time' => $post['post_date'],
                    'media_url' => $post_material_thumb['url'],
                ) 
            );        
            
            // 文章資料上傳準備
            $article = new Article([
                'title' => $post['post_title'],
                'thumb_media_id' => $post_material_thumb['media_id'],
                'author' => $post['post_author'],
                'content' => strip_tags($post['post_content']),
                'source_url' => $post['post_url'],
                'show_cover' => 1, // 是否在文章内容显示封面图片
        
            ]);
            // 上傳到微信
            $post_material_article = $app->material->uploadArticle($article);
            // 取得此篇文章在微信的資訊
            $resource = $app->material->get($post_material_article["media_id"]);
            // 寫入資料庫
            foreach ($resource["news_item"] as $item ){
                $wpdb->insert( 
                    $table_name, 
                    array( 
                        'media_id' => $post_material_article["media_id"], 
                        'media_type' => 'news', 
                        'update_time' => $post['post_date'],
                        'media_name' => $item['title'],
                        'media_url' => $item['url'],
                        'post_id' => $post['id'],
                        'post_guid' => $item['content_source_url'],
                    ) 
                );
            };
            $media_id = $post_material_article["media_id"];
        } else {
            $media_id = $result[0]->media_id;
        }


        switch ($mediaType) {
            case 'mediaPreview':
                foreach ($openids as $user) {
                    $sendResult = $app->broadcasting->previewNews($media_id, $user['openid']);
                }                
                break;
            case 'mediaPersonal':
                $ids = array();
                foreach ($openids as $user) {
                   array_push($ids, $user['openid']);
                }
                $sendResult = $app->broadcasting->sendNews($media_id, $ids);
                break;
            case 'mediaGroup':
                $sendResult = $app->broadcasting->sendNews($media_id);
                break;
        }

        wp_send_json_success( array('code' => 200, 'data' => $sendResult  ) );        
        echo 0;

    } else {
        wp_send_json_error(array('code' => 500, 'data' => $_POST, 'msg' => '錯誤的請求'));
        echo -1;
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
    $msgType = $_POST['msgType'];
    $openid = $user['openid'];
    $nonce = $_POST['nonce'];

    if ( wp_verify_nonce( $nonce, WPWX_AJAX_WEIXIN_ACTION_NONCE . date('ymdH') ) ) {
        $msg = new Text($message);
        if ($msgType=='personal') {
            $sendResult = $app->customer_service->message($msg)->to($openid)->send();
        } else {
            $sendResult = $app->broadcasting->sendMessage($msg);
        }       

        wp_send_json_success( array('code' => 200, 'data' => $sendResult  ) );        
        echo 0;

    }else {
        wp_send_json_error(array('code' => 500, 'data' => $_POST, 'msg' => '錯誤的請求'));
        echo - 1;
    }
    wp_die(); // this is required to terminate immediately and return a proper response

}

// 建立&刪除 微信菜單
add_action( 'wp_ajax_wpwx_ajax_ewcWxMenu_action', 'wpwx_ajax_ewcWxMenu_action' );
function wpwx_ajax_ewcWxMenu_action(){
    global $wpdb; // this is how you get access to the database
    global $app;  // EasyWeChat app

    $nonce = $_POST['nonce'];
    $menuType = $_POST['menuType'];
    $termName = $_POST['termName'];

    if ( wp_verify_nonce( $nonce, WPWX_AJAX_SETTING_ACTION_NONCE . date('ymdH') ) ) {

        if ($menuType=='delete'){
            $result = $app->menu->delete(); // 全部刪除
        }
        if ($menuType=='create'){            
            list($vueMenu,$wxMenu)=get_terms_hierarchicaly($termName);
            $app->menu->create($wxMenu);
        }

        wp_send_json_success( array('code' => 200, 'result' => $result, 'wxMenu'=> $wxMenu  ) );        
        echo 0;
    } else {
        wp_send_json_error(array('code' => 500, 'data' => $_POST, 'msg' => '錯誤的請求'));
        echo - 1;
    }

    wp_die(); // this is required to terminate immediately and return a proper response
}

/**
 * Recursively sort an array of taxonomy terms hierarchically. Child categories will be
 * placed under a 'children' member of their parent term.
 * @param Array   $cats     taxonomy term objects to sort
 * @param Array   $into     result array to put them in
 * @param integer $parentId the current parent ID to put them in
 */
function sort_terms_hierarchicaly(Array &$cats, Array &$into, $parentId = 0)
{
    foreach ($cats as $i => $cat) {
        if ($cat->parent == $parentId) {
            $into[$cat->term_id] = $cat;
            unset($cats[$i]);
        }
    }

    foreach ($into as $topCat) {
        $topCat->children = array();
        sort_terms_hierarchicaly($cats, $topCat->children, $topCat->term_id);
    }
}
function get_terms_hierarchicaly($termName){
    $categories = get_terms( 'category', array('hide_empty' => 0 ) );    
    $categoryHierarchy = array();
    sort_terms_hierarchicaly($categories, $categoryHierarchy);
    
    $vue_menu = array(); // For 管理介面:微信選單設定
    $wx_menu = array();  // For 傳送到微信建立Menu
    foreach ($categoryHierarchy as $root_menu) {
        if ($root_menu->name == $termName ) {
            $i = 0;            
            foreach ($root_menu->children as $first_level) {
                                
                $vue_menu[$i]['id']= "$first_level->term_id";
                $vue_menu[$i]['name']= $first_level->name;

                $wx_menu[$i]['name']= $first_level->name;               
                if(count($first_level->children)>0){
                    $j = 0;
                    $sub_menu = array();
                    $wx_subMenu = array();
                    foreach ($first_level->children as $sub) {
                        $sub_menu[$j]['id'] =  "$sub->term_id";
                        $sub_menu[$j]['name'] =  $sub->name;
                        // Get the ID of a given category
                        $category_id = get_cat_ID( $sub->name );
                        // Get the URL of this category
                        $category_link = get_category_link( $category_id );
                        $sub_menu[$j]['link'] = $category_link;

                        $wx_subMenu[$j]['name'] =  $sub->name;
                        $wx_subMenu[$j]['type']= "view";
                        $wx_subMenu[$j]['url'] = $category_link;
                        $j++;
                    }
                    $vue_menu[$i]['sub']= $sub_menu;
                    $wx_menu[$i]['sub_button']= $wx_subMenu;

                } else {
                    // Get the ID of a given category
                    $category_id = get_cat_ID( $first_level->name );
                    // Get the URL of this category
                    $category_link = get_category_link( $category_id );
                    $vue_menu[$i]['link'] = $category_link;

                    $wx_menu[$i]['type']= "view";
                    $wx_menu[$i]['url']= $category_link;

                }
                $i++;
            }
        }
        
    }

    return array($vue_menu, $wx_menu);
    //echo json_encode($vue_menu);
    //var_dump($vue_menu);
    //var_dump($categoryHierarchy);
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