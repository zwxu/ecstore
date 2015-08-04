<?php

 
class desktop_application_widgets extends base_application_prototype_xml{

    var $xml='desktop.xml';
    var $xsd='desktop_content';
    var $path = 'widgets';

    function controller(){
        return $this->app->controller($this->key);
    }

    function current(){
        $this->current = $this->iterator()->current();
        $this->key = $this->current['id'];
        return $this;
    }

    function row(){
        $row = array(
                    'menu_type' => $this->content_typename(),
                    'app_id'=>$this->target_app->app_id,
                    'menu_order'=>$this->current['order'],
                );
        $row['menu_title'] = $this->current['value'];
        $row['addon'] = $this->current['id'];
        return $row;
    }
    
    function install(){    
        kernel::log('Installing '.$this->content_typename().' '.$this->key());
        return app::get('desktop')->model('menus')->insert($this->row());
    }
    
    function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }
        app::get('desktop')->model('menus')->delete(array(
            'app_id'=>$app_id,'menu_type' => $this->content_typename()));
    }

}
