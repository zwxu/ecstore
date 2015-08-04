<?php

 
class desktop_ctl_debug extends desktop_controller{

    function index() {
        $this->path[] = array('text'=>app::get('desktop')->_('数据备份'));
        if($time = app::get('shopex')->getConf("system.last_backup")){
            $this->pagedata['time'] = date('Y-m-d H:i:s',$time);
        }
        $this->pagedata['debug'] = 'current';
        kernel::single("desktop_ctl_data")->index();
        $this->page('system/debug/clear.html');
    }
    function cleardata(){
        $filter['uname'] = $_POST['uname'];
        $filter['password'] = $_POST['password'];
        if( !$filter['uname'] || !$filter['password'] ) $this->error_splash();
        $arr = $this->login( $filter );
        if( !is_array($arr) ) $this->error_splash();
        reset( $arr );
        $arr = current( $arr );
        $arr = $this->app->model('users')->dump( $arr['account_id'] );
        
        if( $arr['super'] ) $this->clear();
        else $this->error_splash();
    }
    
    private function error_splash( $flag=false,$msg='用户名密码错误',$url=false ) {
        $this->begin($url);
        $this->end( $flag, $msg );
    }
    
    private function login( $filter ) {
        $type = pam_account::get_account_type('desktop');
        $user_data['login_name'] = $filter['uname'];
        $arr = app::get('pam')->model('account')->getList('*',array(
                'login_name'=>$filter['uname'],
                'login_password'=>pam_encrypt::get_encrypted_password($filter['password'],$type,$user_data),
                'account_type' => $type,
                'disabled' => 'false',
                ),0,1
            ); 
        return $arr;
    }
    
    private function clear() {
         foreach( kernel::servicelist("desktop_debug_clean_data") as $object ) {
             if( method_exists($object,'clean') ) 
                 $object->clean();
         }
//        app::get('b2c')->model('goods')->delete( array('goods_id|than'=>0) );
//        app::get('b2c')->model('goods_cat')->delete( array('cat_id|than'=>0) );
//        app::get('b2c')->model('goods_type')->delete( array('type_id|than'=>0) );
//        app::get('b2c')->model('brand')->delete( array('brand_id|than'=>0) );
        $this->error_splash( true, '数据清理成功!' );
    }

    

}
