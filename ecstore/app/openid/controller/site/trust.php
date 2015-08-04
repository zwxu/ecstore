<?php
class openid_ctl_site_trust extends b2c_frontpage{

    function __construct(&$app){
        parent::__construct($app);
        $this->_response->set_header('Cache-Control', 'no-store');
        kernel::single('base_session')->start();
    }


    //信任登陆回调函数(token_url)
    function callback(){
            app::get('openid')->setConf('trust_token',$_GET['token']);
            $callback = kernel::single('pam_callback');
            $params['module'] = 'openid_passport_trust';
            $params['type'] = pam_account::get_account_type('b2c');
            $back_url = $this->gen_url(array('app'=>'openid','ctl'=>'site_trust','act'=>'post_login','full'=>1));
            $params['redirect'] = base64_encode($back_url);

            $callback->login($params);

            if($result_m['redirect_url']){
                echo "script>window.location=decodeURIComponent('".$result_m['redirect_url']."');</script>";
                exit;
            }else{
                echo "<script>top.window.location='".$back_url."'</script>";
                exit;
            }
    }


    //pam登陆后处理(保存信任登陆返回的信息)
    function post_login(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
        $member_id = $_SESSION['account'][pam_account::get_account_type('b2c')];

        if($member_id){
            $obj_mem = app::get('b2c')->model('members');
            $obj_openid = app::get('openid')->model('openid');
            $member_point = app::get('b2c')->model('member_point');

            $member_data = $obj_mem->dump($member_id);
            $lv_model = app::get('b2c')->model('member_lv');
            $member_lv_id = $lv_model->get_default_lv();
            $result = kernel::single('openid_denglu')->get_user();

            $data = array(
                'member_id' => $member_id,
                'member_lv_id' => $member_lv_id,
                //'email' => $result['data']['email'],
                'name'=> empty($result['data']['nickname']) ? $result['data']['realname'] : $result['data']['nickname'],
                'addr' => $result['data']['address'],
                'sex' => $this->gender($result['data']['gender']),
                'trust_name' => empty($result['data']['nickname'])?$result['data']['realname']:$result['data']['nickname'],
            );

            $save = array(
                'member_id' => $member_id,
                'openid' => $result['data']['openid'],
                'provider_code' => $result['data']['provider_code'],
                'provider_openid' => $result['data']['provider_openid'],
                'avatar' => $result['data']['avatar'],
                'email' => $result['data']['email'],
                'address' => $result['data']['address'],
                'gender' => $result['data']['gender'],
                'nickname' => $result['data']['nickname'],
                'realname' => $result['data']['realname'],
            );

            $pam_accout_info = kernel::single('pam_mdl_account')->getRow('login_name',array('account_id'=>$member_id));


            if( ($uc = kernel::service("uc_user_register")) && empty($member_data['foreign_id'])) {
				$data['nick_name'] = $data['name'];

				$trustInfo['trust_data'] = $data;
				unset($trustInfo['trust_data']['member_id']);
				$trustInfo['trust_save'] = $save;
				unset($trustInfo['trust_save']['member_id']);
				$extra = serialize($trustInfo);
				//同步到ucenter yindingsheng
                $rs = $uc->uc_user_register($pam_accout_info['login_name'],time(),'','','','','',$extra,'user_name',$save['nickname']);//密码问题
				//同步到ucenter yindingsheng
                if($rs < 0){
					if($rs == -3){//-3表示soo已经存在该用户,需要查member_id
						$user_rs = $uc->uc_get_user($pam_accout_info['login_name']);
						$data['foreign_id'] = $user_rs['uid'];
                    }else{
                        $obj_pam_account = app::get('pam')->model('account');
                        $obj_pam_account->delete(array('account_id'=>$member_id));
                        $obj_pam_auth = app::get('pam')->model('auth');
                        $obj_pam_auth->delete(array('account_id'=>$member_id));
						$this->splash('failed', kernel::base_url(1), app::get('b2c')->_('登录失败，参数错误！'));
                    }
                    
                }else{
					$data['foreign_id'] = $rs;
				}
            }

            if(!$member_data){
                $data['regtime'] = time(); //注册时间
                if($obj_mem->insert($data)){
                    $obj_openid->insert($save);
					//同步到ucenter yindingsheng
					if( $uc = kernel::service("uc_user_synlogin")) {
						$uc->uc_user_synlogin($data['foreign_id']);
					}
					//同步到ucenter yindingsheng

                    $this->bind_member($member_id);
                }else{
                    $this->splash('failed',$url,app::get('b2c')->_('登录失败,请联系商店管理员'));
                }
            }else{
                if($obj_mem->update($data, array('member_id' => $member_id))){
                    $obj_openid->update($save, array('openid' => $save['openid']));
                }

                $sdf = $obj_mem->dump($member_id);
                $obj_order = app::get('b2c')->model('orders');
                $msg = kernel::single('b2c_message_msg');
                $sdf['order_num'] = count($obj_order->getList('order_id', array('member_id' => $member_id)));
                $sdf['unreadmsg'] = count($msg->getList('*', array('to_id' => $member_id, 'has_sent' => 'true', 'for_comment_id' => 'all', 'mem_read_status' => 'false')));
                unset($msg);

                if(app::get('b2c')->getConf('site.level_switch') == 1){
                    $sdf['member_lv']['member_group_id'] = $obj_mem->member_lv_chk($sdf['member_lv']['member_group_id'], $sdf['experience']);
                }

                if(app::get('b2c')->getConf('site.level_switch') == 0 && app::get('b2c')->getConf('site.level_point') == 1){
                    $sdf['member_lv']['member_group_id'] = $member_point->member_lv_chk($member_id, $sdf['member_lv']['member_group_id'], $sdf['score']['total']);
                }

                $obj_mem->save($sdf);
				//同步到ucenter yindingsheng
				if( $uc = kernel::service("uc_user_synlogin")) {
					$uc->uc_user_synlogin($member_data['foreign_id']);
				}
				//同步到ucenter yindingsheng
                $this->bind_member($member_id);
            }

            //根据账号绑定状态判断是否跳转到绑定页面 
            $obj_account = app::get('pam')->model('auth');
            $account_data = $obj_account->dump(array('account_id' => $member_id), '*');

            if(!empty($account_data)){
                if($account_data['has_bind'] == 'N'){
                    $objRepass = app::get('b2c')->model('member_pwdlog');
                    $secret = $objRepass->generate($member_id);
                    if($objRepass->isValiad($secret)){
                        $this->pagedata['secret'] = $secret;
                        $this->pagedata['url'] = $url;
                        $this->pagedata['account_id'] = $member_id;
                        $this->set_tmpl('trust');
                        $this->page('site/reg_bind.html');
                    }else{
                        $this->splash('failed', kernel::base_url(1), app::get('b2c')->_('登录失败，参数错误！'));
                    }
                }else{
                    $this->splash('success', $url, app::get('b2c')->_('登录成功'));
                }
            }else{
                $this->splash('failed', kernel::base_url(1), app::get('b2c')->_('登录失败，参数错误！'));
            }
            // end
        }else{
            $this->splash('failed',kernel::base_url(1),app::get('b2c')->_('登录失败，参数错误！'));
        }
    }

