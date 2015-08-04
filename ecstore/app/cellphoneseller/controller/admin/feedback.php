<?php  
class cellphoneseller_ctl_admin_feedback extends desktop_controller
{
    var $workground = 'cellphoneseller.wrokground.mobile';

    public function __construct($app)
    {
        parent::__construct($app);
        $this->app = $app;
        $this->router = app::get('desktop')->router();
    }

    public function index(){
       $this->finder('cellphoneseller_mdl_feedback',array(
            'title'=>app::get('b2c')->_('反馈信息'),
            ));
    }
    
    public function  getusinfo(){
        $item['content'] =  $this->app->getConf('cellphoneseller.appinfo.usinfo');
        $item['infotype'] = 'usinfo';
        $this->pagedata['item'] = $item;
        $this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphoneseller','ctl'=>'admin_feedback','act'=>'getusinfo'));

        $this->page('admin/appinfo.html');
	
	}
    
	public function getversioninfo(){
        $item['content'] =  $this->app->getConf('cellphoneseller.appinfo.versioninfo');
        $item['infotype'] = 'versioninfo';
        $this->pagedata['item'] = $item;
        $this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphoneseller','ctl'=>'admin_feedback','act'=>'getversioninfo'));

        $this->page('admin/appinfo.html');
	}
    
	public function toAdd(){
	
         if($_POST['infotype'] == 'usinfo'){
             $this->app->setConf('cellphoneseller.appinfo.usinfo',$_POST['content']);
             $this->getusinfo();
         }
         if($_POST['infotype'] == 'versioninfo'){
              $this->app->setConf('cellphoneseller.appinfo.versioninfo',$_POST['content']);
              $this->getversioninfo();
         }
	
	}
}