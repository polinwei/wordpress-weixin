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

require_once WPWX_PLUGIN_DIR . '/includes/vue-header.php';






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
/**
 * 取得所有文章
 */
function getAllPost(){
  global $wpdb; 
  $query = "SELECT DISTINCT id, post_date, post_title, post_content, guid as post_url
            ,(SELECT display_name from wp_users WHERE wp_users.id = wp_posts.post_author) AS 'post_author'
            ,(SELECT meta_value FROM wp_postmeta WHERE wp_postmeta.meta_key='_wp_attached_file' and  wp_postmeta.post_id=
              (SELECT meta_value FROM wp_postmeta WHERE wp_postmeta.meta_key = '_thumbnail_id' AND wp_postmeta.post_id = wp_posts.ID)) AS 'image'
            ,(SELECT group_concat(wp_terms.name separator ', ') FROM wp_terms
                INNER JOIN wp_term_taxonomy on wp_terms.term_id = wp_term_taxonomy.term_id
                INNER JOIN wp_term_relationships wpr on wpr.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id
                WHERE taxonomy= 'category' and wp_posts.ID = wpr.object_id
              ) AS 'Categories'
            ,(SELECT group_concat(wp_terms.name separator ', ') 
                FROM wp_terms
                INNER JOIN wp_term_taxonomy on wp_terms.term_id = wp_term_taxonomy.term_id
                INNER JOIN wp_term_relationships wpr on wpr.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id
                WHERE taxonomy= 'post_tag' and wp_posts.ID = wpr.object_id
              ) AS 'Tags'
            FROM wp_posts
            WHERE post_type = 'post' 
            ORDER BY
            id,categories,post_date";
  $result = $wpdb->get_results($query);
  echo json_encode( $result);
}


?>

<div id="app">
  <div class="wrap">
  <template>
    <el-table :data="tableData" border style="width: 100%">
      <el-table-column 
        fixed
        prop="post_date"
        label="日期"
        width="150">
      </el-table-column>
      <el-table-column prop="image" label="image" width="120">
        <template scope="scope">            
            <el-popover
              placement="right"
              title=""
              trigger="hover">
              <img :src="scope.row.image | imgSrc" style="max-height: 300px;max-width: 300px"/>
              <img slot="reference" :src="scope.row.image | imgSrc" :alt="scope.row.image" style="max-height: 60px;max-width: 100px">
            </el-popover>
        </template>
      </el-table-column>      
      <el-table-column        
        prop="post_title"
        label="文章標題">
      </el-table-column>
      <el-table-column
        prop="post_author"
        label="作者"
        width="120">
      </el-table-column>
      <el-table-column
        prop="post_url"
        label="文章連結"
        width="120">
      </el-table-column>
      <el-table-column
        prop="Categories"
        label="類別"
        width="120">
      </el-table-column>
      <el-table-column
        prop="Tags"
        label="Tags"
        width="100">
      </el-table-column>
      <el-table-column
        fixed="left"
        label="操作"
        width="100">
        <template slot-scope="scope">
          <el-button @click="review(scope.row)" type="text" size="small">檢視</el-button>
          <el-button @click="sendMsg2WX(scope.row)" type="text" size="small">發送</el-button>
        </template>
      </el-table-column>
    </el-table>
  </template>
  </div>

</div>



<script>
	Vue.filter('imgSrc', function(value) {	
		  if (value) {
		    return '/wp-content/uploads/'+value;
		  }
	});

var Main = {
    methods: {
      review(row) {    
        console.log(row);
        this.$alert(row.post_content, row.post_title, {
          dangerouslyUseHTMLString: true
        });
      },
      sendMsg2WX(row) {
        console.log(row);
        
      }
    },

    data() {
      return {
        tableData: <?php echo json_decode(getAllPost()) ?>        
      }
    }
  }
var Ctor = Vue.extend(Main)
new Ctor().$mount('#app')
</script>