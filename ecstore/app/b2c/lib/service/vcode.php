<?php
class b2c_service_vcode{
    
    function __construct($app){
        $this->app = $app;
    }
    
    function status(){
        if(app::get('b2c')->getConf('site.login_valide') == 'false'){
            if($_SESSION['error_count'][$this->app->app_id] >= 3) return true;
        }
        return app::get('b2c')->getConf('site.login_valide') == 'true' ? true : false;
    }
}
?>