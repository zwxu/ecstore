<?php

 
class desktop_router implements base_interface_router{

    function __construct($app){
        $this->app = $app;
    }

    function gen_url($params=array(),$full=false){
        $params = utils::http_build_query($params);
        if($params){
            return $this->app->base_url($full).'index.php?'.$params;
        }else{
            return $this->app->base_url($full);
        }
    }

    function dispatch($query){
        $_GET['ctl'] = $_GET['ctl']?$_GET['ctl']:'default';
        $_GET['act'] = $_GET['act']?$_GET['act']:'index';
        $_GET['app'] = $_GET['app']?$_GET['app']:'desktop';
        $query_args = $_GET['p'];

        $controller = app::get($_GET['app'])->controller($_GET['ctl']);
        $arrMethods = get_class_methods($controller);
        if (in_array($_GET['act'], $arrMethods))
            call_user_func_array(array(&$controller,$_GET['act']),(array)$query_args);
        else
            call_user_func_array(array(&$controller,'index'),(array)$query_args);
    }

}
