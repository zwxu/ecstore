<?php

 class cellphone_finder_phone{
 
    function __construct($app){
        $this->app = $app;
        $this->router = app::get('desktop')->router();
        $this->phone = $this->app->model('phone');
    }//End


	var $column_edit = '操作';
	public $column_edit_width = 110;

    function column_edit($row){


        $row = $this->phone->getList('*',array('phone_id'=>$row['phone_id']));
        $row = $row[0];
		$html = '';
		if($row['disabled']=='false'){
			
	    $html .= '<a href="'. $this->router->gen_url( array('app'=>'cellphone','ctl'=>'admin_phone','act'=>'edit','phone_id'=>$row['phone_id']) ) .'" >'.app::get('cellphone')->_('编辑').'</a>&nbsp;&nbsp;';
			
		if($row['is_active']=='false'){
				$html .= '<a href="'. $this->router->gen_url( array('app'=>'cellphone','ctl'=>'admin_phone','act'=>'openActivity','phone_id'=>$row['phone_id']) ) .'" >'.app::get('cellphone')->_('开启').'</a>&nbsp;&nbsp;';
		}else{
				$html .= '<a href="'. $this->router->gen_url( array('app'=>'cellphone','ctl'=>'admin_phone','act'=>'closeActivity','phone_id'=>$row['phone_id']) ) .'" >'.app::get('cellphone')->_('关闭').'</a>&nbsp;&nbsp;';
			}
	  }
        return $html;

    }

 }