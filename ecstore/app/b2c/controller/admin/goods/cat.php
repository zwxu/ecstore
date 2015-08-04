<?php


class b2c_ctl_admin_goods_cat extends desktop_controller{
    /*
        %1 - id
        %2 - title
        $s - string
        $d - number
    */
    var $workground = 'b2c_ctl_admin_goods';

    public function __construct($app){
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){

        $objCat = &$this->app->model('goods_cat');
        if (!$objCat->checkTreeSize())
        {
            //超过100条
            $tree = $objCat->get_cat_list(false,0);
            $this->pagedata['hidensub'] = true;
        }
        else
        {
            $tree=$objCat->get_cat_list();
        }

        $this->pagedata['tree_number']=count($tree);
        if($tree){
            foreach($tree as $k=>$v){
                $tree[$k]['link'] = array('cat_id'=>array(
                    'v'=>$v['cat_id'],
                    't'=>app::get('b2c')->_('商品类别').app::get('b2c')->_('是').$v['cat_name']
                ));
            }
        }
        $this->pagedata['tree']= &$tree;
        $depath=array_fill(0,$objCat->get_cat_depth(),'1');
        $this->pagedata['depath']=$depath;
        $this->page('admin/goods/category/map.html');

        
    }

    function addnew($nCatId = 0){
        $this->_info($nCatId);
    }
	
	function changeCat(){
        $cat_id = $_GET['cat_id'];
		$select_id = $_GET['select_id'];
		$this->getTmplInfo($cat_id, $select_id, true);
		$this->display('admin/goods/category/tmpl.html');
	}
	function getTmplInfo($id=0, $sel_id=null, $is_change=false){
		//begin 
        if(!$id && !$is_change){
            $sel_id = '0';
        }
        
        if($id && !$sel_id){
            $objCat = &$this->app->model('goods_cat');
    		$aCat = $objCat->dump($id);
        }

		if($aCat && !$aCat['parent_id']){
            $filter['type'] = $id;

        }elseif($sel_id === '0'){
            $filter['type'] = '0';

        }else{
            $filter['filter_sql'] = 'ISNULL(type)';
        }
        $filter['tmpl_type'] = 'gallery';

		$tmpl_mod = app::get('site')->model('themes_tmpl');
		$tmpl_info = $tmpl_mod->getList('*',$filter);
		$this->pagedata['tmpl_info'] = $tmpl_info;
		
	}

    function _info($id=0,$type='add'){
        $objCat = &$this->app->model('goods_cat');
        $catList =$objCat->get_cat_list();
        $res = $objCat->dump($id,'seo_info,gallery_setting');
        $seoCat = $res['seo_info'];
        $gallery_setting = $res['gallery_setting'];
        $aCatNull[] = array('cat_id'=>0,'cat_name'=>app::get('b2c')->_('----无----'),'step'=>1);
        if(empty($catList)){
            $catList = $aCatNull;
        }else{
            $catList = array_merge($aCatNull, $catList);
        }
        $this->pagedata['catList'] = $catList;
        $oGtype = &$this->app->model('goods_type');
            $this->pagedata['gtypes'] = $oGtype->getList('type_id,name');
            $this->pagedata['gtype']['status'] = $oGtype->checkDefined();
            $aCat = $objCat->dump($id);
			$this->getTmplInfo($id);
            $this->pagedata['cat']['parent_id'] = $aCat['cat_id'];
            
            $this->pagedata['cat']['type_id']['linkid'] = $aCat['type_id'];
            $this->pagedata['cat']['type_id']['info'] = json_encode(array(array('id'=>$aCat['type_id'])));
         
            if($type == 'edit'){
                $this->pagedata['cat']['cat_id'] = $aCat['cat_id'];
                $this->pagedata['cat']['cat_name'] = $aCat['cat_name'];
                $this->pagedata['cat']['parent_id'] = $aCat['parent_id'];
                $this->pagedata['cat']['p_order'] = $aCat['p_order'];
                $this->pagedata['cat']['profit_point'] = $aCat['profit_point'];
                $this->pagedata['cat']['hidden'] = $aCat['hidden'];
                $this->pagedata['cat']['cat_logo'] = $aCat['cat_logo'];
            }
        $this->pagedata['seo_info'] = $seoCat;
        $this->pagedata['gallery_setting'] = $gallery_setting;
        $this->pagedata['isprofit'] = app::get('b2c')->getConf('member.isprofit');
        $this->display('admin/goods/category/info.html');
    }

