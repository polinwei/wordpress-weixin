<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

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
          <el-input v-model="settingForm.AppSecret"></el-input>
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


<script>
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
    methods: {
      submitForm(formName) {
        this.$refs[formName].validate((valid) => {
          if (valid) {
            alert('submit!');
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
var wxSetting = new settingPage().$mount('#wxSetting')
</script>
