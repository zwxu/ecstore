<?php

 



class gift_mdl_member_lv extends b2c_mdl_member_lv {
    var $defaultOrder = array('member_lv_id',' DESC');
    
    
    public function get_schema(){
        $this->app = app::get('b2c');
        $columns = parent::get_schema();
        return $columns;
    }

    
    public function table_name($real=false){
        $app_id = $this->app->app_id;
        $table_name = substr(get_parent_class($this),strlen($app_id)+5);
        if($real){
            return kernel::database()->prefix.$this->app->app_id.'_'.$table_name;
        }else{
            return $table_name;
        }
    }

}