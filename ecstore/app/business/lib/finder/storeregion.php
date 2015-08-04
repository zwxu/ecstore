<?php
class business_finder_storeregion{
   
    function __construct($app){
        $this->app = $app;
    }

	var $column_control = '操作';
    var $column_control_width = 100;

 	function column_control($row){
		
        return '<a href="index.php?app=business&ctl=admin_storeregion&act=edit&region_id='.$row['region_id'].'&finder_id='.$_GET['_finder']['finder_id'].'"  target="blank">'.app::get('business')->_('编辑').'</a>';
    }
    
	
}