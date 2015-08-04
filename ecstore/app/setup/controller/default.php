<?php

 
class setup_ctl_default extends setup_controller{
    
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
    
    public function console(){

        $shell = new base_shell_webproxy;
        $shell->input = $_POST['options'];
        echo "\n";
        $shell->exec_command($_POST['cmd']);
    }
    
    private function lock(){
        header('Content-type: text/html',1,401);
        echo '<h3>Setup Application locked by config/install.lock.php</h3><hr />';
        exit;
    }
    
    public function index(){
		$this->pagedata['conf'] = base_setup_config::deploy_info();
		$this->pagedata['install_bg'] = kernel::base_url(1).'/config/setup_product.jpg';
		$this->pagedata['statics_url'] = $this->app->res_url;
		$this->display('installer-start.html');
    }

    public function process(){
        set_time_limit(0);
        $serverinfo = kernel::single('setup_serverinfo')->run($_POST['installer_check']);
		if($serverinfo['allow_install'] != 1){
			$this->pagedata['serverinfo'] = $serverinfo;
		}
        $this->pagedata['conf'] = base_setup_config::deploy_info();
        $install_queue = $this->install_queue($this->pagedata['conf']);
        
        $install_options = array();
        if(is_array($install_queue)){
            foreach($install_queue as $app_id=>$app_info){
                $option = app::get($app_id)->runtask('install_options');
                if(is_array($option) && count($option)>=1){
                    $install_options[$app_id] = $option;
                }
            }
        }
        $this->pagedata['install_options'] = &$install_options;
		$this->pagedata['install_demodata_options'] = $this->install_demodata_options($this->pagedata['conf']);
		
		$this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['apps'] = &$install_queue;
		if ($this->pagedata['conf']['demodatas']){
			$this->pagedata['demodata'] = array(
				'install'=>'true',
				'name'=>'demodata',
				'description'=>'demodata',
			);
		}else{
			$this->pagedata['demodata'] = 'false';
		}
		
		if (isset($this->pagedata['conf']['active_ceti'])&&$this->pagedata['conf']['active_ceti'])
			$this->pagedata['success_page'] = $this->pagedata['conf']['active_ceti']['active_ceti_url'];
		else
			$this->pagedata['success_page'] = 'success';
			
        if($_GET['console']){
            $output = $this->fetch('console.html');
        }else{
            $output = $this->fetch('installer.html');
        }
		
        echo str_replace('%BASE_URL%',kernel::base_url(1),$output);
		
    }
    
    public function success(){
		$this->pagedata['conf'] = base_setup_config::deploy_info();
		$this->pagedata['install_bg'] = kernel::base_url(1).'/config/setup_product.jpg';
		$output = $this->fetch('installer-success.html');
		echo str_replace('%BASE_URL%',kernel::base_url(1),$output);
    }
	
	public function active(){
		$this->pagedata['conf'] = base_setup_config::deploy_info();
		$this->pagedata['callback_ur'] = base64_encode(kernel::base_url(1).'/index.php/setup/default/success');
		$this->pagedata['enterprise_url'] = SHOP_USER_ENTERPRISE;
		$output = $this->fetch('installer-active.html');
		echo str_replace('%BASE_URL%',kernel::base_url(1),$output);
	}
    
    private function write_lock_code(){
        kernel::single('base_setup_lock')->write_lock_file();
    }
    
    public function install_queue($config=null){
        $config = $config?$config:base_setup_config::deploy_info();      
        
        foreach($config['package']['app'] as $k=>$app){
            $applist[] = $app['id'];
        }
                
        return kernel::single('base_application_manage')->install_queue($applist);
    }
	
	/**
	 * 得到deploy部署的demo data选择项目 
	 * @param null
	 * @return array
	 */
	public function install_demodata_options($config=null)
	{
		$config = $config?$config:base_setup_config::deploy_info(); 
		
		$install_options = array();
		$tmp_arr_options = array();		
		foreach ((array)$config['demodatas'] as $key=>$demo_data){			
			foreach ((array)$demo_data['options'] as $arr_options){
				$tmp_arr_options[$arr_options['key']] = $arr_options['value'];
			}
			unset($demo_data['options']);
			$demo_data['options'] = $tmp_arr_options;
			$install_options[$key] = $demo_data;			
		}
		
		return $install_options;
	}

    public function initenv(){
        
        $this->write_lock_code();
        
        header('Content-type: text/plain; charset=UTF-8');
        
        $install_queue = $this->install_queue();
        foreach($install_queue as $app_id=>$app_info){
            if(false === app::get($app_id)->runtask('checkenv',$_POST['options'][$app_id])){
                $error = true;
            }
        }
        if($error){
            echo 'check env failed';
        }else{
            echo 'config init ok.';            
        }
    }
    
    public function install_app(){
        kernel::set_online(true);
        $app = $_GET['app'];
        if(file_exists(ROOT_DIR.'/config/config.php')){
            $shell = new base_shell_webproxy;
            $shell->input = $_POST['options'];
            $shell->exec_command('install -r '.$app);
        }else{
            echo 'config file?';
        }
    }
	
	public function install_demodata(){
        kernel::set_online(true);
       
        if(file_exists(ROOT_DIR.'/config/config.php')){
            $shell = new base_shell_webproxy;
            $shell->input = $_POST['options'];
            $shell->exec_command('install_demodata -r demodata');
        }else{
            echo 'config file?';
        }
    }

    public function setuptools() 
    {
        $app = addslashes($_GET['app']);
        $method = addslashes($_GET['method']);
        if(empty($app) || empty($method))   die('call error');
        $data = app::get($app)->runtask($method, $_POST['options']);
        header('Content-type: application/json; charset=UTF-8');
        echo json_encode($data);
    }//End Function

}
