<?php


class complain_ctl_admin_reports_cat extends desktop_controller{
    /*
        %1 - id
        %2 - title
        $s - string
        $d - number
    */
    var $workground = 'complain_ctl_admin_reports';

    public function __construct($app){
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){

        $objCat = &$this->app->model('reports_cat');
        if($objCat->checkTreeSize()){
            $this->pagedata['hidenplus']=true;
        }
        $tree=$objCat->get_cat_list();
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
        $this->page('admin/reports/category/map.html');
    }

    function addnew($nCatId = 0){
        $this->_info($nCatId);
    }

    function _info($id=0,$type='add'){
        $objCat = &$this->app->model('reports_cat');
        $catList =$objCat->get_cat_list();
        $aCatNull[] = array('cat_id'=>0,'cat_name'=>app::get('b2c')->_('----无----'),'step'=>1);
        if(empty($catList)){
            $catList = $aCatNull;
        }else{
            $catList = array_merge($aCatNull, $catList);
        }
        $this->pagedata['catList'] = $catList;
        $aCat = $objCat->dump($id);
        $this->pagedata['cat']['parent_id'] = $aCat['cat_id'];
        if($type == 'edit'){
            $this->pagedata['cat']['cat_id'] = $aCat['cat_id'];
            $this->pagedata['cat']['cat_name'] = $aCat['cat_name'];
            $this->pagedata['cat']['parent_id'] = $aCat['parent_id'];
            $this->pagedata['cat']['p_order'] = $aCat['p_order'];
        }
        $this->display('admin/reports/category/info.html');
    }

     function save(){
         $this->begin('index.php?app=complain&ctl=admin_reports_cat&act=index');
         if( $_POST['p_order'] === '' )
             $_POST['p_order'] = 0;

        $objCat = &$this->app->model('reports_cat');
        if($objCat->save($_POST['cat']))
            $this->end(true,app::get('b2c')->_('保存成功'));
        else
            $this->end(false,app::get('b2c')->_('保存失败'));
    }

    function toRemove($nCatId){
        header('Content-Type:text/jcmd; charset=utf-8');
        $objCat = &$this->app->model('reports_cat');
        $cat_sdf = $objCat->dump($nCatId);
        $msg='';
        if($objCat->toRemove($nCatId,$msg)){
            echo '{success:"【'.$cat_sdf['cat_name'].'】'.app::get('b2c')->_('已删除').'",_:null}';
            exit;
        }
        echo '{error:"'.$msg.'",_:null}';
        exit;
    }

    function edit($nCatId){
        $this->_info($nCatId,'edit');
    }

    function update(){
        $this->begin('index.php?app=complain&ctl=admin_reports_cat&act=index');
        $o = $this->app->model('reports_cat');
        foreach( $_POST['p_order'] as $k => $v ){
            $o->update(array('p_order'=>($v===''?null:$v)),array('cat_id'=>$k) );
        }
        $o->cat2json();
        $this->end(true,app::get('b2c')->_('操作成功'));
    }

    function getByStr(){
        header('Content-type: application/json');

        $objCat = &$this->app->model('reports_cat');

        $data = $objCat->getCatLikeStr($_POST['kw']);

        echo $data;
    }
    function get_subcat_list($cat_id){
        $objCat = &$this->app->model('reports_cat');
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

        $objCat = &$this->app->model('reports_cat');
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
            }
        }
        $newCat = $objCat->get_new_cat(10);
        foreach($newCat as $nk=>&$nv){
            $nv['cat_path'] = substr($nv['cat_path'],1);
            $nv['cat_path'] = $nv['cat_path'].$nv['cat_id'];
        }
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
       
        $this->display('admin/reports/category/cat_list.html');
        
    }
}
