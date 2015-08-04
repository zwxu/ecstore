<?php
 

class b2c_ctl_site_message extends b2c_frontpage{

	function __construct($app){
		parent::__construct($app);
		if($this->app->getConf('system.message.open') !='on'){
			 $this->splash('failed',kernel::base_url(1),app::get('b2c')->_('未开启商店留言功能！'));
		}
		$shopname = $app->getConf('system.shopname');
		$this->shopname = $shopname;
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('商品页').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('商品页_').'_'.$shopname;
            $this->description = app::get('b2c')->_('商品页_').'_'.$shopname;
        }
	}

    function index($nPage=1){
        $this->path[] = array('title'=>app::get('b2c')->_('商店留言'),'link'=>'a');
        $GLOBALS['runtime']['path'] = $this->path;
        $objMessage = kernel::single('b2c_message_message');
        if(!isset($_COOKIE['UNAME'])){
           $this->pagedata['nomember'] = 'on';
        }
        $aData = $objMessage->getList('*',array('for_comment_id' => 0,'display' => 'true'));
        foreach($aData as $key=>$reply){
            $aData[$key]['reply'] = $objMessage->get_reply($reply['comment_id']);
        }
        if($this->check_login()){
            $this->pagedata['login'] = 'YES';
        }
        else{
            $this->pagedata['login'] = 'NO';
        }
        $this->pagedata['msg'] = $aData;
        $this->pagedata['msgshow'] = $this->app->getConf('comment.verifyCode.discuss')? $this->app->getConf('comment.verifyCode.discuss'):'on';
        $this->pagedata['message_open'] = $this->app->getConf('system.message.open');
        $power = $this->app->getConf('system.message.power') ? $this->app->getConf('system.message.power') : 'member';
        $siteMember = $this->get_current_member();
        $this->site_member_lv_id = $siteMember['member_lv'];
        if(!$this->site_member_lv_id && $power == 'member'){
        	  $this->pagedata['msg_status'] = true;
              $this->pagedata['msg_message'] = '请<a href="'.app::get('site')->router()->gen_url(array('app' => 'b2c', 'ctl' => 'site_passport', 'act' => 'login', 'arg' =>'')).'">登陆</a>后再留言<br>如果您不是会员请<a href="'.app::get('site')->router()->gen_url(array('app' => 'b2c','ctl' => 'site_passport', 'act' => 'signup', 'arg' =>'')).'">注册</a>!';
        }
        $this->setSeo('site_message','index',$this->prepareSeoData($this->pagedata));
        $this->page('site/message/index.html');

    }

    function prepareSeoData($data){
        return array(
            'shop_name'=>$this->shopname,
        );
    }

    function sendMsgToOpt(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_message','act'=>'index'));
        $msgshow = $this->app->getConf('comment.verifyCode.discuss')? $this->app->getConf('comment.verifyCode.discuss'):'on';
        $power = $this->app->getConf('system.message.power') ? $this->app->getConf('system.message.power') : 'member';
        if(!$this->check_login() && $power == 'member'){
        	  $this->splash('failed',$url,app::get('b2c')->_('仅注册会员才可发表'),'','',true);
        }
        if($msgshow === "on"){
            if(!base_vcode::verify('MESSAGEVCODE',intval($_POST['verifyCode']))){
                 $this->splash('failed',$url,app::get('b2c')->_('验证码填写错误'),'','',true);
            }
        }
        $display = $this->app->getConf('comment.display.discuss') ? $this->app->getConf('comment.display.discuss'): 'reply';
        if($display== "soon"){
            $_POST['display'] = "true";
        }
        else{
            $_POST['display'] = "false";
        }
        $member_data = $this->get_current_member();
        $objMessage = kernel::single('b2c_message_message');
        $_POST['ip'] = $_SERVER["REMOTE_ADDR"];
         if($objMessage->send($_POST,$member_data)){
             $this->splash('success',$url,app::get('b2c')->_('发表成功！'),'','',true);
         }
         else{
             $this->splash('failed',$url,app::get('b2c')->_('发表失败！'),'','',true);
         }
    }

    function verifyCode(){
        $vcode = kernel::single('base_vcode');
        $vcode->length(4);
        $vcode->verify_key('MESSAGEVCODE');
        $vcode->display();
    }
}
