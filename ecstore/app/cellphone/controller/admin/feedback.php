<?php  
class cellphone_ctl_admin_feedback extends desktop_controller
{
    var $workground = 'cellphone.wrokground.mobile';

    public function __construct($app)
    {
        parent::__construct($app);
        $this->app = $app;
        $this->router = app::get('desktop')->router();
    }

    public function index(){
       $this->finder('cellphone_mdl_feedback',array(
            'title'=>app::get('b2c')->_('反馈信息'),
            ));
    }
    
    public function  getusinfo(){
        $item['content'] =  $this->app->getConf('cellphone.appinfo.usinfo');
        $item['infotype'] = 'usinfo';
        $this->pagedata['item'] = $item;
        $this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_feedback','act'=>'getusinfo'));

        $this->page('admin/appinfo.html');
	
	}
    
	public function getversioninfo(){
        $item['content'] =  $this->app->getConf('cellphone.appinfo.versioninfo');
        $item['infotype'] = 'versioninfo';
        $this->pagedata['item'] = $item;
        $this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_feedback','act'=>'getversioninfo'));

        $this->page('admin/appinfo.html');
	}
    
	public function toAdd(){
	
         if($_POST['infotype'] == 'usinfo'){
             $this->app->setConf('cellphone.appinfo.usinfo',$_POST['content']);
             $this->getusinfo();
         }
         if($_POST['infotype'] == 'versioninfo'){
              $this->app->setConf('cellphone.appinfo.versioninfo',$_POST['content']);
              $this->getversioninfo();
         }
	
	}
}