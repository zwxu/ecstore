<?php
class business_finder_storeroles{
   
    var $column_control = '角色操作';
    function __construct($app){
        $this->app= $app;
        //$this->obj_roles = kernel::single('business_storeroles');
    }


     function column_control($row){
        $render = $this->app->render();
        $render->pagedata['role_id'] = $row['role_id'];
        return $render->fetch('admin/store/href.html');
      }
	
}