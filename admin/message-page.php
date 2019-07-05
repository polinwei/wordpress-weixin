<?php
defined( 'ABSPATH' ) or die( 'You cannot be here.' );
require_once WPWX_PLUGIN_DIR . '/includes/vue-header.php';
?>

<div id="app">
  <div class="wrap">
  <template>
    <el-table :data="postTableData" border style="width: 100%">
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
          <el-button @click="openDialog(scope.$index,scope.row)" type="text" size="small">發送</el-button>
        </template>
      </el-table-column>
    </el-table>
  </template>
  </div>

  <el-dialog
    title="提示"
    width="50%"
    :show-close=false
    :visible.sync="dialogVisible"    
    :before-close="handleCloseDialog"
    center>  

    <template>
      <el-transfer
        filterable
        :filter-method="filterMethod"
        filter-placeholder="請輸入微信名字"
        v-model="openidSelected"
        :data="openidList"
        :titles="['微信粉絲', '發送清單']">
      </el-transfer>
    </template>

    <span slot="footer" class="dialog-footer">
      <el-button @click="dialogVisible = false">取消</el-button>
      <el-button type="primary" @click="dialogVisible = false; handleCloseDialog()">確認發送</el-button>
    </span>
  </el-dialog>


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
      review(row) {    
        //console.log(row);
        this.$alert(row.post_content, row.post_title, {
          dangerouslyUseHTMLString: true
        });
      },
      handleCloseDialog(done) {
        //console.log("before close");
        var post = this.post;
        var openidList = this.openidList;        
        var openidSelected = this.openidSelected;
        var openids=[];
        openidSelected.forEach(function(item, index, array){
          openids.push(openidList[item]);
        });        
        var data = {
                'action': 'wpwx_ajax_ewcSendNews_action',
                'post': post, 
                'openids': openids,
                'nonce': '<?php echo wp_create_nonce(WPWX_AJAX_WEIXIN_ACTION_NONCE . date('ymdH') ); ?>'
        };
        $.post(ajaxurl, data, function (response) {                
            alert('Send success!!' );                 
        })
        .error(function(response) { alert("Oops! Sorry error occurred! Internet issue."); });
      },
      openDialog(index,row) {
        //console.log(index, row);//这里可打印出每行的内容 
        this.dialogVisible = true;
        this.post=row;
      }
    },

    data() {

      const generateData = _ => {
        const data = [];
        const userNames = [<?php list($userNames, $openids) = ewcGetAllOpenids(); echo $userNames; ?>];
        const openid = [<?php list($userNames, $openids) = ewcGetAllOpenids(); echo $openids; ?>];
        userNames.forEach((userName, index) => {
          data.push({
            label: userName,
            key: index,
            openid: openid[index]
          });
        });
        return data;
      };

      return {
        dialogVisible: false,
        post:{},
        postTableData: <?php echo json_decode(getAllPost()) ?>,
        wxTableData: [<?php ewcGetAllUsers(); ?>],
        openidList: generateData(),
        openidSelected: [],
        filterMethod(query, item) {
          return item.label.indexOf(query) > -1;
        }        
      }
    }
  }
  var Ctor = Vue.extend(Main)
  new Ctor().$mount('#app')
});
</script>