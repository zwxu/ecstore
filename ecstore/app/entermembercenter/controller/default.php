<?php

 
class entermembercenter_ctl_default extends entermembercenter_controller{
    
    public function __construct($app){
        kernel::set_online(false);
        if(kernel::single('base_setup_lock')->lockfile_exists()){
            if(!kernel::single('base_setup_lock')->check_lock_code()){
                $this->lock();
            }
        }
        parent::__construct($app);
        define(LOG_TYPE, 3);
    }    
	
	public function active(){
		$this->pagedata['conf'] = base_setup_config::deploy_info();
		$this->pagedata['callback_ur'] = base64_encode(kernel::base_url(1).'/index.php/entermembercenter/default/success');
		$this->pagedata['enterprise_url'] = SHOP_USER_ENTERPRISE;
		$output = $this->fetch('installer-active.html');
		echo str_replace('%BASE_URL%',kernel::base_url(1),$output);
	}
	
	public function success()
	{
		/** 获取证书，企业号的验证 **/
		$active_url = kernel::base_url(1).'/index.php/entermembercenter/default/active';
		if(($_GET['ent_id'] && $_GET['ent_ac'] &&  $_GET['ent_sign'] && $_GET['ent_email'])){
			//判断数据是否是中心过来的
			if(md5($_GET['ent_id'] . $_GET['ent_ac'] . 'ShopEXUser')==$_GET['ent_sign']){
				//检测企业帐号是否正确
				base_enterprise::set_version();
				base_enterprise::set_token();
				if (!base_enterprise::is_valid('json',$_GET['ent_id'])){
					header("Content-type: text/html; charset=utf-8");
					header('Location:'.$active_url);exit;
				}else{
					$arr_enterprise = array(
						'ent_id'=>$_GET['ent_id'],
						'ent_ac'=>$_GET['ent_ac'],
						'ent_email'=>$_GET['ent_email'],
					);
					base_enterprise::set_enterprise_info($arr_enterprise);
					if (!base_enterprise::ent_id() || !base_enterprise::ent_email() || !base_enterprise::ent_ac()){
						header("Content-type: text/html; charset=utf-8");
						header('Location:'.$active_url);exit;
					}
					base_certificate::register();
					if (base_certificate::certi_id()&&base_certificate::token()){
						$this->get_active_node_id();
					}
				}
			}else{
				// 出现异常的情况
				header("Content-type: text/html; charset=utf-8");
				header('Location:'.$active_url);exit;
			}
		}else{
			header("Content-type: text/html; charset=utf-8");
			header('Location:'.$active_url);exit;
		}
		$success_url = kernel::base_url(1).'/index.php/setup/default/success';
		header("Content-type: text/html; charset=utf-8");
		header('Location:'.$success_url);exit;
	}
	
	private function get_active_node_id(){
        kernel::set_online(true);
       
        if(file_exists(ROOT_DIR.'/config/config.php')){
			$obj_buildin = kernel::single('base_shell_buildin');
			$obj_buildin->command_active_node_id('ceti_node_id');
        }else{
            echo 'config file?';
        }
    }
}
