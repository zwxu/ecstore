<?php
class cellphone_ctl_admin_aboutus extends desktop_controller
{
    var $workground = 'cellphone.wrokground.mobile';

    public function __construct($app)
    {
        parent::__construct($app);
        $this->app = $app;
		$this->router = app::get('desktop')->router();
    }

  
	public function  getcopyright(){
    $item['content'] =  $this->app->getConf('cellphone.appinfo.copyright');
    $item['infotype'] = 'copyright';
	$this->pagedata['item'] = $item;
	$this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_aboutus','act'=>'getcopyright'));

	$this->page('admin/appinfo.html');
	
	}
	public function getlicense(){
	$item['content'] =  $this->app->getConf('cellphone.appinfo.license');
    $item['infotype'] = 'license';
	$this->pagedata['item'] = $item;
	$this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_aboutus','act'=>'getlicense'));

	$this->page('admin/appinfo.html');
	
	}
    public function getdescription(){
	$item['content'] =  $this->app->getConf('cellphone.appinfo.description');
    $item['infotype'] = 'description';
	$this->pagedata['item'] = $item;
	$this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_aboutus','act'=>'getdescription'));

	$this->page('admin/appinfo.html');
	
	}

	public function toAdd(){
	
	 if($_POST['infotype'] == 'copyright'){
	 $this->app->setConf('cellphone.appinfo.copyright',$_POST['content']);
	 $this->getcopyright();
	 }
	 if($_POST['infotype'] == 'license'){
	  $this->app->setConf('cellphone.appinfo.license',$_POST['content']);
	  $this->getlicense();
	 }
	 if($_POST['infotype'] == 'description'){
	  $this->app->setConf('cellphone.appinfo.description',$_POST['content']);
	  $this->getdescription();
	 }
	
	 
	}



}