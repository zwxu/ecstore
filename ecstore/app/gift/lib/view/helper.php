<?php

 
class gift_view_helper{

    function __construct($app){
        $this->app = $app;
    }
    /**
     * 获取菜单HTML
     *
     * @param array $params 模板中要替换的键值组
     * @param object &$smarty 模板解析类
     * @return string 
     */
    function function_goodsmenu($params,&$smarty){
        $vars = $smarty->pagedata;
        $smarty->pagedata = $params;
        $html = $smarty->fetch('site/product/menu.html',app::get('gift')->app_id);
        $smarty->pagedata = $vars;
        return $html;
    }

}
