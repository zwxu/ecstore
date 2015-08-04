<?php

 
class desktop_application_workground extends base_application_prototype_xml {

    var $xml='desktop.xml';
    var $xsd='desktop_content';
    var $path = 'workground';

    function current(){
        $this->current = $this->iterator()->current();
        $this->current['action'] = $this->current['action']?$this->current['action']:'index';
        $this->key = $this->current['id'];
        return $this;
    }

    function row(){
        $row = array(
            'menu_type' => $this->content_typename(),
            'app_id'=>$this->target_app->app_id,
            'workground'=>$this->current['id'],
                );
        $this->current['action'] = $this->current['action']?$this->current['action']:'index';
        #$row['menu_path'] = "app={$this->target_app->app_id}&ctl={$this->current['controller']}&act={$this->current['action']}";
        if($this->current['controller']&&$this->current['action']&&$this->current['app']){
            $row['menu_path'] = "app=".$this->current['app'].'&ctl='.$this->current['controller'].'&act='.$this->current['action'];
        }else{
            $row['menu_path'] = '';
        }
        $row['menu_title'] = $this->current['name'];
        $row['menu_order'] = $this->current['order'];
        $row['display'] = $this->current['display']?$this->current['dispaly']:true;
        $row['addon'] = $this->current['controller'];
        return $row;
    }
    
    function install(){
        kernel::log('Installing '.$this->content_typename().' '.$this->current['id']);
        $row = app::get('desktop')->model('menus')->dump(array('menu_type'=>'workground','workground'=>$this->current['id']));
        if($row['menu_id']){
            $data = $this->row();
            $data['menu_id'] = $row['menu_id'];
            $data['app_id'] = $row['app_id'];
            app::get('desktop')->model('menus')->save($data);
            return $row['menu_id'];
        }else{
            return app::get('desktop')->model('menus')->insert($this->row());
        }
    }
    
    function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }
        app::get('desktop')->model('menus')->delete(array(
            'app_id'=>$app_id,'menu_type' => $this->content_typename()));
    }

}
