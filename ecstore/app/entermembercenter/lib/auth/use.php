<?php

 
class entermembercenter_auth_use
{
	public function pre_auth_uses()
	{		
		if(!base_enterprise::ent_id() || !base_enterprise::ent_ac() || !base_enterprise::ent_email()){
			//判断数据是否是中心过来的
			return false;
		}else{
			base_enterprise::set_version();
			base_enterprise::set_token();			
			if (!base_enterprise::is_valid('json',base_enterprise::ent_id())){
				return false;
			}
		}
		
		return true;
	}
	
	public function pre_ceti_use()
	{
		if(!base_certificate::certi_id() || !base_certificate::token()){
			return false;
		}
		
		return true;
	}
	
	public function login_verify()
	{
		if (!$this->pre_auth_uses()&&!$this->pre_ceti_use()){
			$active_url = kernel::router()->app->base_url(1).'/index.php?app=entermembercenter&ctl=register';
			header('Location: '.$active_url);exit;
		}elseif (!$this->pre_auth_uses()){
			$render = kernel::single('base_render');
			$render->pagedata['enterprise_url'] = SHOP_USER_ENTERPRISE;
			$render->pagedata['callback_url'] = base64_encode(kernel::router()->app->base_url(1).'index.php?app=entermembercenter&ctl=register&act=active');
			return $render->fetch('login_verify.html', 'entermembercenter');
		}else{		
			return '';
		}
	}
	
	public function active_top_html()
	{
		/** 获取证书，企业号的验证 **/
		$active_url = kernel::router()->app->base_url(1).'/index.php?app=entermembercenter&ctl=register';
		$render = kernel::single('base_render');
		$render->pagedata['active_url'] = $active_url;
		return $render->fetch('desktop_active_top.html', 'entermembercenter');
	}
}