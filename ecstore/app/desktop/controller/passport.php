<?php


class desktop_ctl_passport extends desktop_controller{

    var $login_times_error=3;


    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
		/** 登录之前的预先验证 **/
		$obj_services = kernel::servicelist('app_pre_auth_use');
		foreach ($obj_services as $obj){
			if (method_exists($obj, 'pre_auth_uses') && method_exists($obj, 'login_verify')){
				$this->pagedata['desktop_login_verify'] = $obj->login_verify();
			}
		}
		/** end **/

		//检查证书是否合法,从而判定产品功能是否可用。比如b2c功能
		$certCheckObj = kernel::service("product_soft_certcheck");
		if(is_object($certCheckObj) && method_exists($certCheckObj,"check"))
		$certCheckObj->check();

        //在登陆页面时，验证码之后,可实现的servicelist
		$present = kernel::servicelist("passport_index_present");
        foreach ($present as $item)
        {
		    if(is_object($item) && method_exists($item,"handle"))
		    $item->handle($this);
        }

        $auth = pam_auth::instance(pam_account::get_account_type($this->app->app_id));
        $auth->set_appid($this->app->app_id);
        $auth->set_redirect_url($_GET['url']);
      	$this->pagedata['desktop_url'] = kernel::router()->app->base_url(1);
		$this->pagedata['cross_call_url'] =base64_encode( kernel::router()->app->base_url(1).
		'index.php?ctl=passport&act=cross_call'
		);

		$conf = base_setup_config::deploy_info();
        foreach(kernel::servicelist('passport') as $k=>$passport){
            if($auth->is_module_valid($k,$this->app->app_id)){
                $this->pagedata['passports'][] = array(
                        'name'=>$auth->get_name($k)?$auth->get_name($k):$passport->get_name(),
                        'html'=>$passport->get_login_form($auth,'desktop','basic-login.html',$pagedata),
                    );
            }
        }
		$this->pagedata['product_key'] = $conf['product_key'];
        $this->display('login.html');
    }

    function gen_vcode(){
        $vcode = kernel::single('base_vcode');
        $vcode->length(4);
        $vcode->verify_key($this->app->app_id);
        $vcode->display();
    }

	function cross_call(){
		header('Content-Type: text/html;charset=utf-8');
		echo '<script>'.str_replace('top.', 'parent.parent.',base64_decode($_REQUEST['script'])).'</script>';
	}

    function logout($backurl='index.php'){
		$this->begin('javascript:Cookie.dispose("basicloginform_password");Cookie.dispose("basicloginform_autologin");
					   location="'.kernel::router()->app->base_url(1).'"');
        $this->user->login();
        $this->user->logout();
        $auth = pam_auth::instance(pam_account::get_account_type($this->app->app_id));
        foreach(kernel::servicelist('passport') as $k=>$passport){
    	  if($auth->is_module_valid($k,$this->app->app_id))
            $passport->loginout($auth,$backurl);
        }
        kernel::single('base_session')->destory();
		$this->end('true',app::get('desktop')->_('已成功退出系统,正在转向...'));
        /* $this->redirect('');*/

    }


}
