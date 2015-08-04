<?php

 

class desktop_ctl_sms extends desktop_controller{
    var $workground = 'desktop_ctl_system';
    
     public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    
    function setting(){
        #print_r($this->app->getConf('email.config.sendway'));exit;
        $this->pagedata['options'] = $this->getOptions();
        $this->display('sms/config.html');
    }
    
     function getOptions(){
        return array(
			'wsdl'=>array('label'=>app::get('desktop')->_('接口地址'),'type'=>'input','value'=>$this->app->getConf('sms.config.wsdl')),
            'username'=>array('label'=>app::get('desktop')->_('用户名'),'type'=>'input','value'=>$this->app->getConf('sms.config.username')),
            'password'=>array('label'=>app::get('desktop')->_('密码'),'type'=>'input','value'=>$this->app->getConf('sms.config.password')),
			'pubkey'=>array('label'=>app::get('desktop')->_('密钥'),'type'=>'input','value'=>$this->app->getConf('sms.config.pubkey')),
        );
    }
    
    function saveCfg(){
       # $this->begin('index.php?app=desktop&ctl=email&act=setting');
       $this->begin();
           foreach($_POST['config'] as $key=>$value){
            $this->app->setConf('sms.config.'.$key,$value);
        }
        $this->end(true,app::get('desktop')->_('配置保存成功'));
    }
    
    
}
?>
