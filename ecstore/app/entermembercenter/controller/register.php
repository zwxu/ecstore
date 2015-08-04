<?php

 
class entermembercenter_ctl_register extends base_controller{

    function index()
	{

        $this->pagedata['conf'] = base_setup_config::deploy_info();
		$this->pagedata['enterprise_url'] = SHOP_USER_ENTERPRISE;
		$this->pagedata['callback_url'] = base64_encode(kernel::router()->app->base_url(1).'index.php?app=entermembercenter&ctl=register&act=active');
		$output = $this->fetch('register.html');
		echo str_replace('%BASE_URL%',kernel::base_url(1),$output);
    }
	
	function active()
	{
		if(($_GET['ent_id'] && $_GET['ent_ac'] &&  $_GET['ent_sign'] && $_GET['ent_email'])){
			//判断数据是否是中心过来的
			if(md5($_GET['ent_id'] . $_GET['ent_ac'] . 'ShopEXUser')==$_GET['ent_sign']){
				//检测企业帐号是否正确
				base_enterprise::set_version();
				base_enterprise::set_token();
				if (!base_enterprise::is_valid('json',$_GET['ent_id'])){
					header("Content-type: text/html; charset=utf-8");
					$active_url = kernel::router()->app->base_url(1).'/index.php?app=entermembercenter&ctl=register';
					header('Location:'.$active_url);exit;
				}else{
					$arr_enterprise = array(
						'ent_id'=>$_GET['ent_id'],
						'ent_ac'=>$_GET['ent_ac'],
						'ent_email'=>$_GET['ent_email'],
					);
					base_enterprise::set_enterprise_info($arr_enterprise);
					if (!base_certificate::certi_id()|| !base_certificate::token()){
						base_certificate::register();
					}
					if (!base_shopnode::node_id()&&base_certificate::certi_id()&&base_certificate::token()){
						$obj_buildin = kernel::single('base_shell_buildin');
						$obj_buildin->command_active_node_id('ceti_node_id');
					}
				}
			}
		}else{
			header("Content-type: text/html; charset=utf-8");
			$active_url = kernel::router()->app->base_url(1).'/index.php?app=entermembercenter&ctl=register';
			header('Location:'.$active_url);exit;
		}
		$url = kernel::router()->gen_url(array(),1);
		$url = base64_encode($url);
		$login_html = 'index.php?ctl=passport&act=index&url='.$url;
		header("Content-type: text/html; charset=utf-8");
		header('Location:'.$login_html);exit;
	}
}

