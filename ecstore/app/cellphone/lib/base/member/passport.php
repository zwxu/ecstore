<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class cellphone_base_member_passport extends cellphone_cellphone{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
        kernel::single('base_session')->start();
    }

    //手机平台用户登录
    public function login(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'uname'=>app::get('b2c')->_('用户登录名'),
            'password'=>app::get('b2c')->_('登录密码')
        );
        $this->check_params($must_params);


        //$params['password'] = trim($this->des_decode($params['password'],$this->des_private_key));

		if($uc = kernel::service("uc_user_login")){
            if($userInfo = $uc->uc_user_login($params['uname'], $params['password'])){
                $rows = $userInfo;
            }else{
				$this->send(false,null,app::get('b2c')->_('UC登录失败，用户名或密码错误'));
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
            /*
            if($params['remember'] === "true")
                 setcookie('pam_passport_basic_uname', $rows[0]['login_name'], time()+365*24*3600, '/');
            else setcookie('pam_passport_basic_uname', '', 0, '/');
            */

            //unset($_SESSION['error_count'][$auth->appid]);

            if(substr($rows[0]['login_password'], 0, 1) !== 's'){
                $pam_filter = array(
                    'account_id' => $rows[0]['account_id']
                );

                $string_pass = md5($rows[0]['login_password'].$rows[0]['login_name'].$rows[0]['createtime']);
                $update_data['login_password'] = 's' . substr($string_pass, 0, 31);
                app::get('pam')->model('account')->update($update_data, $pam_filter);

            }

            /*不同步登录UC，此处注释掉
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

        if(!$data['member_id']){
            $this->send(false,null,app::get('b2c')->_('登录失败'));
        }

        $data['session']=kernel::single('base_session')->sess_id();

        /*
            print_r('<pre>');
            print_r($data);
            print_r('</br>');
            //print_r($_SESSION);
            print_r('</pre>');
        */

        $this->send(true,$data,app::get('b2c')->_('登录成功'));
    }

    //手机平台用户注册
    public function signup(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'uname'=>app::get('b2c')->_('用户登录名'),
            'password'=>app::get('b2c')->_('登录密码')
        );
        $this->check_params($must_params);

        //账号名
        $uname=strtolower(trim($params['uname']));

        //pass
        $password=trim($params['password']);

        if(strpos($uname,'@')===true){
            //邮箱注册
            $reg_data['reg_type']='email';
        }elseif(preg_match('/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}|14[0-9]{1}[0-9]{8}$/',$uname)){
            //手机注册
            $reg_data['reg_type']='mobile';
            if(!$params['cellphonevcode']){
               $this->params['mobile']=$uname;
               $this->getCellphoneCode();
            }
        }else{
            //用户名注册
            $reg_data['reg_type']='username';
        }

        $member_mdl = &app::get('b2c')->model('members');

        if($reg_data['reg_type'] == 'username'){
            $reg_data['pam_account']['login_name'] = $uname;
            if($params['email']){
                $reg_data['contact']['email'] = htmlspecialchars($params['email']);
            }else{
                $this->send(false,null,app::get('b2c')->_('缺少邮箱地址'));
            }
        }else{
            $login_name_prefix = $member_mdl->getLoginNamePrefix();
            $random_login_name = strtolower($member_mdl->randomName($login_name_prefix, 12));

            while(true){
                if(!$member_mdl->is_exists($random_login_name)){
                    break;
                }
                $random_login_name = strtolower($member_mdl->randomName($login_name_prefix, 12));
            }
            $reg_data['pam_account']['login_name'] = $random_login_name;

            if($reg_data['reg_type'] == 'email'){
                $reg_data['contact']['email'] = htmlspecialchars($uname);
                $reg_data['verify_email'] = 'Y';
            }

            if($reg_data['reg_type'] == 'mobile'){
                $reg_data['contact']['phone']['mobile'] = $uname;
                $reg_data['verify_mobile'] = 'Y';
            }
        }

        if(!$reg_data['contact']['phone']['mobile'] && $params['mobile']){
            if(preg_match('/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}|14[0-9]{1}[0-9]{8}$/',$params['mobile'])){
                $reg_data['contact']['phone']['mobile']=$params['mobile'];
            }
        }
        $reg_data['pam_account']['login_password'] = $password;
        $reg_data['pam_account']['psw_confirm'] = $password;

        $validate_rst = $member_mdl->validate($reg_data, $msg);
        if(!$validate_rst){
            $this->send(false,null,$msg);
        }

        //验证手机短信验证码
        if($reg_data['reg_type'] == 'mobile'){
            if(!$this->checkCellphoneCode($uname,$params['cellphonevcode'], $msg)){
                $this->send(false,$params,$msg);
            }
        }

        $lv_model = &app::get('b2c')->model('member_lv');
        $reg_data['member_lv']['member_group_id'] = $lv_model->get_default_lv();
        $arrDefCurrency = app::get('ectools')->model('currency')->getDefault();
        $reg_data['currency'] = $arrDefCurrency['cur_code'];

        //传递给UC
        $reg_data['uc_pwd'] = $reg_data['pam_account']['login_password'];

        $reg_data['pam_account']['account_type'] = 'member';
        $reg_data['pam_account']['createtime'] = time();
        $use_pass_data['login_name'] = $reg_data['pam_account']['login_name'];
        $use_pass_data['createtime'] = $reg_data['pam_account']['createtime'];
        $reg_data['pam_account']['login_password'] = pam_encrypt::get_encrypted_password(trim($reg_data['pam_account']['login_password']), 'member', $use_pass_data);

        $reg_data['reg_ip'] = base_request::get_remote_addr();
        $reg_data['regtime'] = time();

        $db = kernel::database();
        $db->beginTransaction();

        //同步到ucenter
        if( $uc = kernel::service("uc_user_register") ) {
            $uid = $uc->uc_user_register($reg_data['pam_account']['login_name'],$reg_data['pam_account']['psw_confirm'],$reg_data['contact']['email'],'','','',$reg_data['contact']['phone']['mobile'],null,$reg_data['reg_type']);
            if($uid>0){
                $reg_data['foreign_id'] = $uid;
            }else{
                if( $reg_data['reg_type'] == 'mobile' ){
                   $msg=app::get('b2c')->_('UCenter注册失败,请检查用户名或手机号');
                }else if($_POST['reg_type'] == 'email') {
                   $msg=app::get('b2c')->_('UCenter注册失败,请检查用户名或邮箱');
                }else {
                   $msg=app::get('b2c')->_('UCenter注册失败,请检查用户名');
                }
                $db->rollBack();
                $this->send(false,null,$msg);
			}
        }
        //同步到ucenter

        $reg_data['source'] = 'mobile';
        if(!$member_mdl->save($reg_data)){ // 存储失败
            $db->rollBack();
            $this->send(false,'',app::get('b2c')->_('注册失败'));
        }

        $member_id = $reg_data['member_id'];
        if(!$this->save_attr($member_id,$reg_data,$msg)){ // 存储失败
            $db->rollBack();
            $this->send(false,'',$msg);
        }

        // 存储成功 提交DB事务
        $db->commit();

        // 注册成功后 默认登录
        kernel::single('base_session')->start();
        $_SESSION['account'][pam_account::get_account_type('b2c')] = $member_id;
        kernel::single('b2c_frontpage')->bind_member($member_id);

        //同步到ucenter
			if($uc = kernel::service("uc_user_synlogin")){
				$uc->uc_user_synlogin($reg_data['foreign_id']);
			}
		//同步到ucenter


        foreach(kernel::servicelist('b2c_save_post_om') as $object) {
            $object->set_arr($member_id, 'member');
            $refer_url = $object->get_arr($member_id, 'member');
        }

        /*注册完成后做某些操作! begin*/
        foreach(kernel::servicelist('b2c_register_after') as $object) {
            $object->registerActive($member_id);
        }

        //增加会员同步
        if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
            $member_rpc_object->createActive($member_id);
        }
        /*end*/

        $data['member_id'] = $member_id;
        $data['uname'] = $reg_data['pam_account']['login_name'];
        //$data['passwd'] = $reg_data['pam_account']['psw_confirm'];
        //$data['email'] = $reg_data['contact']['email'];
        //$data['refer_url'] = $refer_url ? $refer_url : '';
        //$data['is_frontend'] = true;
        //app::get('b2c')->model('member_account')->fireEvent('register',$data,$member_id);
        unset($_SESSION['CELLPHONE_CODE']);//注册成功后，注销本次验证码
        $data['sess_id']=kernel::single('base_session')->sess_id();
        $this->send(true,$data,app::get('b2c')->_('注册成功'));

    }

    /**
    * @method : checkCellphoneCode
    * @description : 验证验证码是否正确
    * @params :
    *       $code : 输入的验证码
    *       $msg : 返回信息
    * @return : bool
    */
    private function checkCellphoneCode($mobile,$code,&$msg){
        $session_obj = kernel::single('base_session');
		$session_obj->set_sess_id(str_pad($mobile,32,'1'));

		if(base_kvstore::instance('sessions')->fetch(str_pad($mobile,32,'1'), $_SESSION) === false){
              $_SESSION = array();
              $this->send('-1',null,'验证码已过期');
        }

        kernel::single('base_session')->set_sess_expires(0);
		$session_obj->start();

        $vcode = $_SESSION['CELLPHONE_CODE'];
        if(trim($code) == ''){
            $msg = '短信验证码为空！';
            return false;
        }elseif(trim($code) != trim($vcode)){
            $msg = '短信验证码错误！';
            return false;
        }else{
            return true;
        }
    }

    public function  chkCellphoneCode(){
        $params = $this->params;
         //检查应用级必填参数
        $must_params = array(
            'mobile'=>'手机号码',
            'cellphonevcode'=>'手机验证码'
        );
        $this->check_params($must_params);

        if(!$this->checkCellphoneCode($params['mobile'],$params['cellphonevcode'], $msg)){
                $this->send(false,null,$msg);
        }

        $this->send(true,null,app::get('b2c')->_('验证码正确'));
    }

    //手机短信验证码
    public function getCellphoneCode(){

        $params = $this->params;
         //检查应用级必填参数
        $must_params = array(
            'mobile'=>'手机号'
        );
        $this->check_params($must_params);

        $mobile = trim($params['mobile']);

        if($params['session']){
            $member=$this->get_current_member();
            $member_id=$member['member_id'];
        }
        
        $type=$params['type'];

        $objmember = &app::get('b2c')->model('members');
        $objaccount = &app::get('b2c')->model('member_account');

         if($objmember->check_mobile($mobile, $message,$member_id) || $type=='recove'){
            //验证是否是恶意请求验证码 add by zlj begin
            $request_time = time();
            $isSpite = app::get('b2c')->model('message_log')->isSpiteRequest($request_time,$mobile,$msg);
            if($isSpite != 'ok'){
                if($isSpite == 'spite'){
                    $message_log = app::get('b2c')->model('message_log')->saveMessageLog(__FUNCTION__,$request_time,$mobile,base_request::get_remote_addr(),'spite','sms');//恶意请求 记录日志
                }

                $this->send(false,'failed',$msg);
            }
            //end

            $random = $objmember->randCode();
			$session_obj = kernel::single('base_session');
			$session_obj->set_sess_id(str_pad($mobile,32,'1'));
            //用手机号作为短信验证码session_id 不满32位补1
			$session_obj->set_sess_expires(0);
			$session_obj->start();
            $_SESSION['CELLPHONE_CODE'] = $random;

            //发送会员注册时的手机验证码
            $data['contact']['phone']['mobile'] = $mobile;
            $data['mobile_code'] = $random;
            $data['sendmobilecodetype'] = 'userRegister';
            $data['disabled_time'] = 2;

            $message_log = app::get('b2c')->model('message_log')->saveMessageLog(__FUNCTION__,$request_time,$mobile,base_request::get_remote_addr(),'ok','sms');//正常请求 记录日志 add by zlj

            $tmp_rs = $objaccount->fireEvent('sendmobilecode', $data);
            if($tmp_rs){
                $msg = '短信发送成功';
                $this->send(true,array('cellphone_code' => $random),$msg);
            }else{
                $msg = '短信发送失败，请重新获取验证码';
                $this->send(false,'failed',$msg);
            }
        }else{
            $this->send(false,'failed',$message);
        }
    }

    /**
    * @method : save_attr
    * @description : 保存会员注册信息
    * @params :
    *       $member_id : 会员id
    *       $aData : 会员信息数据
    *       $msg : 返回信息
    * @return : bool
    * @access : private
    */
    private function save_attr($member_id=null,$aData,&$msg){
        if(!$member_id){
            $msg = app::get('b2c')->_('注册失败');
            return false;
        }

        $member_model = &app::get('b2c')->model('members');
        $aData['pam_account']['account_id'] = $member_id;

        if(!$aData['profile']['birthday']) unset($aData['profile']['birthday']);

        if($aData['profile']['gender'] == 1){
            $aData['profile']['gender'] = 'male';
        }elseif($aData['profile']['gender'] ===0){
            $aData['profile']['gender'] = 'female';
        }else{
            $aData['profile']['gender'] = 'no';
        }

        foreach($aData as $key=>$val){
            if(strpos($key,"box:") !== false){
                $aTmp = explode("box:",$key);
                $aData[$aTmp[1]] = serialize($val);
            }
        }

        if($aData['contact']['name']&&!preg_match('/^[0-9a-zA-Z_\\-\x{4e00}-\x{9fa5}]+$/u', $aData['contact']['name'])){
            $msg = app::get('b2c')->_('姓名包含非法字符');
            return false;
        }

        if(preg_match('/^[0-9]+$/', $aData['contact']['name'])){
            $msg = app::get('b2c')->_('用户名不能全为数字');
            return false;
        }

        $obj_filter = kernel::single('b2c_site_filter');
        $aData = $obj_filter->check_input($aData);

        if($member_model->save($aData)){
            $msg = app::get('b2c')->_('注册成功');
            return true;
        }

        $msg  = app::get('b2c')->_('注册失败');
        return false;
    }


     /**
    * @method : memberInfo
    * @description : 会员中心
    */
    public function memberInfo(){
        $params = $this->params;
        $must_params = array(
            'session' => '用户ID',
        );
        $this->check_params($must_params);

        $member = $this->get_current_member();
        if(!$member['member_id']){
            $this->send(false,null,'该会员不存在');
        }

        $obj_member_lv = app::get('b2c')->model('member_lv');
        $lv_name = $obj_member_lv->dump(array('member_lv_id' => $member['member_lv']),'name');
        $member['member_lv_name'] = $lv_name['name'];

        $mobile = app::get('b2c')->model('members')->dump(array('member_id'=>$member['member_id']),'mobile');
        $member['mobile'] = $mobile['mobile'];

        $this->send(true,$member,'');
    }



    public function logout(){
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

    //会员中心修改密码
    public function security(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>app::get('b2c')->_('用户ID'),
            'old_password'=>app::get('b2c')->_('登录密码'),
            'new_password'=>app::get('b2c')->_('新密码'),
            'passwd_re'=>app::get('b2c')->_('新密码')
        );

        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];

        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在或未登录'));
        }

        $data['passwd']=trim($params['new_password']);
        $data['passwd_re']=trim($params['passwd_re']);
        $data['old_passwd']=trim($params['old_password']);

        $obj_member = &app::get('b2c')->model('members');
        $result = $obj_member->save_security($member_id,$data,$msg);
        if($result){
            $this->send(true,null,app::get('b2c')->_('修改成功'));
        }else{
            $this->send(false,null,$msg);
        }

    }

    //手机端密码找回
    public function recover(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'mobile'=>app::get('b2c')->_('手机号码'),
        );

        $this->check_params($must_params);

        $params['mobile']=trim($params['mobile']);

        if(!preg_match('/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}|14[0-9]{1}[0-9]{8}$/',$params['mobile'])){
            //手机
            $this->send(false,null,app::get('b2c')->_('手机号码格式错误'));
        }

        $obj_member = &app::get('b2c')->model('members');
        $data = $obj_member->getList('*',array('mobile'=>$params['mobile']));

       if($data[0]['mobile'] < 1){
            $this->send(false,null, app::get('b2c')->_('会员信息错误可能缺少手机号码'));
        }
        $member_id = $data[0]['member_id'];

        $rows = app::get('pam')->model('account')->getList('*', array('account_type'=>'member', 'account_id'=>$member_id));

        $sdf = &app::get('pam')->model('account')->dump($member_id);
        $new_password = $this->randomkeys(6);
        //$use_pass_data['account_id'] = $member_id ;
        $use_pass_data['login_name'] = $rows[0]['login_name'];
        $use_pass_data['createtime'] = $rows[0]['createtime'];


        $sdf['login_password'] = pam_encrypt::get_encrypted_password(trim($new_password), 'member', $use_pass_data);

		//同步到ucenter
		if( $member_object = kernel::service("uc_user_edit")) {
			$aData['member_id'] = $member_id;
			$aData['passwd_re'] = $new_password;
			if(!$member_object->uc_user_edit_pwd($aData)){
				$this->end(false,null, app::get('b2c')->_('第三方修改密码失败,请重试'));
			}
		}
		//同步到ucenter

        if(app::get('pam')->model('account')->save($sdf)){
            $xdata['uname']= $sdf['login_name'];
            $xdata['passwd']=$new_password;
            $xdata['name']= $data[0]['name'];
            //$data['contact']['phone']['mobile'] = $params['mobile'];
            $objaccount = &app::get('b2c')->model('member_account');
            $tmp_rs = $objaccount->fireEvent('lostPw', $xdata,$member_id);
           
            if($tmp_rs){
                $msg = app::get('b2c')->_('密码变更已经发送请注意查收');
                $this->send(true,array('pass'=>$new_password),$msg);
            }else{
                $msg = app::get('b2c')->_('短信发送失败，请重新找回');
                $this->send(false,null,$msg);
            }
        }else{
            $this->end(false,null,app::get('b2c')->_('发送失败，请与商家联系'));
        }

    }

    ####随机取6位字符数
    function randomkeys($length){
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';    //字符池
        for($i=0;$i<$length;$i++){
            $key .= $pattern{mt_rand(0,35)};    //生成php随机数
        }
        return $key;
    }
   

}