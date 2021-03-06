<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once WPWX_PLUGIN_DIR . '/includes/vue-header.php';

?>

<div id="app">
  <div class="wrap">
  <el-button type="primary" @click="msgType='group'; sendMsg2WX(null)" type="text" size="small">群發訊息</el-button>
  <template>
    <el-table
      :data="tableData"
      border
      style="width: 100%">
      <el-table-column
        fixed
        prop="subscribe_time"
        label="觀注日期"
        width="100">
        <template scope="scope">
          {{ scope.row.subscribe_time | formatDate}}
        </template>
      </el-table-column>
      <el-table-column
        prop="nickname"
        label="姓名"
        sortable=true
        width="120">
      </el-table-column>
      <el-table-column
        prop="province"
        label="省份"
        width="120">
      </el-table-column>
      <el-table-column
        prop="city"
        label="城巿"
        width="120">
      </el-table-column>
      <el-table-column prop="headimgurl" label="Avatar" width="120">
        <template scope="scope">
              <img :src="scope.row.headimgurl" style="max-height: 60px;max-width: 60px"/>
        </template>
      </el-table-column>      
      <el-table-column
        fixed="right"
        label="操作">
        <template slot-scope="scope">
          <el-button @click="msgType='personal'; sendMsg2WX(scope.row)" type="text" size="small">發送訊息</el-button>
        </template>
      </el-table-column>
    </el-table>
  </template>
  </div>
</div>

<script>
jQuery(document).ready(function ($) {  

  Vue.filter('formatDate', function(value) {
    if (value) {
      return moment(String(value)).format('YYYY/MM/DD')
    }
  });

  var Main = {
      data() {
        return {
          msgType: 'personal',
          tableData: <?php echo json_decode(dbGetAllUsers()); ?>
        }
      },
      methods: {
        sendMsg2WX(row) {
          //console.log(row);

          this.$prompt('請輸入要傳送的訊息', '提示', {
            confirmButtonText: '確定',
            cancelButtonText: '取消',
          }).then(({ value }) => {
            this.$message({
              type: 'success',
              message: '您要傳送的訊息是: ' + value
            });

            var data = {
                          'action': 'wpwx_ajax_ewcSendMessage_action',
                          'user': row,
                          'message' : value,
                          'msgType': this.msgType,                       
                          'nonce': '<?php echo wp_create_nonce(WPWX_AJAX_WEIXIN_ACTION_NONCE . date('ymdH') ); ?>'
              };
              $.post(ajaxurl, data, function (response) {                
                  alert('Send success!!' );                 
              })
              .error(function(response) { alert("Oops! Sorry error occurred! Internet issue."); });


          }).catch(() => {
            this.$message({
              type: 'warning',
              message: '取消傳送訊息'
            });       
          });

        }      
      }
    }
  var Ctor = Vue.extend(Main)
  new Ctor().$mount('#app')
});
</script>