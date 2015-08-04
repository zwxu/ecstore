<?php
class business_ctl_site_activity extends b2c_frontpage{
    function __construct($app){
        $this->app = $app;
        $this->router = app::get('site')->router();
        //设置不读缓存 
        $GLOBALS['runtime']['nocache']=microtime();
    }//End

    function attend(){
        $render = $this->app->render();

        //积分换购
        $score_server = kernel::servicelist('scorebuy_score_activity');
        if($score_server){
            $url = $this->router->gen_url(array('app'=>'scorebuy','ctl'=>'site_activity','act'=>'attend'));
        }

        //秒杀
        $spike_server = kernel::servicelist('spike_spike_activity');
        if($spike_server){
            $url = $this->router->gen_url(array('app'=>'spike','ctl'=>'site_activity','act'=>'attend'));
        }

        //团购活动
        $group_server = kernel::servicelist('group_activity');
        if($group_server){
            $url = $this->router->gen_url(array('app'=>'groupbuy','ctl'=>'site_activity','act'=>'attend'));
        }
        //限时抢购
        $business_activity = kernel::servicelist('business_activity');
        if($business_activity){
            $url = $this->router->gen_url(array('app'=>'timedbuy','ctl'=>'site_activity','act'=>'attend'));
        }
        kernel::single('base_controller')->splash('success',$url);
    }

    function myAttend(){
        $render = $this->app->render();

        //积分换购
        $score_server = kernel::servicelist('scorebuy_score_activity');
        if($score_server){
            $url = $this->router->gen_url(array('app'=>'scorebuy','ctl'=>'site_activity','act'=>'myAttend'));
        }

        //秒杀
        $spike_server = kernel::servicelist('spike_spike_activity');
        if($spike_server){
            $url = $this->router->gen_url(array('app'=>'spike','ctl'=>'site_activity','act'=>'myAttend'));
        }

        //团购活动
        $group_server = kernel::servicelist('group_activity');
        if($group_server){
            $url = $this->router->gen_url(array('app'=>'groupbuy','ctl'=>'site_activity','act'=>'myAttend'));
        }
        //限时抢购
        $obj_menu_extends = kernel::servicelist('business_activity');
        if($obj_menu_extends){
            $url = $this->router->gen_url(array('app'=>'timedbuy','ctl'=>'site_activity','act'=>'myAttend'));
        }
        kernel::single('base_controller')->splash( 'success',$url );
    }
}