<?php
 

class b2c_ctl_site_mptimedbuy extends b2c_frontpage{

    function __construct($app){
        parent::__construct($app);
        $seo = app::get('site')->model('seo');
        $info = $seo->dump(array('ctl'=>'site_mptimedbuy','act'=>'index'),'param');
        $shopname = $app->getConf('system.shopname');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_($info['param']['seo_title']);
            $this->keywords = app::get('b2c')->_($info['param']['seo_keywords']);
            $this->description = app::get('b2c')->_($info['param']['seo_content']);
        }
    }

    function index(){
        $this->set_tmpl('mp_timedbuy');
        $this->page('site/mptimedbuy/index.html');
    }

}
