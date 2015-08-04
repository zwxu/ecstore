<?php
class cellphone_ctl_admin_category extends desktop_controller{
    
    public function __construct($app){
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $objCat = &$this->app->model('category');
        if($objCat->checkTreeSize()){
            $this->pagedata['hidenplus']=true;
        }
        $tree = $objCat->get_cat_list(false,false);

        $this->pagedata['tree_number']=count($tree);
        if(is_array($tree)){
            foreach($tree as $k=>$v){
               parse_str($v['filter'],$filter);
               $link = array();
               foreach($filter as $n=>$f){
                   if($n=='pricefrom'&&!$f){
                       $link[$n] = array('v'=>0,'t'=>$v['cat_name']);
                   }
                   if($f){
                       $link[$n] = array('v'=>$f,'t'=>$v['cat_name']);
                   }
                   $data[$n] = $f;
               }
               if(is_array($data['props'])){
                   foreach($data['props'] as $pk=>$pv){
                       if($pv[0] != '_ANY_'){
                           $data['p_'.$pk] = $pv[0];
                       }
                   }
               }
               $tree[$k]['link'] = $link;
               if(!empty($data)){
                   $tree[$k]['filter'] = urlencode(serialize($data));
               }else{
                   $tree[$k]['filter'] = null;
               }
               unset($data);
            }

        }

        $this->pagedata['tree']=&$tree;
        $depath=array_fill(0,$objCat->get_cat_depth(),'1');
        $this->pagedata['depath']=$depath;
        $this->page('admin/category/map.html');
    }
    
    function addNew($id=0){
        $this->begin('index.php?app=cellphone&ctl=admin_category&act=index');
        $this->path[] = array('text'=>app::get('b2c')->_('分类新增'));
        $vobjCat = &$this->app->Model('category');
        $objCat = &app::get('b2c')->Model('goods_cat');
        $aCat = $vobjCat->get_cat_list(true);
        $aCatNull[] = array('cat_id'=>0,'cat_name'=>app::get('b2c')->_('----无----'),'step'=>1);
        if(empty($aCat)){
            $aCat = $aCatNull;
        }else{
            $aCat = array_merge($aCatNull, $aCat);
        }
        $this->pagedata['catList'] = $aCat;
        $this->pagedata['gtypes'] = $objCat->getTypeList();
        $oGtype = &app::get('b2c')->Model('goods_type');
        $this->pagedata['gtype']['status'] = $oGtype->checkDefined();

        if($id){
            $aCat = $vobjCat->dump($id);
            $this->pagedata['cat']['parent_id'] = $aCat['cat_id'];
            $this->pagedata['cat']['type_id'] = $aCat['type_id'];
        }else{
            $aTmp = $oGtype->getDefault();
            $this->pagedata['cat']['type_id'] = $aTmp[0]['type_id'];
        }
        $this->pagedata['cat']['p_order'] = 0;
        $this->display('admin/category/info.html');
    }

    function doAdd(){
        $this->begin('index.php?app=cellphone&ctl=admin_category&act=index');
        $objCat = &$this->app->Model('category');
        $cat = $_POST['cat'];
        $cat['filter'] = $_POST['adjunct']['items'][0];
        foreach((array)$_POST['custom'] as $key => $value){
            $cat['customized'][] = $value;
        }

        /*判断价格区间从低到高填写*/
        parse_str($cat['filter'],$tmpfilter);
        if($tmpfilter['pricefrom']>$tmpfilter['priceto']){
            $this->end(false,app::get('b2c')->_('价格区间填写有误，请从低到高填写!'));
        }

        if($objCat->addNew($cat)){
            $this->end(true,app::get('b2c')->_('保存成功'));
        }else{
            $this->end(false,app::get('b2c')->_('保存失败'));
        }
    }

    function getGoodsCatById($cat_id=0){
        $vobjCat = &$this->app->Model('category');
        echo json_encode($vobjCat->getGoodsCatById($cat_id));
    }


    function edit($catid){
        $this->path[] = array('text'=>app::get('b2c')->_('商品虚拟分类编辑'));
        $vobjCat = &$this->app->Model('category');
        $objCat = &app::get('b2c')->Model('goods_cat');
        $aCat = $vobjCat->dump($catid);
        $this->pagedata['cat_name'] = $aCat['cat_name'];
        $aCat['addon'] = unserialize($aCat['addon']);
        $filter = $aCat['filter'];
        unset($aCat['filter']);
        $aCat['filter']['items'] = $filter;
        $aCat['customized'] = unserialize($aCat['customized']);
        $this->pagedata['cat'] = $aCat;
        $aCat = $vobjCat->get_cat_list(true,true,$catid);
        $aCatNull[] = array('cat_id'=>0,'cat_name'=>app::get('b2c')->_('----无----'),'step'=>1);
        $aCat = array_merge($aCatNull, (array)$aCat);
        $this->pagedata['catList'] = $aCat;
        $this->pagedata['gtypes'] = $objCat->getTypeList();
        $oGtype = &app::get('b2c')->Model('goods_type');
        $this->pagedata['gtype']['status'] = $oGtype->checkDefined();
        $this->display('admin/category/info.html');
    }


    function toRemove($id){
        $this->begin('index.php?app=cellphone&ctl=admin_category&act=index');
        $objType = &$this->app->Model('category');
        if($objType->toRemove($id)){
        	$this->end(true,app::get('b2c')->_('分类删除成功'));
        }
        $this->end(false, $objType->remove_errmsg ? $objType->remove_errmsg : app::get('b2c')->_('分类删除失败'));
    }

    function update(){
        $this->begin('index.php?app=cellphone&ctl=admin_category&act=index');
        $objType = &$this->app->Model('category');
        $this->end($objType->updateOrder($_POST['p_order']), app::get('b2c')->_('更新成功'));
    }
}
?>
