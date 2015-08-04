<?php

 

/** 
 * 后台桌面首页边上的面板
 * controller class
 */
class b2c_sidepanel_member extends desktop_controller{

    function __construct($app){
        $this->tag_type = 'members';
        $this->app = $app;
    }
}
