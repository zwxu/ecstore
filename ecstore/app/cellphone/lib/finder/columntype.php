<?php

class cellphone_finder_columntype{
 
    function __construct($app){
        $this->app = $app;
        $this->router = app::get('desktop')->router();
        //$this->banner = $this->app->model('banner');
    }//End
    

 	var $column_edit = '编辑';
	//public $column_edit_width = 110;

     function column_edit($row){
        return '<a href="index.php?app=cellphone&ctl=admin_columntype&act=edit&columntype_id='.$row['columntype_id'].'">编辑</a>';
    }
 
 
 
 
 }

