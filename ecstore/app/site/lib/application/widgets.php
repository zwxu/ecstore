<?php

 

class site_application_widgets extends base_application_prototype_filepath 
{
    var $path = 'widgets';

    public function install() 
    {
        if(is_dir($this->getPathname())){
            $widgets_name = basename($this->getPathname());
            $widgets_app = $this->target_app->app_id;
            kernel::log('Installing Widgets '. $widgets_app . ':' . $widgets_name);
            $data['app'] = $widgets_app;
            $data['name'] = $widgets_name;
            app::get('site')->model('widgets')->insert($data);
        }
    }//End Function
    
    function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }
        app::get('site')->model('widgets')->delete(array('app'=>$app_id));
    }
    
}//End Class