     function save(){
         $this->begin('index.php?app=b2c&ctl=admin_goods_cat&act=index');
         if( $_POST['p_order'] === '' )
             $_POST['p_order'] = 0;

         if(!isset($_POST['cat']['hidden'])){
            $_POST['cat']['hidden'] = 'false';
         }
         
        
         $type_info = $_POST['cat']['type_id'];

         if($type_info['linkid'] == ''){
             $type_info['linkid'] = 1;
         }

         unset($_POST['cat']['type_id']);
         $type_info['linkid'] = array_filter(explode(',',$type_info['linkid']));
         $_POST['cat']['type_id'] = $type_info['linkid'][0];
         

        $objCat = &$this->app->model('goods_cat');

		$parent_id = $objCat->dump(array('cat_id'=>$_POST['cat']['cat_id']),'parent_id');
		$parent_id = $parent_id['parent_id'];

        if($objCat->save($_POST['cat'])){
			$this->updatePath($_POST,$parent_id);
            $this->end(true,app::get('b2c')->_('保存成功'));
        }else{
            $this->end(false,app::get('b2c')->_('保存失败'));
		}
    }

	function updatePath($data,$parent_id=''){
		if($data['cat']['cat_id']){
			$cat_id = $data['cat']['cat_id'];
			$objCat = $this->app->model('goods_cat');
			
			//begin修改子节点个数
			if($parent_id){
				$count = $objCat->count(array('parent_id'=>$parent_id));
				$objCat->update(array('child_count'=>$count),array('cat_id'=>$parent_id));
			}
			//end
			$cat_list = $objCat->getList('*',array('cat_path|has'=>$cat_id));
			foreach($cat_list as $k=>$v){
				$cat_path = $objCat->getCatPath($v['parent_id']);
				$objCat->update(array('cat_path'=>$cat_path),array('cat_id'=>$v['cat_id']));
			}
		}
	}

    function toRemove($nCatId){
        $this->begin('index.php?app=b2c&ctl=admin_goods_cat&act=index');
        $objCat = &$this->app->model('goods_cat');
        $cat_sdf = $objCat->dump($nCatId);

        if($objCat->toRemove($nCatId,$msg)){
            $this->end(true,$cat_sdf['cat_name'].app::get('b2c')->_('已删除'));

        }
        $this->end(false, $msg);
    }

    function edit($nCatId){
        $this->_info($nCatId,'edit');
    }

    function update(){
        $this->begin('index.php?app=b2c&ctl=admin_goods_cat&act=index');
        $o = $this->app->model('goods_cat');
        foreach( $_POST['p_order'] as $k => $v ){
            $o->update(array('p_order'=>($v===''?null:$v)),array('cat_id'=>$k) );
        }
        $o->cat2json();
        $this->end(true,app::get('b2c')->_('操作成功'));
    }

    function getByStr(){
        header('Content-type: application/json');

        $objCat = &$this->app->model('goods_cat');

        $data = $objCat->getCatLikeStr($_POST['kw']);

        echo $data;
    }
    function get_subcat_list($cat_id){
        $objCat = &$this->app->model('goods_cat');
        $row = $objCat->dump($cat_id);
        $path_id = explode(',',$row['cat_path']);
        array_shift($path_id);
        array_pop($path_id);
        $path_id[] = $cat_id;
        $cat_path = array();
        if($path_id){
            $filter = array('cat_id'=>$path_id);
            $cat_path = $objCat->getList('*',$filter);
        }
        $list1 = $objCat->get_subcat_list($cat_id);
        
        //过滤掉不属于该用户的分类数据 
        $list=array();
        $cat=kernel::single('desktop_user')->get_user_cat();
        if($cat!==false && !empty($cat['allCat'])){
            foreach($list1 as $key1=>$val1){            
                if(!in_array($val1['cat_id'],$cat['allCat'])){
                    unset($list1[$key1]);
                    continue;
                }
                $list[]=$val1;
            }            
        }else{
            $list=$list1;
        }
        foreach($list as $key=>&$val){
            
            if($val['child_count'] > 0){
                $val['isParent'] = 'isParent';
            }else{
                //unset($list['isParent']);
            }
        }


        $count = $objCat->get_subcat_count($cat_id);
        $this->pagedata['cats'] = json_encode($list);
        if(is_array($cat_path)){
            foreach($cat_path as $ck=>$cv){
                $catPath[] = $cv['cat_id'];
            }
        }
        echo json_encode($list);
       
    }

