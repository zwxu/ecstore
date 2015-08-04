<?php

 

/**
 * 后台桌面首页边上的面板
 * controller class
 */
class b2c_sidepanel_order extends desktop_controller{

    function __construct($app){
        $this->tag_type = 'orders';
        $this->app = $app;
    }
}
