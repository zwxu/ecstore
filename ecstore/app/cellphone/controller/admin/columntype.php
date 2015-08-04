<?php


 class cellphone_ctl_admin_columntype extends desktop_controller{
     
	 
	 function __construct($app){
        parent::__construct($app);
        $this->router = app::get('desktop')->router();
        $this->_request = kernel::single('base_component_request');
    }


	function index(){
	
	$this->finder('cellphone_mdl_columntype',
		array('actions' =>array(
                  array(
                    'label' => app::get('cellphone')->_('添加类型'),
                    'icon' => 'add.gif',
                    'href' => 'index.php?app=cellphone&ctl=admin_columntype&act=add',
                   // 'target' => "_blank",
                    ),
                        ),
		    	'title'=>'专栏类型列表',    
                'use_buildin_set_tag'=>false,
                'use_buildin_filter'=>false,
                'use_buildin_tagedit'=>false,
                'use_buildin_export'=>false,
                'use_buildin_import'=>false,
                'allow_detail_popup'=>true,
                'use_view_tab'=>false,));
	}


	 function add(){
	 $this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_columntype','act'=>'index'));
	 $this->page('admin/columntype/add.html');
	  }
     

	 function edit(){
	 
	    $this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_columntype','act'=>'index')));
        
        $columntype_id = $this->_request->get_get('columntype_id');
        $columntype = $this->app->model('columntype');
        $data = $columntype->getList('*',array('columntype_id'=>$columntype_id));
		//echo '<pre>';
		//print_r($data);
		//exit;

        $this->pagedata['item'] = $data[0];
		$this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_columntype','act'=>'index'));
        $this->page('admin/columntype/add.html');
        
	
	 }

	 function toAdd(){

     //$data = $this->_request->get_post();;
	   //echo "<pre>";
		// print_r($data);
		// exit;
    
     $this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_columntype','act'=>'index'))); 
	 $data = $this->get_data();
	 $columntype = $this->app->model('columntype');
	 if($data['columntype_id']){
	 $re = $columntype->update($data,array('columntype_id'=>$data['columntype_id']));
	 }
	 else{
	  $re = $columntype->save($data);
	 }
	 if($re){
	 $this->end(true,'保存成功');
	 }
	 else{
	 $this->end(false,'保存失败');
	 }
	  
	 }

	 //
	  public function get_data(){

      $data = $this->_request->get_post();;
      if(!$data['columntype_name']){
	     $this->end(false,'栏目类型不为空');
	  }
	  if(!$data['columntype_createtime']){
	     $this->end(false,'录入时间不为空');
	  }
	  if(!$data['css_type']){
	     $this->end(false,'请选择样式类型');
	  }
	  if(!$data['d_order']){
	     $this->end(false,'排序不能为空');
	  }
	  if($data['d_order']&&!is_numeric($data['d_order'])){
	     $this->end(false,'排序必须为数字');
	  }
	
      $data['columntype_createtime'] = strtotime($data['columntype_createtime']);
     $item['columntype_id']= $data['columntype_id'];
     $item['columntype_name']= $data['columntype_name'];
     $item['columntype_createtime']= $data['columntype_createtime'];
	 $item['css_type']= $data['css_type'];
     $item['d_order']= $data['d_order'];
     $item['columntype_description']= $data['columntype_description'];
       return $item; 
    }

}