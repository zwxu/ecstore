<?php



 class cellphone_ctl_admin_column extends desktop_controller{
     
	 
	 function __construct($app){
        parent::__construct($app);
        $this->router = app::get('desktop')->router();
        $this->_request = kernel::single('base_component_request');
    }


     function index(){
	 
	 $this->finder('cellphone_mdl_column',
		    array('actions' =>array(
                  array(
                    'label' => app::get('cellphone')->_('添加'),
                    'icon' => 'add.gif',
                    'href' => 'index.php?app=cellphone&ctl=admin_column&act=add',
                   // 'target' => "_blank",
                    ),

                  array('label' => "批量操作",
                        'icon' => 'batch.gif',
                        'group' => array(
                        
                            array('label' => app :: get('cellphone') -> _('排序'), 'icon' => 'download.gif', 'submit' => 'index.php?app=cellphone&ctl=admin_column&act=singleBatchEdit&p[0]=dorder', 'target' => 'dialog'),
					                    ),
                        ),

                  ),
		    	'title'=>'专栏列表',    
                'use_buildin_set_tag'=>false,
                'use_buildin_filter'=>false,
                'use_buildin_tagedit'=>false,
                'use_buildin_export'=>false,
                'use_buildin_import'=>false,
                'allow_detail_popup'=>true,
                'use_view_tab'=>false,));

	 }

	 function add(){
     $columntype = $this->app->model('columntype');
	 $type = $columntype->getList('columntype_id,columntype_name');
     $this->pagedata['columntype'] = $type;
	 $this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_column','act'=>'index'));
	 $this->page('admin/column/add.html');
	  }

     function edit(){
	 
	    $this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_column','act'=>'index')));
        $columntype = $this->app->model('columntype');
	    $type = $columntype->getList('columntype_id,columntype_name');
        $this->pagedata['columntype'] = $type;
        $column_id = $this->_request->get_get('column_id');
        $column = $this->app->model('column');
	
        $data = $column->getList('*',array('column_id'=>$column_id));
		//echo '<pre>';
		//print_r($data);
		//exit;
		if($data['is_active']=='true'){	
         
			$this->end(false,'不能编辑已开启的项');
		}

        $this->pagedata['column'] = $data[0];
		$this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_column','act'=>'index'));
        $this->page('admin/column/add.html');
         
	 }


	 function toAdd(){
		 
     $this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_column','act'=>'index'))); 
	 $data = $this->get_data();
	//echo "<pre>";
	//print_r($data);
	//exit;
	 $column = $this->app->model('column');
	 if($data['column_id']){
	 $re = $column->update($data,array('column_id'=>$data['column_id']));
	 }
	 else{
		
	  $re = $column->save($data);
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
      if(!$data['columntype_id']){
	     $this->end(false,'请选择栏目类型');
	  }
	  if(!$data['goods_id']){
	     $this->end(false,'请选择商品');
	  }
	  
	  if(!$data['start_time']||!$data['end_time']){
           $this->end(false,'请填写完整的时间段');
       }
      $data['start_time'] = strtotime($data['start_time'].' '.$data['_DTIME_']['H']['start_time'].':'.$data['_DTIME_']['M']['start_time']);
      $data['end_time'] = strtotime($data['end_time'].' '.$data['_DTIME_']['H']['end_time'].':'.$data['_DTIME_']['M']['end_time']);
      if($data['end_time']<=$data['start_time']){
           $this->end(false,'开始时间要大于结束时间');
       }
	  if(!$data['image_id']){
	       $this->end(false,'请上传图片');   
	  }


       return $data; 
    }

     public function openActivity(){
		$this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_column','act'=>'index')));
		$id = $this->_request->get_get('column_id');
		$column = $this->app->model('column');
		$re = $column->update(array('is_active'=>'true'),array('column_id'=>$id));
		if($re){
			$this->end(true,'保存成功');
		}else{
			$this->end(false,'保存失败');
		}
	}
	public function closeActivity(){
		$this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_column','act'=>'index')));
		$id = $this->_request->get_get('column_id');
		$column = $this->app->model('column');
	    $re=
	  $column->update(array('is_active'=>'false'),array('column_id'=>$id));
		if($re){
			$this->end(true,'保存成功');
		}else{
			$this->end(false,'保存失败');
		}
	}

   
	function singleBatchEdit($editType = '') {
        // $objshopinfo = &$this->app->model('storegrade');
        $newFilter = $_POST;
        unset($newFilter['app']);
        unset($newFilter['ctl']);
        unset($newFilter['act']);
        unset($newFilter['_finder']);
        unset($newFilter['marketable']);
        unset($newFilter['_DTYPE_BOOL']);

        if ($_POST['isSelectedAll'] == '_ALL_')
            $_POST['id'][0] = '_ALL_';
        if (count($_POST['column_id']) == 0 && $_POST['_finder']['select'] != 'multi' && !$_POST['_finder']['column_id'] && !$_POST['filter']) {
            echo __('请选择记录');
            exit;
        } 
        if ($_POST['filter']) {
            $_POST['_finder'] = unserialize($_POST['filter']);
            $editType = $_POST['updateAct'];
        } 
        // 选中记录的ID经序列化后保存于页面
        $this -> pagedata['filter'] = serialize($_POST['column_id']);
        $this -> pagedata['catfilter'] = array('parent_id|noequal'=>0);
        $this -> pagedata['editInfo'] = array('count' => count($_POST['column_id']));

        $this -> display('admin/column/batch/batchEdit' . $editType . '.html');
    } 


    function saveBatchEdit() { 

        $filter = unserialize($_POST['filter']); 
        //echo '<pre>';
		//print_r($_POST);
		//exit;
        switch($_POST['updateAct']){
		
		 case 'dorder':
		$this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_column','act'=>'index')));
        $column = $this->app->model('column');
		$result = true;
		foreach($filter as $val){
		if(!$column->update(array('d_order'=>$_POST['orderval']),array('column_id'=>$val))){
        $result=false;
        $this->end(false,'操作失败');
		
		   }
		
       }
	   if($result){
	     $this->end(true,'批量操作成功');
		
	   }
       else{
	    echo $GLOBALS['php_errormsg'];
	   }
		
		break;
		
		default:
		break;

		}

       
      } 

}