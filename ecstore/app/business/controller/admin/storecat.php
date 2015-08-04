<?php

class business_ctl_admin_storecat extends desktop_controller{
    /*
        %1 - id
        %2 - title
        $s - string
        $d - number
    */
    var $workground = 'business_ctl_admin_store';

    public function __construct($app){
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){

        $objCat = &$this->app->model('storecat');
        if($objCat->checkTreeSize()){
            $this->pagedata['hidenplus']=true;
        }
        $tree=$objCat->get_cat_list();

       
       
        $this->pagedata['tree_number']=count($tree);
        if($tree){
            foreach($tree as $k=>$v){
                $tree[$k]['link'] = array('cat_id'=>array(
                                'v'=>$v['cat_id'],
                                't'=>app::get('business')->_('店铺类别').app::get('business')->_('是').$v['cat_name']
                            ));
            }
        }
       
        $this->pagedata['tree']= &$tree;
        $depath=array_fill(0,$objCat->get_cat_depth(),'1');
        $this->pagedata['depath']=$depath;
        $this->page('admin/store/map.html');
    }

    function addnew($nCatId = 0){
        $this->_info($nCatId);
    }

    function _info($id=0,$type='add'){
        $objCat = &$this->app->model('storecat');
        $catList =$objCat->get_cat_list();
        $res = $objCat->dump($id,'seo_info,gallery_setting');
        $seoCat = $res['seo_info'];
        $gallery_setting = $res['gallery_setting'];
        $aCatNull[] = array('cat_id'=>0,'cat_name'=>app::get('business')->_('----无----'),'step'=>1);
        if(empty($catList)){
            $catList = $aCatNull;
        }else{
            $catList = array_merge($aCatNull, $catList);
        }
        $this->pagedata['catList'] = $catList;
        //$oGtype = &$this->app->model('goods_type');
         //   $this->pagedata['gtypes'] = $oGtype->getList('type_id,name');
           // $this->pagedata['gtype']['status'] = $oGtype->checkDefined();
            $aCat = $objCat->dump($id);
            $this->pagedata['cat']['parent_id'] = $aCat['cat_id'];
            $this->pagedata['cat']['type_id'] = $aCat['type_id'];
            if($type == 'edit'){
                $this->pagedata['cat']['cat_id'] = $aCat['cat_id'];
                $this->pagedata['cat']['cat_name'] = $aCat['cat_name'];
                $this->pagedata['cat']['parent_id'] = $aCat['parent_id'];
                $this->pagedata['cat']['p_order'] = $aCat['p_order'];
            }
        $this->pagedata['seo_info'] = $seoCat;
        $this->pagedata['gallery_setting'] = $gallery_setting;
        $this->display('admin/store/info.html');
    }

     function save(){
         $this->begin('index.php?app=business&ctl=admin_storecat&act=index');
         if( $_POST['p_order'] === '' )
             $_POST['p_order'] = 0;

        $objCat = &$this->app->model('storecat');
        if($objCat->save($_POST['cat']))
            $this->end(true,app::get('business')->_('保存成功'));
        else
            $this->end(false,app::get('business')->_('保存失败'));
    }

    function toRemove($nCatId){
        $this->begin('index.php?app=business&ctl=admin_storecat&act=index');
        $objCat = &$this->app->model('storecat');
        $cat_sdf = $objCat->dump($nCatId);

        if($objCat->toRemove($nCatId,$msg)){
            $this->end(true,$cat_sdf['cat_name'].app::get('business')->_('已删除'));

        }
        $this->end(false, $msg);
    }

    function edit($nCatId){
        $this->_info($nCatId,'edit');
    }

    function update(){
        $this->begin('index.php?app=business&ctl=admin_storecat&act=index');
        $o = $this->app->model('storecat');
        foreach( $_POST['p_order'] as $k => $v ){
            $o->update(array('p_order'=>($v===''?null:$v)),array('cat_id'=>$k) );
        }
        $o->cat2json();
        $this->end(true,app::get('business')->_('操作成功'));
    }

    function getByStr(){
        header('Content-type: application/json');

        $objCat = &$this->app->model('storecat');

        $data = $objCat->getCatLikeStr($_POST['kw']);

        echo $data;
    }
    function get_subcat_list($cat_id){
        $objCat = &$this->app->model('storecat');
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
        $list = $objCat->get_subcat_list($cat_id);
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

        $objCat = &$this->app->model('storecat');
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
        $list = $objCat->get_subcat_list(0);
        foreach($list as $key=>&$val){
            if($val['child_count'] > 0){
                $val['isParent'] = 'isParent';
            }else{
                //unset($list['isParent']);
            }
        }
        $newCat = $objCat->get_new_cat(10);
        foreach($newCat as $nk=>&$nv){
            $nv['cat_path'] = substr($nv['cat_path'],1);
            $nv['cat_path'] = $nv['cat_path'].$nv['cat_id'];
        }

    //     error_log(var_export($list,true),3,'c:/dd.txt');

        $count = $objCat->get_subcat_count($cat_id);
        $list[]['cat_id'] = 0;
        $list[]['cat_name'] = '分类不限';
        $this->pagedata['cats'] = json_encode($list);
        if(is_array($cat_path)){
            foreach($cat_path as $ck=>$cv){
                $catPath[] = $cv['cat_id'];
            }
        }
        $this->pagedata['catPath'] = implode(',',$catPath);
        $this->pagedata['newCat'] = $newCat;
       
        $this->display('admin/store/cat_list.html');
        
    }
}
