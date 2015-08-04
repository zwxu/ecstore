<?php

class timedbuy_member_menuextends
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function get_extends_menu(&$arr_menus, $args=array())
    {
        $arr_extends = array();
		//echo '<pre>';print_r($arr_menus);exit;
        //if($this->app->getConf('site.is_open_seller'))
        {
            $arr_extends = array(
                
            );
            $arr_menus = array_merge($arr_menus, $arr_extends);
            return true;
        }
        return false;
    }
}