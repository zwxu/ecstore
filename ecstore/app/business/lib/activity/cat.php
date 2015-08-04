<?php

class business_activity_cat
{
    function __construct(&$app) 
    {
        $this->app = $app;
        $this->router = app::get('desktop')->router();
    }//En

    public function loadActivityCat(){
        $result = array();
        
        $business_activity = kernel::service('business_activity');
        if($business_activity){
            $result[] = array(
                        'tab_name'=>'限时抢购',
                        'app'=>'timedbuy',
                        'ctl'=>'site_activity',
                        'act'=>'attend'
                    );
        }

        $group_server = kernel::service('groupbuy_group_activity');
        if($group_server){
            $result[] = array(
                        'tab_name'=>'团购活动',
                        'app'=>'groupbuy',
                        'ctl'=>'site_activity',
                        'act'=>'attend'
                    );
        }

        $spike_server = kernel::service('desktop_finder.spike_mdl_activity');
        if($spike_server){
            $result[] = array(
                        'tab_name'=>'秒杀活动',
                        'app'=>'spike',
                        'ctl'=>'site_activity',
                        'act'=>'attend'
                    );
        }
        
        $package_server = kernel::service('package_group_activity');
        if($package_server){
            $result[] = array(
                        'tab_name'=>'捆绑活动',
                        'app'=>'package',
                        'ctl'=>'site_activity',
                        'act'=>'attend',
                    );
        }

        $score_server = kernel::service('desktop_finder.score_mdl_activity');
        if($spike_server){
            $result[] = array(
                        'tab_name'=>'积分换购活动',
                        'app'=>'scorebuy',
                        'ctl'=>'site_activity',
                        'act'=>'attend'
                    );
        }

        return $result;
    }
}