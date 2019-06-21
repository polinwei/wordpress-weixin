<?php
defined( 'ABSPATH' ) or die( 'You cannot be here.' );
require_once WPWX_PLUGIN_DIR . '/vendor/autoload.php';
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;

global $app;

$wcdConfig= (include WPWX_PLUGIN_DIR . '/includes/WeChatDeveloper-config.php');
$ewcConfig= (include WPWX_PLUGIN_DIR . '/includes/EasyWeChat-config.php');

$app= Factory::officialAccount($ewcConfig);
$server = $app->server;
$user = $app->user;

$text = 'Hello!! Polin !!';
$openid = 'ob9Ek1V2nZrK8VVptu89XQgrCvvE';
$app->broadcasting->sendText($text, [$openId]);

//ewcSendNews();
//ewcSendMsg();


//wcdSendMessage($wcdConfig);

function wcdSendMessage($wcdConfig){
  try {
    // 实例对应的接口对象
    $msg = new \WeChat\Custom($wcdConfig);
    $data = [
      "touser"=> "ob9Ek1V2nZrK8VVptu89XQgrCvvE", 
      "msgtype"=> "text", 
      "text"=> ["content"=> "Hello Polin 魏"]    
     
    ];      
    $msg->send($data);
    
  } catch (Exception $e) {  
    // 出错啦，处理下吧
    echo $e->getMessage() . PHP_EOL;
    
  }  
}

function ewcSendMsg(){
  global $app; 

  $message = new Text('Hello world! Polin WEI !');
  
  $result = $app->customer_service->message($message)->to('ob9Ek1V2nZrK8VVptu89XQgrCvvE')->send();

}

function ewcSendNews(){
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
  $result = $app->customer_service->message($news)->to('ob9Ek1V2nZrK8VVptu89XQgrCvvE')->send();
}

