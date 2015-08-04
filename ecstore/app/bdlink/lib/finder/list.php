<?php

 
class bdlink_finder_list{
    var $column_control = '操作';
    
    public function __construct($app) {
        $this->app = $app;
    }
    
    function column_control($row){
        return '<a href="index.php?app=bdlink&ctl=clink&act=edit&id='.$row['id'].'" >'.app::get('bdlink')->_('编辑').'</a>';
    }
    
}