    function get_subcat($cat_id){
        if(empty($cat_id)){
             $cat_id = 0;
        }
        
        $objCat = &$this->app->model('goods_cat');
        $row = $objCat->dump($cat_id);
        $path_id = explode(',',$row['cat_path']);
        array_shift($path_id);
        array_pop($path_id);
        $path_id[] = $cat_id;
        $cat_path = array();
        if($path_id){
            $filter = array('cat_id'=>$path_id);
            $cat_path = $objCat->getList('*',$filter);
        }
        $list1 = $objCat->get_subcat_list(0);
        $list=array();
        
        //过滤掉不属于该用户的分类数据 
        $cat=kernel::single('desktop_user')->get_user_cat();
        if($cat!==false && !empty($cat['allCat'])){
            foreach($list1 as $key1=>$val1){                
                if(!in_array($val1['cat_id'],$cat['allCat'])){
                    unset($list1[$key1]);
                    continue;
                }                
                $list[]=$val1;
            }
        }else{
            $list=$list1;
        }
        foreach($list as $key=>&$val){            
            if($val['child_count'] > 0){
                $val['isParent'] = 'isParent';
            }else{
                //unset($list['isParent']);
            }
        }
        $newCat = $objCat->get_new_cat(10);
        //过滤掉不属于该用户的分类数据
        if($cat!==false && !empty($cat['allCat'])){
            foreach($newCat as $nk=>$nv){
                if(!in_array($nv['cat_id'],$cat['allCat'])){
                    unset($newCat[$nk]);
                    continue;
                }
            }
        }
        foreach($newCat as $nk=>&$nv){
            $nv['cat_path'] = substr($nv['cat_path'],1);
            $nv['cat_path'] = $nv['cat_path'].$nv['cat_id'];
        }

    //     error_log(var_export($list,true),3,'c:/dd.txt');

        //$count = $objCat->get_subcat_count($cat_id);
        //$list[]['cat_id'] = 0;
        //$list[]['cat_name'] = '分类不限';
        $list[]=array('cat_id'=>'0','cat_name'=>'分类不限');
        $this->pagedata['cats'] = json_encode($list);
        //print_r($this->pagedata['cats']);
        if(is_array($cat_path)){
            foreach($cat_path as $ck=>$cv){
                $catPath[] = $cv['cat_id'];
            }
        }
        $this->pagedata['catPath'] = implode(',',$catPath);
        $this->pagedata['newCat'] = $newCat;
       
        $this->display('admin/goods/category/cat_list.html');
        
    }

    function getChildNode(){
        if(empty($_POST['catId'])){
            return null;
        }

        $objCat = &$this->app->model('goods_cat');
        $tree = $objCat->get_cat_list(false,$_POST['catId']);
        $this->pagedata['tree']= &$tree;
        $this->page('admin/goods/category/sub_map.html');
    }

	function updataType(){
		$data = $_POST;
		if($data['name']){
			$filter['name|has'] = $data['name'];
		}
		$oGtype = &$this->app->model('goods_type');
        $gtypes = $oGtype->getList('type_id,name',$filter);
		foreach($gtypes as $k=>$v){
			if($v['type_id']==1){
				unset($gtypes[$k]);
			}
		}
		$this->pagedata['gtypes'] = $gtypes;
		$html = $this->fetch('admin/goods/category/selectType.html');
		echo $html;exit;
	}
}
