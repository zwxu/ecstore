<?php

class logisticstrack_extension_settings{

    function __construct($app){
        $this->app = $app;
    }
    
    function settings(&$arr_settings = array()){
        $arr_ext_settings = array('system.order.tracking');
        $arr_settings[app::get('b2c')->_('其他设置')] = array_merge($arr_settings[app::get('b2c')->_('其他设置')],$arr_ext_settings);
    }
}