    //信任登录绑定账号 
    function reg_bind(){
        $back_url = null;
        $url = $_POST['url'];
        $member_model = app::get('b2c')->model('members');
        $objRepass = app::get('b2c')->model('member_pwdlog');

        //验证绑定账号项
        if(!$member_model->reg_bind_validate($_POST, $msg)){
            $this->splash('failed', $back_url, $msg, '', '', true);
        }

        //验证普通图片验证码
        if($_POST['reg_type'] == 'email' || $_POST['reg_type'] == 'username'){
            $valideCode = app::get('b2c')->getConf('site.register_valide');
            if($valideCode == 'true'){
                if(!base_vcode::verify('LOGINVCODE', intval($_POST['signupverifycode']))){
                    $this->splash('failed', $back_url, app::get('b2c')->_('验证码填写错误'), '', '', true);
                }
            }
        }

        //保存绑定账户的密码
        $use_pass_data['password'] = $_POST['pam_account']['login_password'];
        $use_pass_data['secret'] = $_POST['secret'];
        if($objRepass->rePass($use_pass_data)){
            unset($_POST['pam_account']['login_password']);
        }else{
            $_POST['pam_account']['login_password'] = md5($_POST['pam_account']['login_password']);
        }

//        $_POST['pam_account']['login_name'] = strtolower($_POST['pam_account']['login_name']);
        $_POST['contact']['email'] = htmlspecialchars($_POST['contact']['email']);

		//同步到ucenter yindingsheng
        if( $member_object = kernel::service("uc_user_edit")) {
            $uc_data['password'] = $use_pass_data['password'];
            $uc_data['contact']['email'] = $_POST['contact']['email'];
			$uc_data['contact']['phone']['mobile'] = $_POST['contact']['phone']['mobile'];
            $uc_data['member_id'] = $_POST['member_id'];

            if(!$member_object->uc_user_edit($uc_data,true)){
                $this->splash('failed',$url , app::get('b2c')->_('提交失败'),'','',true);
            }
        }
        //同步到ucenter yindingsheng

        //保存绑定账户项
        if($result = app::get('b2c')->model('members')->save($_POST)){
            $obj_auth = app::get('pam')->model('auth');
            $rs = $obj_auth->update(array('has_bind' => $_POST['has_bind']), array('account_id' => $_POST['member_id']));   //修改账户绑定状态

            if($rs){
                $this->splash('success', $url, app::get('b2c')->_('绑定成功'), '', '', true);
            }else{
                $this->splash('failed', kernel::base_url(1), app::get('b2c')->_('绑定失败，请重新绑定！'), '', '', true);
            }
        }else{
            $this->splash('failed', kernel::base_url(1), app::get('b2c')->_('绑定失败，请重新绑定！'), '', '', true);
        }
    }
    // end

    function gender($gender){
        if($gender == '0'){
            return '2';
        }elseif($gender == '2'){
            return '0';
        }else{
            return $gender;
        }
    }
}
?>
