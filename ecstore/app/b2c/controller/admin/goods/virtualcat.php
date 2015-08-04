<?php
 

class b2c_ctl_admin_goods_virtualcat extends desktop_controller{


    public function __construct($app){
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $objCat = &$this->app->model('goods_virtual_cat');
        if($objCat->checkTreeSize()){
            $this->pagedata['hidenplus']=true;
        }
        $tree = $objCat->get_virtualcat_list(false,false);

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
        $depath=array_fill(0,$objCat->get_virtualcat_depth(),'1');
        $this->pagedata['depath']=$depath;
        $this->page('admin/goods/virtualcat/map.html');

    }
    function addNew($id=0){
        $this->begin('index.php?app=b2c&ctl=admin_goods_virtualcat&act=index');
        $this->path[] = array('text'=>app::get('b2c')->_('商品虚拟分类新增'));
        $vobjCat = &$this->app->Model('goods_virtual_cat');
        $objCat = &$this->app->Model('goods_cat');
        $aCat = $vobjCat->get_virtualcat_list(true);
        $aCatNull[] = array('cat_id'=>0,'cat_name'=>app::get('b2c')->_('----无----'),'step'=>1);
        if(empty($aCat)){
            $aCat = $aCatNull;
        }else{
            $aCat = array_merge($aCatNull, $aCat);
        }
        $this->pagedata['catList'] = $aCat;
        $this->pagedata['gtypes'] = $objCat->getTypeList();
        $oGtype = &$this->app->Model('goods_type');
        $this->pagedata['gtype']['status'] = $oGtype->checkDefined();

        if($id){
            $aCat = $vobjCat->dump($id);
            $this->pagedata['cat']['parent_id'] = $aCat['virtual_cat_id'];
            $this->pagedata['cat']['type_id'] = $aCat['type_id'];
        }else{
            $aTmp = $oGtype->getDefault();
            $this->pagedata['cat']['type_id'] = $aTmp[0]['type_id'];
        }
        $this->pagedata['cat']['p_order'] = 0;
        $this->display('admin/goods/virtualcat/info.html');
    }

    function doAdd(){
        $this->begin('index.php?app=b2c&ctl=admin_goods_virtualcat&act=index');
        $objCat = &$this->app->Model('goods_virtual_cat');
        $cat = $_POST['cat'];
        $cat['virtualcat_template'] = $_POST['virtualcat_template'];
        $cat['filter'] = $_POST['adjunct']['items'][0];

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
    function doImport(){

        if(!is_array($_POST['cat']) || empty($_POST['cat'])){
            $this->splash('failed', 'index.php?ctl=goods/virtualcat&act=import', app::get('b2c')->_('请选择商品分类源节点'));
        }else{
            $this->begin('index.php?app=b2c&ctl=admin_goods_virtualcat&act=index');
            foreach($_POST['cat'] as $key=>$v){
                if($v){
                    $search[]=$v;
                }
            }
            $objCat = &$this->system->loadModel('goods/virtualcat');
            $this->end($objCat->doImport($search,$_POST['vCat_id'],$_POST['defaultfilter']),app::get('b2c')->_('保存成功'));
        }
    }


    function getGoodsCatById($cat_id=0){
        $vobjCat = &$this->app->Model('goods_virtual_cat');
        echo json_encode($vobjCat->getGoodsCatById($cat_id));

    }


    function edit($catid){
        $this->path[] = array('text'=>app::get('b2c')->_('商品虚拟分类编辑'));
        $vobjCat = &$this->app->Model('goods_virtual_cat');
        $objCat = &$this->app->Model('goods_cat');
        $aCat = $vobjCat->dump($catid);
        $this->pagedata['virtual_cat_name'] = $aCat['virtual_cat_name'];
        $aCat['addon'] = unserialize($aCat['addon']);
        $filter = $aCat['filter'];
        unset($aCat['filter']);
        $aCat['filter']['items'] = $filter;
        $this->pagedata['cat'] = $aCat;
        $aCat = $vobjCat->get_virtualcat_list(true,true,$catid);
        $aCatNull[] = array('cat_id'=>0,'cat_name'=>app::get('b2c')->_('----无----'),'step'=>1);
        $aCat = array_merge($aCatNull, (array)$aCat);
        $this->pagedata['catList'] = $aCat;
        $this->pagedata['gtypes'] = $objCat->getTypeList();
        $oGtype = &$this->app->Model('goods_type');
        $this->pagedata['gtype']['status'] = $oGtype->checkDefined();

        $this->display('admin/goods/virtualcat/info.html');
    }


    function toRemove($id){
        $this->begin('index.php?app=b2c&ctl=admin_goods_virtualcat&act=index');
        $objType = &$this->app->Model('goods_virtual_cat');
        if($objType->toRemove($id)){
        	$this->end(true,app::get('b2c')->_('分类删除成功'));
        }
        $this->end(false, $objType->remove_errmsg ? $objType->remove_errmsg : app::get('b2c')->_('分类删除失败'));
    }

    function update(){
        $this->begin('index.php?app=b2c&ctl=admin_goods_virtualcat&act=index');
        $objType = &$this->app->Model('goods_virtual_cat');
        $this->end($objType->updateOrder($_POST['p_order']), app::get('b2c')->_('更新成功'));
    }
}
?>
