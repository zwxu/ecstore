<?php


 class cellphone_ctl_admin_phone extends desktop_controller{
     
	 
	 function __construct($app){
        parent::__construct($app);
        $this->router = app::get('desktop')->router();
        $this->_request = kernel::single('base_component_request');
    }


	function index(){
	
	$this->finder('cellphone_mdl_phone',
		array('actions' =>array(
                  array(
                    'label' => app::get('cellphone')->_('添加客服电话'),
                    'icon' => 'add.gif',
                    'href' => 'index.php?app=cellphone&ctl=admin_phone&act=add',
                   // 'target' => "_blank",
                    ),
                        ),
		    	'title'=>'客服电话列表',    
                'use_buildin_set_tag'=>false,
                'use_buildin_filter'=>false,
                'use_buildin_tagedit'=>false,
                'use_buildin_export'=>false,
                'use_buildin_import'=>false,
                'allow_detail_popup'=>true,
                'use_view_tab'=>false,));
	}


	 function add(){
	 $this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_phone','act'=>'index'));
	 $this->page('admin/customerphone/add.html');
	  }
     

	 function edit(){
	 
	    $this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_phone','act'=>'index')));
        
        $phone_id = $this->_request->get_get('phone_id');
        $objphone = $this->app->model('phone');
        $data = $objphone->getList('*',array('phone_id'=>$phone_id));
		//echo '<pre>';
		//print_r($data);
		//exit;

        $this->pagedata['item'] = $data[0];
		$this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_phone','act'=>'index'));
        $this->page('admin/customerphone/add.html');
        
	
	 }

	 function toAdd(){

    
     $this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_phone','act'=>'index'))); 
	 $data = $this->get_data();
     // echo "<pre>";
		// print_r($data);
		// exit;
	 $objphone = $this->app->model('phone');
	 if($data['phone_id']){
	 $re = $objphone->update($data,array('phone_id'=>$data['phone_id']));
	 }
	 else{
	  $re = $objphone->save($data);
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
      if(!$data['phone_number']){
	     $this->end(false,'客服电话不能为空');
	  }
	  if(!$data['is_active']){
	     $this->end(false,'请选择启用状态');
	  }
	
      
     $item['phone_id']= $data['phone_id'];
     $item['phone_number']= $data['phone_number'];
     $item['is_active']= $data['is_active'];
     $item['remark']= $data['remark'];
       return $item; 
    }


	public function openActivity(){
		$this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_phone','act'=>'index')));
		$id = $this->_request->get_get('phone_id');
		$objphone = $this->app->model('phone');
		$re = $objphone->update(array('is_active'=>'true'),array('phone_id'=>$id));
		if($re){
			$this->end(true,'保存成功');
		}else{
			$this->end(false,'保存失败');
		}
	}
	public function closeActivity(){
		$this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_phone','act'=>'index')));
		$id = $this->_request->get_get('phone_id');
		$objphone = $this->app->model('phone');
	    $re=
	  $objphone->update(array('is_active'=>'false'),array('phone_id'=>$id));
		if($re){
			$this->end(true,'保存成功');
		}else{
			$this->end(false,'保存失败');
		}
	}



}