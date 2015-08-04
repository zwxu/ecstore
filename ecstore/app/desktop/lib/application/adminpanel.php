<?php

 
class desktop_application_adminpanel extends base_application_prototype_xml{

    var $xml='desktop.xml';
    var $xsd='desktop_content';
    var $path = 'adminpanel';

    function controller(){
        return $this->app->controller($this->key);
    }

    function current(){
        $this->current = $this->iterator()->current();
        $this->current['action'] = $this->current['action']?$this->current['action']:'index';
        $this->key = $this->current['controller'].':'.$this->current['action'];
        return $this;
    }

    function row(){
        $row = array(
                'menu_type' => $this->content_typename(),
                'app_id'=>$this->target_app->app_id,
                'menu_order'=>$this->current['order'],
            );
        $this->current['action'] = $this->current['action']?$this->current['action']:'index';
        $row['menu_path'] = "app={$this->target_app->app_id}&ctl={$this->current['controller']}&act={$this->current['action']}";
        $row['menu_title'] = $this->current['group'].':'.$this->current['value'];
        $row['addon'] = $this->key;
        $row['display'] = $this->current['display']?$this->current['display']:"true";
        $row['permission'] = $this->current['permission'];
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
