<?php


  class cellphone_ctl_admin_banner extends desktop_controller{
     
	 
	 function __construct($app){
        parent::__construct($app);
        $this->router = app::get('desktop')->router();
        $this->_request = kernel::single('base_component_request');
    }

      function index(){ 
		  
		  $this->finder('cellphone_mdl_banner',
              array(
			'actions' =>array(
                  array(
                    'label' => app::get('cellphone')->_('添加'),
                    'icon' => 'add.gif',
                    'href' => 'index.php?app=cellphone&ctl=admin_banner&act=add',
                   // 'target' => "_blank",
                    ),

                  array('label' => "批量操作",
                        'icon' => 'batch.gif',
                        'group' => array(
                        
                            array('label' => app :: get('cellphone') -> _('排序'), 'icon' => 'download.gif', 'submit' => 'index.php?app=cellphone&ctl=admin_banner&act=singleBatchEdit&p[0]=dorder', 'target' => 'dialog'),
					                    ),
                        ),

                  ),
		    	'title'=>'轮播列表',    
                'use_buildin_set_tag'=>false,
                'use_buildin_filter'=>false,
                'use_buildin_tagedit'=>false,
                'use_buildin_export'=>false,
                'use_buildin_import'=>false,
                'allow_detail_popup'=>true,
                'use_view_tab'=>false,
                ));
	  
	  
	  
	  
	  }

     function add(){
	 $this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_banner','act'=>'index'));
	 $this->page('admin/banner/add.html');
	  }

	 function changeType(){
	    $render = $this -> app -> render();
        $select_type = $_POST['select_type'];

	   if($select_type == "goods"){
        echo $render -> fetch('admin/banner/goods.html');
	   }
	   if($select_type == "activity"){
        echo $render->fetch('admin/banner/activity.html');
	   }
	   if($select_type =="article"){
        echo $render->fetch('admin/banner/article.html');
	   }

	 
	 }

	 function changeActivity(){
	    $render = $this -> app -> render();
        $select_activity = $_POST['select_activity'];
		if($select_activity=="groupbuy"){
		 echo $render -> fetch('admin/banner/groupbuy.html');
		}
		if($select_activity=="timedbuy"){
		 echo $render -> fetch('admin/banner/timedbuy.html');
		}
		if($select_activity=="scorebuy"){
		 echo $render -> fetch('admin/banner/scorebuy.html');
		}
		if($select_activity=="spike"){
		 echo $render -> fetch('admin/banner/spike.html');
		}
		if($select_activity=="package"){
		 echo $render -> fetch('admin/banner/package.html');
		}
	 
	 }
	 function toAdd(){
    
     $this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_banner','act'=>'index'))); 
	 $data = $this->get_data();
	// echo "<pre>";
		//print_r($data);
		//exit;
	 $banner = $this->app->model('banner');
	 if($data['id']){
	 $re = $banner->update($data,array('id'=>$data['id']));
	 }
	 else{
	  $re = $banner->save($data);
	 }
	 if($re){
	 $this->end(true,'保存成功');
	 }
	 else{
	 $this->end(false,'保存失败');
	 }
	  
	 }

	 function edit(){
	 
	    $this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_banner','act'=>'index')));
        
        $id = $this->_request->get_get('id');
        $banner = $this->app->model('banner');
        $data = $banner->getList('*',array('id'=>$id));
		//echo '<pre>';
		//print_r($data);
		//exit;
		if($data['is_active']=='true'){	
         
			$this->end(false,'不能编辑已开启的项');
		}

        $this->pagedata['item'] = $data[0];
		$this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_banner','act'=>'index'));
        $this->page('admin/banner/add.html');
        
	 
	 }
	 public function get_data(){

      $data = $this->_request->get_post();;
      if(!$data['associate_type']){
	     $this->end(false,'请选择类型');
	  }
	  if(!$data['associate_id']){
	     $this->end(false,'请选择具体ID');
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
		$this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_banner','act'=>'index')));
		$id = $this->_request->get_get('id');
		$banner = $this->app->model('banner');
		$re = $banner->update(array('is_active'=>'true'),array('id'=>$id));
		if($re){
			$this->end(true,'保存成功');
		}else{
			$this->end(false,'保存失败');
		}
	}
	public function closeActivity(){
		$this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_banner','act'=>'index')));
		$id = $this->_request->get_get('id');
		$banner = $this->app->model('banner');
	    $re=
	  $banner->update(array('is_active'=>'false'),array('id'=>$id));
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
        if (count($_POST['id']) == 0 && $_POST['_finder']['select'] != 'multi' && !$_POST['_finder']['id'] && !$_POST['filter']) {
            echo __('请选择记录');
            exit;
        } 
        if ($_POST['filter']) {
            $_POST['_finder'] = unserialize($_POST['filter']);
            $editType = $_POST['updateAct'];
        } 
        // 选中记录的ID经序列化后保存于页面
        $this -> pagedata['filter'] = serialize($_POST['id']);
        $this -> pagedata['catfilter'] = array('parent_id|noequal'=>0);
        $this -> pagedata['editInfo'] = array('count' => count($_POST['id']));

        $this -> display('admin/banner/batch/batchEdit' . $editType . '.html');
    } 

     function saveBatchEdit() { 

        $filter = unserialize($_POST['filter']); 
        //echo '<pre>';
		//print_r($_POST);
		//exit;
        switch($_POST['updateAct']){
		
		 case 'dorder':
        $this->begin($this->router->gen_url(array('app'=>'cellphone','ctl'=>'admin_banner','act'=>'index')));
        $banner = $this->app->model('banner');
		$result = true;
		foreach($filter as $val){
		if(!$banner->update(array('d_order'=>$_POST['orderval']),array('id'=>$val))){
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



