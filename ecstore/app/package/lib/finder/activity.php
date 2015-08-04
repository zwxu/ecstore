<?php

class package_finder_activity{
    public $column_control = '操作';
    
    function __construct($app){
        $this->app = $app;
        $this->router = app::get('desktop')->router();
    }//End
    
    public function column_control($row){
        $returnValue = '<a href="'. $this->router->gen_url( array('app'=>'package','ctl'=>'admin_activity','act'=>'edit','act_id'=>$row['act_id']) ) .'" >'.app::get('timedbuy')->_('编辑').'</a>&nbsp;&nbsp;';
        return $returnValue;
    }

}