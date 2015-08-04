<?php


class desktop_application_permission extends base_application_prototype_xml{

    var $xml='desktop.xml';
    var $xsd='desktop_content';
    var $path = 'permissions/permission';

    function current(){
        $this->current = $this->iterator()->current();
        $this->key = $this->current['id'];
        return $this;
    }

    function row(){
        $row = array(
            'menu_type' => $this->content_typename(),
            'app_id'=>$this->target_app->app_id,
                );
        $row['menu_title'] = $this->current['value'];
        $row['display'] =  $this->current['display'];
        $row['parent'] =  $this->current['parent'];
        $access_opct = array(
            'show' => $this->current['show'],
            'save' => $this->current['save'],
                );
        $row['addon'] = serialize($access_opct);
        $row['permission'] = $this->current['id'];
        $row['workground'] = $this->current['workground'];
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
