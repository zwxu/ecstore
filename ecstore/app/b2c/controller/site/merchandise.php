<?php
 

class b2c_ctl_site_merchandise extends b2c_frontpage{

    var $seoTag=array('shopname','merchandise');

    function __construct($app){
        parent::__construct($app);
        $shopname = $app->getConf('system.shopname');
        $this->shopname = $shopname;
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('日用百货').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('日用百货').'_'.$shopname;
            $this->description = app::get('b2c')->_('日用百货').'_'.$shopname;
        }

    }

    public function index(){
         
        $this->set_tmpl_file('merchandise-index.html');
        $this->page('site/merchandise/index.html');
    }

    
}
