<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$AppID = get_option( 'wpwx_AppID');
$AppSecret = get_option( 'wpwx_AppSecret');

require_once WPWX_PLUGIN_DIR . '/includes/vue-header.php';
?>

<div id="wxSetting">  
  <el-tabs type="border-card">
    <el-tab-pane>
      <span slot="label"><i class="el-icon-setting"></i> 設定 (Basic Settings)</span>
      <el-form :model="settingForm" :rules="rules" ref="settingForm" label-width="100px" >
        <el-form-item label="AppID" prop="AppID">
          <el-input v-model="settingForm.AppID"></el-input>
        </el-form-item>
        <el-form-item label="AppSecret" prop="AppSecret">
          <el-input v-model="settingForm.AppSecret" value=<?php echo $AppSecret?> ></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="submitForm('settingForm')">存檔</el-button>
          <el-button @click="resetForm('settingForm')">重置</el-button>
        </el-form-item>
      </el-form>      
    </el-tab-pane>
    <el-tab-pane>
      <span slot="label"><i class="el-icon-alarm-clock"></i> 排程 (Schedule Settings)</span>
      定時任務    
    </el-tab-pane>
  </el-tabs>
</div>



<?php
add_action( 'admin_footer', 'wpwx_setting_javascript' ); // Write our JS below here

function wpwx_setting_javascript() { ?>
	<script type="text/javascript" >

/* 關閉vue-devtools */
//Vue.config.devtools = true;

/* 關閉錯誤警告 */
//Vue.config.debug = false;

var wxSetting = {
  data() {
    return {
      settingForm: {
        AppID: '',
        AppSecret: ''          
      },
      rules: {
        AppID: [
          { required: true, message: '請輸入 AppID', trigger: 'blur' }
        ],
        AppSecret: [
          { required: true, message: '請輸入 AppSecret', trigger: 'blur' }
        ]
      }
    };
  },
  mounted() {
	  console.log('init');
    this.settingForm.AppID='<?php echo get_option( 'wpwx_AppID'); ?>';
    this.settingForm.AppSecret='<?php echo get_option( 'wpwx_AppSecret'); ?>';
  },
  methods: {
    submitForm(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          /* 送出Post */
          alert('submit!'+this.settingForm.AppID+'-'+this.settingForm.AppSecret);
          // '<?php echo WPWX_PLUGIN_URL."/admin/ajax-setting.php" ?>'
          // ajaxurl
          var data = {
                      'action': 'wpwx_ajax_setting_action',
                      'AppID': this.settingForm.AppID, 
                      'AppSecret': this.settingForm.AppSecret,
                      'nonce': '<?php echo wp_create_nonce('wpwx-ajax-setting-action'); ?>'
          };
          $.post(ajaxurl, data, function (response) {
                      //alert('Got this from the server: ' + response);
  
          });
          
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    resetForm(formName) {
      this.$refs[formName].resetFields();
    }
  }
}
var settingPage = Vue.extend(wxSetting)
var vueSetting = new settingPage().$mount('#wxSetting')

	</script> <?php
}

