<?php


class openid_passport_trust extends openid_interface_passport{

    function __construct(){
       parent::__construct();
       $this->name = '信任登陆';
    }

    function get_name(){
        return null;
    }

    function get_login_form($auth, $appid, $view, $ext_pagedata=array()){
        return null;
    }

    function login($auth,&$usrdata){
        if($_SESSION['account']['member']){
            
			$url = &app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
            kernel::single('site_controller')->splash('success',$url,app::get('b2c')->_('您已经是登录状态，不需要重新登录'));
           
        }

        $result = kernel::single('openid_denglu')->get_user();
        if($result['rsp'] == 'succ'){
            $data = $result['data'];
            $usrdata['login_name'] = $this->_get_login_name($data['provider_code'],$data['openid']);
        }else{
            //提示会是参数错误
            $usrdata['log_data'] = $result['err_msg'];
        }
        return $usrdata['login_name'];
    }

    function _get_login_name($provider_code,$data_openid){
        $auth_model = app::get('pam')->model('auth');
        $login_name = 'auth_'.$data_openid.'_'.substr(md5($data_openid),0,5).'_'.$provider_code;
        //兼容以前版本中login_name
        $account = $auth_model->getList('account_id',array('module_uid'=>$data_openid));
        if($account){
            $auth_model->update(array('module_uid'=>$login_name),array('account_id'=>$account[0]['account_id']));
            app::get('pam')->model('account')->update(array('login_name'=>$login_name),array('account_id'=>$account[0]['account_id']));
        }else{
            $auth_data = $auth_model->getList('*',array('module_uid'=>$login_name));
            if(!$auth_data){
                $this->save_login_data($login_name);
            }
        }
        return $login_name;
    }


    function loginout($auth,$backurl="index.php"){
        unset($_SESSION['account'][$this->type]);
        unset($_SESSION['last_error']);
        #header('Location: '.$backurl);
    }

    function save_login_data($login_name){
        $account = app::get('pam')->model('account');
        $auth_model = app::get('pam')->model('auth');
        $data = array(
            'login_name' => $login_name,
            'login_password' => md5(time()),
            'account_type'=>'member',
            'createtime'=>time(),
        );
        $account_id = $account->insert($data);

        $data = array(
            'account_id'=>$account_id,
            'module_uid'=>$login_name,
            'module'=>'openid_passport_trust',
            'has_bind' => 'N',  //添加字段has_bind:是否绑定账号('N':未绑定,'Y':已绑定) 
        );

        $auth_model->insert($data);
        return true;
    }

    function get_data(){
    }

    function get_id(){
    }

    function get_expired(){
    }

    /**
	* 得到配置信息
	* @return  array 配置信息数组
	*/
    function get_config(){
        $ret = app::get('pam')->getConf('passport.'.__CLASS__);
        if($ret && isset($ret['shopadmin_passport_status']['value']) && isset($ret['site_passport_status']['value'])){
            return $ret;
        }else{
            $ret = $this->get_setting();
            $ret['passport_id']['value'] = __CLASS__;
            $ret['passport_name']['value'] = $this->name;
            $ret['shopadmin_passport_status']['value'] = 'true';
            $ret['site_passport_status']['value'] = 'false';
            $ret['passport_version']['value'] = '1.5';
            app::get('pam')->setConf('passport.'.__CLASS__,$ret);
            return $ret;
        }
    }
    /**
	* 设置配置信息
	* @param array $config 配置信息数组
	* @return  bool 配置信息设置成功与否
	*/
    function set_config(&$config){
        $appid = app::get('openid')->getConf('appid');
        $appkey= app::get('openid')->getConf('appkey');
        $save = app::get('pam')->getConf('passport.'.__CLASS__);
        if(count($config))
            foreach($config as $key=>$value){
                if(!in_array($key,array_keys($save))) continue;
                $save[$key]['value'] = $value;
            }
        if(empty($appid) || empty($appkey) ){
            $res = kernel::single('openid_denglu')->add();
            if($res['rsp'] == 'fail'){
                $config['error'] =  '开启失败：'.$res['err_msg'];
                return false;
            }
        }
        $save['shopadmin_passport_status']['value'] = 'false';
        return app::get('pam')->setConf('passport.'.__CLASS__,$save);
    }
   /**
	* 获取finder上编辑时显示的表单信息
	* @return array 配置信息需要填入的项
	*/
    function get_setting(){
        return array(
            'passport_id'=>array('label'=>app::get('pam')->_('通行证id'),'type'=>'text','editable'=>false),
            'passport_name'=>array('label'=>app::get('pam')->_('通行证'),'type'=>'text','editable'=>false),
            'shopadmin_passport_status'=>array('label'=>app::get('pam')->_('后台开启'),'type'=>'bool','editable'=>false),
            'site_passport_status'=>array('label'=>app::get('pam')->_('前台开启'),'type'=>'bool'),
            'passport_version'=>array('label'=>app::get('pam')->_('版本'),'type'=>'text','editable'=>false),
        );
    }

}