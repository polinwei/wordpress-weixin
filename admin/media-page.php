<?php
defined( 'ABSPATH' ) or die( 'You cannot be here.' );
global $app;
require_once WPWX_PLUGIN_DIR . '/includes/vue-header.php';
?>

<div id="app">
  <div class="wrap">
  <template>
    <el-table :data="tableData" border style="width: 100%" v-loading.fullscreen.lock="fullscreenLoading">
      <el-table-column
        fixed
        prop="update_time"
        label="上傳日期"
        width="150">
      </el-table-column>
      <el-table-column
        prop="media_id"
        label="media_id"
        width="250">
      </el-table-column>
      <el-table-column
        prop="media_type"
        label="素材類別"
        width="120">
      </el-table-column>
      <el-table-column
        prop="media_name"
        label="素材網址"
        width="250">
        <template slot-scope="scope">
          <a :href="scope.row.media_url"
            target="_blank">{{scope.row.media_name}}</a>
        </template>        
      </el-table-column>
      <el-table-column
        prop="post_guid"
        label="文章連結">
        <template slot-scope="scope">
          <a :href="scope.row.post_guid"
            target="_blank">{{scope.row.post_guid}}</a>
        </template>
      </el-table-column>
      <el-table-column
        fixed="left"
        label="操作"
        width="60">
        <template slot-scope="scope">
          <el-button @click="deleteWxMedia(scope.row)" type="text" size="small">刪除</el-button>
        </template>
      </el-table-column>
    </el-table>
  </template>
  </div>
</div>  





<script>
jQuery(document).ready(function ($) {
	Vue.filter('imgSrc', function(value) {	
		  if (value) {
		    return '/wp-content/uploads/'+value;
		  }
	});

  var Main = {
    methods: {
      deleteWxMedia(row) {
        console.log(row);
        var media = row;
        var responseMediaData;        
        this.$confirm('是否刪除微信上這筆素材？', '確認訊息', {
          distinguishCancelAndClose: true,
          confirmButtonText: '確定',
          cancelButtonText: '取消'
        }).then(() => {
          // 傳送資料時, 禁止使用者再按其它按鍵        
          this.fullscreenLoading = true;
          var data = {
                      'action': 'wpwx_ajax_delOneMedia_action',
                      'media': media,
                      'nonce': '<?php echo wp_create_nonce(WPWX_AJAX_SETTING_ACTION_NONCE . date('ymdH') ); ?>'
          };
          $.post(ajaxurl, data, function (response) {
              responseMediaData =  response.data.mediaData;
              wxMedia.tableData= responseMediaData;
              wxMedia.fullscreenLoading = false;
              alert('Deleted Success!!' );                
          }).error(function(response) { wxMedia.fullscreenLoading = false; alert("Oops! Sorry error occurred! Internet issue."); });
          
        }).catch(action => {
          this.$message({
            type: 'info',
            message: '已取消刪除'
          })
        });        
      }
    },
    data() {
      return {
        tableData: <?php echo json_decode(getAllMediasFromDB()) ?>,
        fullscreenLoading: false,
      }
    }
  }
var Ctor = Vue.extend(Main)
var wxMedia = new Ctor().$mount('#app')


});
</script>  