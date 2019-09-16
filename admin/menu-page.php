<?php
defined( 'ABSPATH' ) or die( 'You cannot be here.' );
global $app;
require_once WPWX_PLUGIN_DIR . '/includes/vue-header.php';
?>

<div id="app" v-loading.fullscreen.lock="fullscreenLoading">
  <div class="wrap">
    <el-button type="primary" @click="menuType='create';createWxMenu()" type="text" size="small">建立Menu</el-button>
    <el-button type="warning" @click="menuType='delete';deleteWxMenu()" type="text" size="small">清除Menu</el-button>
    <el-menu :default-active="activeIndex" mode="horizontal" @select="handleSelect" style="width: 50%" >
      <template v-for="item in menu" :index="item.id" >
          <template v-if=item.sub>
            <el-submenu :index="item.id">            
              <template slot="title"><span v-text="item.name"></span></template>
              <el-menu-item-group  v-for="sub in item.sub" :key="sub.id">
                <el-menu-item :index="sub.id"> <a :href="sub.link" target="_blank"><span v-text="sub.name"></span> </a>
                </el-menu-item>
              </el-menu-item-group>
            </el-submenu>
          </template>
          <template v-else>          
            <el-menu-item :index="item.id"><a :href="item.link" target="_blank"><span v-text="item.name"></span> </a></el-menu-item>
          </template>
        </template>
    </el-menu>

  </div>
</div>  

<script>
jQuery(document).ready(function ($) {

  var Main = {    
    data() {
      //console.log('data');
      return {
        fullscreenLoading: false,
        activeIndex: '1',
        menuType: 'delete',
        termName: 'wx_menu',
        menu: '',        
      };
    },
    mounted() {
      //console.log('mounted');      
      this.menu = <?php $vueMenu=get_menu_hierarchicaly(get_option( 'wpwx_wxMenuInWP'),'sub'); echo json_encode($vueMenu); ?>
    },
    methods: {
      handleSelect(key, keyPath) {
        console.log(key, keyPath);
      },
      createWxMenu(){
        this.$confirm('確認依顯示建立Menu', '提示', {
            confirmButtonText: '確定',
            cancelButtonText: '取消',
          }).then(({ value }) => {
            // 傳送資料時, 禁止使用者再按其它按鍵        
            this.fullscreenLoading = true;            
            var data = {
                          'action': 'wpwx_ajax_ewcWxMenu_action',
                          'menuType': this.menuType,
                          'termName': this.termName,                      
                          'nonce': '<?php echo wp_create_nonce(WPWX_AJAX_SETTING_ACTION_NONCE . date('ymdH') ); ?>'
              };
              $.post(ajaxurl, data, function (response) { 
                  wxMenu.fullscreenLoading = false;               
                  alert('Send success!!' );                 
              })
              .error(function(response) { wxMenu.fullscreenLoading = false; alert("Oops! Sorry error occurred! Internet issue."); });
          }).catch(() => {
            this.$message({
              type: 'warning',
              message: '取消建立Menu'
            });       
          });
      },
      deleteWxMenu(){

        this.$confirm('確認要清除Menu', '提示', {
            confirmButtonText: '確定',
            cancelButtonText: '取消',
          }).then(({ value }) => {
            // 傳送資料時, 禁止使用者再按其它按鍵        
            this.fullscreenLoading = true;            
            var data = {
                          'action': 'wpwx_ajax_ewcWxMenu_action',
                          'menuType': this.menuType,
                          'termName': this.termName,                       
                          'nonce': '<?php echo wp_create_nonce(WPWX_AJAX_SETTING_ACTION_NONCE . date('ymdH') ); ?>'
              };
              $.post(ajaxurl, data, function (response) {
                  wxMenu.fullscreenLoading = false;                
                  alert('Send success!!' );                 
              })
              .error(function(response) { wxMenu.fullscreenLoading = false; alert("Oops! Sorry error occurred! Internet issue."); });
          }).catch(() => {
            this.$message({
              type: 'warning',
              message: '取消清除Menu'
            });       
          });

      }
    }
  }
var Ctor = Vue.extend(Main)
var wxMenu=new Ctor().$mount('#app')

});
</script>      