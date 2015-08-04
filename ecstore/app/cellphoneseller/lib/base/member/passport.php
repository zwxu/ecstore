<?php

class cellphoneseller_base_member_passport extends cellphoneseller_cellphoneseller{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }

    /**
     * business_logout 卖家登录接口方法
     * @author
     **/
    public function business_login(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'uname'=>app::get('b2c')->_('用户登录名'),
            'password'=>app::get('b2c')->_('登录密码')
        );
        $this->check_params($must_params);

        if($uc = kernel::service("uc_user_login")){
            if($userInfo = $uc->uc_user_login($params['uname'], $params['password'])){
                $rows = $userInfo;
            }else{
                $this->send(false,null,app::get('b2c')->_('用户名或密码错误'));
            }
        }else{
            //不启用UC时验证
            $password_string = pam_encrypt::get_encrypted_password($params['password'],'member',
                                                            array('login_name'=>$params['uname']));
            $rows = app::get('pam')->model('account')->getList('*',array(
                                            'login_name'=>$params['uname'],
                                            'login_password'=>$password_string,
                                            'account_type' =>'member',
                                            'disabled' => 'false',
                                            ),0,1);



        }

        //登录时判断是否存在用户名或已验证邮箱或已验证手机
        if(empty($rows)){
            unset($rows);
            $mem_email_rows = app::get('b2c')->model('members')->getList('*', array(
                'email' => $params['uname'],
                'disabled' => 'false',
            ));

            if(empty($mem_email_rows)){
                $mem_mobile_rows = app::get('b2c')->model('members')->getList('*', array(
                    'mobile' => $params['uname'],
                    'disabled' => 'false',
                ));

                if(empty($mem_mobile_rows)){
                   $this->send(false,null,app::get('b2c')->_('用户名或密码错误'));
                }else{
                    $rows = app::get('pam')->model('account')->getList('*',
                                        array('account_id' => $mem_mobile_rows[0]['member_id'],
                                        'login_password' => $password_string,
                                        'account_type' =>'member', 'disabled' => 'false'), 0, 1);
                }
            }else{
                $rows = app::get('pam')->model('account')->getList('*',
                                        array('account_id' => $mem_email_rows[0]['member_id'],
                                        'login_password' => $password_string,
                                        'account_type' =>'member', 'disabled' => 'false'), 0, 1);
            }
        }

        if($rows[0]){
            if($_POST['remember'] === "true")
                 setcookie('pam_passport_basic_uname', $rows[0]['login_name'], time()+365*24*3600, '/');
            else setcookie('pam_passport_basic_uname', '', 0, '/');

            //unset($_SESSION['error_count'][$auth->appid]);

            if(substr($rows[0]['login_password'], 0, 1) !== 's'){
                $pam_filter = array(
                    'account_id' => $rows[0]['account_id']
                );

                $string_pass = md5($rows[0]['login_password'].$rows[0]['login_name'].$rows[0]['createtime']);
                $update_data['login_password'] = 's' . substr($string_pass, 0, 31);
                app::get('pam')->model('account')->update($update_data, $pam_filter);

            }

            /*
            //同步到ucenter
            if($rows[0]['account_type'] == 'member' && $uc = kernel::service("uc_user_synlogin")){
                $uid = kernel::single('b2c_mdl_members')->getList('foreign_id',
                                                        array('member_id'=>$rows[0]['account_id']));
                if($uid[0]['foreign_id']){
                    $uc->uc_user_synlogin($uid[0]['foreign_id']);
                }
            }
            //同步到ucenter
            */

            kernel::single('base_session')->set_sess_expires(0);
            //设置cookie过期时间，单位：分
            kernel::single('base_session')->start();
            $_SESSION['account'][pam_account::get_account_type('b2c')] =$rows[0]['account_id'];
            kernel::single('b2c_frontpage')->bind_member($rows[0]['account_id'],true);

        }else{
           $this->send(false,null,app::get('b2c')->_('用户名或密码错误'));
        }

        $data=$this->get_current_member();

        if($data['seller'] != 'seller'){
            $this->send(false,null,app::get('b2c')->_('非卖家账号'));
        }
        $data['session']=kernel::single('base_session')->sess_id();

        $this->send(true,$data,'');
    }

     /**
     * business_logout 卖家退出登录接口方法
     * @author
     **/
    public function business_logout(){
        $params = $this->params;
         //检查应用级必填参数
        $must_params = array(
            'session'=>'session ID'
        );
        $this->check_params($must_params);

        $auth = pam_auth::instance(pam_account::get_account_type(app::get('b2c')->app_id));
        foreach(kernel::servicelist('passport') as $k=>$passport){
           $passport->loginout($auth);
        }

        foreach(kernel::servicelist('member_logout') as $service){
            $service->logout();
        }

        /*
        //同步到ucenter
        if($uc = kernel::service("uc_user_synlogout")){
            $uc->uc_user_synlogout();
        }
        //同步到ucenter
        */
        kernel::single('base_session')->destory();
        $this->send(true,null,app::get('b2c')->_('退出登录成功'));

    }

    /**
     * business_detail_get 获取卖家个人信息接口方法
     * @author
     **/
    public function business_detail_get(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID'
        );
        $this->check_params($must_params);
        $member= $this->get_current_member();

        if(!$member['member_id']){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
            exit;
        }

        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('b2c')->_('非卖家账号'));
            exit;
        }

        $Data = $this->getBusinessMemInfo($member['member_id']);
        $this->send(true,$Data,app::get('b2c')->_('success'));

    }

     /**
     * business_detail_update 更新卖家个人信息接口方法
     * @author
     * @param 字段名称参考卖家中心->个人信息
     **/
    public function business_detail_update(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'updateParams'=>'需要更新的内容'
        );
        $this->check_params($must_params);
        $member= $this->get_current_member();
        if(!$member['member_id']){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
            exit;
        }

        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('b2c')->_('非卖家账号'));
            exit;
        }

        $updateParams = json_decode($params['updateParams'],true);

        if(!$this->save_member_info($updateParams,$member['member_id'],&$msg)){
            $this->send(false,null,$msg);
        }else{
            $this->send(true,null,app::get('b2c')->_('success'));
        }
    }

    /**
     * business_password_update 卖家修改密码接口方法
     * @author
     **/
    public function business_password_update(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>app::get('b2c')->_('用户ID'),
            'old_password'=>app::get('b2c')->_('登录密码'),
            'new_password'=>app::get('b2c')->_('新密码')
        );

        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];

        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在或未登录'));
        }

        $data['passwd']=trim($params['new_password']);
        $data['passwd_re']=trim($params['new_password']);
        $data['old_passwd']=trim($params['old_password']);

        $obj_member = &app::get('b2c')->model('members');
        $result = $obj_member->save_security($member_id,$data,$msg);
        if($result){
            $this->send(true,null,app::get('b2c')->_('修改成功'));
        }else{
            $this->send(false,null,$msg);
        }

    }

    /**
     * getregions 地区获取
     * @author 
     **/
    public function getregions(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>app::get('b2c')->_('用户ID')
        );

        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];

        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在或未登录'));
        }
        
        $region_mod = app::get('ectools')->model('regions');
        if($params['type'] == 'all'){
            if($params['region_id'] == 0){
                $Data = $region_mod->getList('region_id,local_name,p_region_id',array());
            }else{
                $grade = $region_mod->dump(array('region_id'=>$params['region_id']),'region_grade');
                $Data = $region_mod->db->select('select region_id,local_name,p_region_id from '.$region_mod->table_name(1).' where region_grade >'.$grade['region_grade'].' and region_path like "%,'.$params['region_id'].',%"');
            }
        }else{
            if($params['region_id'] == 0){
                $Data = $region_mod->getList('region_id,local_name,p_region_id',array('region_grade'=>'1'));
            }else{
                $Data = $region_mod->getList('region_id,local_name,p_region_id',array('p_region_id'=>$params['region_id']));
            }
        }
        if($Data){
            $this->send(true,$Data,app::get('b2c')->_('success'));
        }else{
            $this->send(false,null,'地区不存在');
        }

    }

    /**
     * app_detail_get 获取app的相关信息接口方法
     * @author qianlei
     **/
    public function app_detail_get(){
        $Data['copyright'] = $this->app->getConf('cellphoneseller.appinfo.copyright');
        $Data['license'] = $this->app->getConf('cellphoneseller.appinfo.license');
        $Data['description'] = $this->app->getConf('cellphoneseller.appinfo.description');

        $this->send(true,$Data,app::get('b2c')->_('success'));
    }

     public function feedback_add(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'content'=>'内容',
            'contact'=>'联系方式'
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];

        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }


        $backMod = $this->app->model('feedback');
        $data = array(
                'member_id'=>$member['member_id'],
                'content'=>$params['content'],
                'contact'=>$params['contact'],
            );
        if($backMod->save($data)){
            $this->send(true,null,app::get('b2c')->_('反馈成功'));
        }else{
            $this->send(false,null,app::get('b2c')->_('反馈失败'));
        }
    }

    /*----------------私有方法--------------------*/
    /**
     * getBusinessMemInfo 获取卖家个人信息私有方法
     * @author qianlei
     **/
    private function getBusinessMemInfo($member_id){
        $member_model = &app::get('b2c')->model('members');
        $mem = $member_model->dump($member_id);
        $cur_model = app::get('ectools')->model('currency');
        $cur = $cur_model->curAll();
        $Data = array();
        foreach((array)$cur as $item){
           $options[$item['cur_code']] = $item['cur_name'];
        }


        $cur['options'] = $options;
        $cur['value'] = $mem['currency'];
        $Data['currency'] = $cur;
        $mem_schema = $member_model->_columns();
        $attr =array();
            foreach(app::get('b2c')->model('member_attr')->getList() as $item){
            if($item['attr_show'] == "true") $attr[] = $item; //筛选显示项
        }
        foreach((array)$attr as $key=>$item){
            $sdfpath = $mem_schema[$item['attr_column']]['sdfpath'];
            if($sdfpath){
                $a_temp = explode("/",$sdfpath);
                if(count($a_temp) > 1){
                    $name = array_shift($a_temp);
                    if(count($a_temp))
                    foreach($a_temp  as $value){
                        $name .= '['.$value.']';
                    }
                }
            }else{
                $name = $item['attr_column'];
            }
            if($item['attr_group'] == 'defalut'){
             switch($attr[$key]['attr_column']){
                    case 'area':
                    $attr[$key]['attr_value'] = $mem['contact']['area'];
                    break;
                     case 'birthday':
                    $attr[$key]['attr_value'] = $mem['profile']['birthday'];
                    break;
                    case 'name':
                    $attr[$key]['attr_value'] = $mem['contact']['name'];
                    break;
                    case 'nickname':
                    $attr[$key]['attr_value'] = $mem['nickname'];
                    break;
                    case 'idcard':
                    $attr[$key]['attr_value'] = $mem['idcard'];
                    break;
                    case 'mobile':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['mobile'];
                    break;
                    case 'tel':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['telephone'];
                    break;
                    case 'zip':
                    $attr[$key]['attr_value'] = $mem['contact']['zipcode'];
                    break;
                    case 'addr':
                    $attr[$key]['attr_value'] = $mem['contact']['addr'];
                    break;
                    case 'sex':
                    $attr[$key]['attr_value'] = $mem['profile']['gender'];
                    break;
                    case 'pw_answer':
                    $attr[$key]['attr_value'] = $mem['account']['pw_answer'];
                    break;
                    case 'pw_question':
                    $attr[$key]['attr_value'] = $mem['account']['pw_question'];
                    break;
                   }
           }
          if($item['attr_group'] == 'contact'||$item['attr_group'] == 'input'||$item['attr_group'] == 'select'){
              $attr[$key]['attr_value'] = $mem['contact'][$attr[$key]['attr_column']];
              if($item['attr_sdfpath'] == ""){
              $attr[$key]['attr_value'] = $mem[$attr[$key]['attr_column']];
              if($attr[$key]['attr_type'] =="checkbox"){
              $attr[$key]['attr_value'] = unserialize($mem[$attr[$key]['attr_column']]);
              }
          }
          }

          $attr[$key]['attr_column'] = $name;
          if($attr[$key]['attr_column']=="birthday"){
              $attr[$key]['attr_column'] = "profile[birthday]";
          }

          if($attr[$key]['attr_type'] =="select" ||$attr[$key]['attr_type'] =="checkbox"){
              $attr[$key]['attr_option'] = unserialize($attr[$key]['attr_option']);
          }

        }
        $Data['attr'] = $attr;
        $Data['email'] = $mem['contact']['email'];
        $Data['mobile'] = $mem['contact']['phone']['mobile'];

        return $Data;
    }

    /**
     * save_member_info 保存卖家个人信息私有方法
     * @author qianlei
     **/
    private function save_member_info($params,$member_id,&$msg){
        $member_model = &app::get('b2c')->model('members');
        foreach($params as $key=>$val){
            if(strpos($key,"box:") !== false){
                $aTmp = explode("box:",$key);
                $params[$aTmp[1]] = serialize($val);
            }
        }

        $params = $this->check_input($params);

        if($params['contact']['email']&&$member_model->is_exists_email($params['contact']['email'],$member_id)){
            $msg = '邮箱已经存在';
            return false;
        }

        if($params['contact']['phone']['mobile'] && !preg_match('/^1[3458][0-9]{9}$/',$params['contact']['phone']['mobile'])){
            $msg = '手机输入格式不正确';
            return false;
        }

        //--防止恶意修改
        $arr_colunm = array('contact','profile','pam_account','currency');
        $attr = app::get('b2c')->model('member_attr')->getList('attr_column');
        foreach($attr as $attr_colunm){
            $colunm = $attr_colunm['attr_column'];
            $arr_colunm[] = $colunm;
        }
        foreach($params as $post_key=>$post_value){
            if( !in_array($post_key,$arr_colunm) ){
                unset($params[$post_key]);
            }
        }
        //---end

        $params['member_id'] = $member_id;

        //同步到ucenter yindingsheng
        if( $member_object = kernel::service("uc_user_edit")) {
            if(!$member_object->uc_user_edit($params)){
                $msg = '提交失败';
                return false;
            }
        }
        //同步到ucenter yindingsheng

        if($member_model->save($params)){

            //增加会员同步 2012-05-15
            if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
                $member_rpc_object->modifyActive($params['member_id']);
            }

            return true;
        }else{
            $msg = '提交失败';
            return false;
        }
    }

        /*
        过滤POST来的数据,基于安全考虑,会把POST数组中带HTML标签的字符过滤掉
    */
    private function check_input($data){
        $aData = $this->arrContentReplace($data);
        return $aData;
    }

    private function arrContentReplace($array){
        if (is_array($array)){
            foreach($array as $key=>$v){
                $array[$key] = $this->arrContentReplace($array[$key]);
            }
        }
        else{
            $array = strip_tags($array);
        }
        return $array;
    }

}