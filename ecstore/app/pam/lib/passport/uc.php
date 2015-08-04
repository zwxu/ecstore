<?php

 
class pam_passport_uc implements pam_interface_passport{

    function get_name(){
        return 'Discuz Ucenter';
    }

    function get_login_form($auth, $appid, $view, $ext_pagedata=array()){
        $render = app::get('pam')->render();
        $render->pagedata['callback'] = $auth->get_callback_url(__CLASS__);
        return $render->fetch('basic-login.html');
    }

    function login($auth,&$usrdata){
        $usrdata['log_data'] = app::get('site')->_('用户').$_POST['uname'].app::get('site')->_('登录成功！');
        return false;
    }
    
      function loginout($auth,$backurl="index.php"){
        unset($_SESSION['account'][$this->type]);
        unset($_SESSION['last_error']);
        header('Location: '.$backurl);
    }

    function get_data(){
    }

    function get_id(){
    }

    function get_expired(){
    }
    
    function get_config(){
       $ret = app::get('pam')->getConf('passport.'.__CLASS__);
        if($ret && isset($ret['shopadmin_passport_status']['value']) && isset($ret['site_passport_status']['value'])){
            return $ret;
        }else{
            $ret = $this->get_setting();
            $ret['passport_id']['value'] = __CLASS__;
            $ret['passport_name']['value'] = $this->get_name();
            $ret['shopadmin_passport_status']['value'] = 'false';
            $ret['site_passport_status']['value'] = 'false';
            $ret['passport_version']['value'] = '1.5';
            app::get('pam')->setConf('passport.'.__CLASS__,$ret);
            return $ret;        
        }
    }
    
    function set_config(&$config){
        $save = app::get('pam')->getConf('passport.'.__CLASS__);
        if(count($config))
            foreach($config as $key=>$value){
                if(!in_array($key,array_keys($save))) continue;
                $save[$key]['value'] = $value;
            }
        return app::get('pam')->setConf('passport.'.__CLASS__,$save);
    }

    function get_setting(){
        return array(
            'passport_id'=>array('label'=>app::get('pam')->_('通行证id'),'type'=>'text','editable'=>false),
            'passport_name'=>array('label'=>app::get('pam')->_('通行证'),'type'=>'text','editable'=>false),
            'shopadmin_passport_status'=>array('label'=>app::get('pam')->_('后台开启'),'type'=>'bool',),
            'site_passport_status'=>array('label'=>app::get('pam')->_('前台开启'),'type'=>'bool',),
            'passport_version'=>array('label'=>app::get('pam')->_('版本'),'type'=>'text','editable'=>false),
            'uc_url'=>array('label'=>'UCenter URL','type'=>'text',  ),   
            'uc_saltl'=>array('label'=>app::get('pam')->_('UCenter 通信密钥'),  'type'=>'text',  ),
            'uc_app_id'=>array('label'=>app::get('pam')->_('UCenter 应用ID'), 'type'=>'text', ),   
            'uc_db_host'=>array('label'=>app::get('pam')->_('UCenter 数据库服务器(不带http://前缀)'), 'type'=>'text', ),
            'uc_db_userl'=>array('label'=>app::get('pam')->_('UCenter 数据库用户名'), 'type'=>'text',    ),
            'uc_db_passwd'=>array('label'=>app::get('pam')->_('UCenter 数据库密码'),  'type'=>'text',  ), 
            'uc_db_dbname'=>array('label'=>app::get('pam')->_('UCenter 数据库名'), 'type'=>'text',    ),
            'uc_db_prefix'=>array('label'=>app::get('pam')->_('UCenter 表名前缀'),  'type'=>'text',  ), 
            'uc_charset'=>array('label'=>app::get('pam')->_('UCenter系统编码'),'type'=>'select','options'=>array('utf8'=>app::get('pam')->_('国际化编码(utf-8)'),'gbk'=>app::get('pam')->_('简体中文'),'bgi5'=>app::get('pam')->_('繁体中文'),'en'=>app::get('pam')->_('英文'))),
            'uc_db_charset'=>array( 'label'=>app::get('pam')->_('UCenter数据库编码'),  'type'=>'select','options'=>array('utf8'=>'UTF8','gbk'=>'GBK'), ),
        );
    }

}
