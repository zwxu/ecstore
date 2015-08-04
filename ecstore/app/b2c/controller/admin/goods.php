<?php
 

class b2c_ctl_admin_goods extends desktop_controller{

    var $workground = 'b2c_ctl_admin_goods';

    public $use_buildin_import = true;




    function index(){
        if($_GET['action'] == 'export') $this->_end_message = '导出商品';

       
        // if($this->has_permission('batcheditmarketable')){
            // $group[] = array('label'=>app::get('b2c')->_('商品上架'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=enable','target'=>'refresh');
            // $group[] = array('label'=>app::get('b2c')->_('商品下架'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=disable','target'=>'refresh');
            // $group[] = array('label'=>'_SPLIT_');
        // }
        if($this->has_permission('batcheditmarketable')){
            $group[] = array('label'=>app::get('b2c')->_('商品上架'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit&p[0]=enable','target'=>'dialog');
            $group[] = array('label'=>app::get('b2c')->_('商品下架'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit&p[0]=disable','target'=>'dialog');
            $group[] = array('label'=>'_SPLIT_');
        }
        if($this->has_permission('batcheditorderdown')){
            $group[] = array('label'=>app::get('b2c')->_('商品降权'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit&p[0]=orderDown','target'=>'dialog');
        }
      
        // if($this->has_permission('batcheditprice')){
        //     $group[] = array('label'=>app::get('b2c')->_('统一调价'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit&p[0]=uniformPrice','target'=>'dialog');
        //     $group[] = array('label'=>app::get('b2c')->_('分别调价'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit&p[0]=differencePrice','target'=>'dialog');
        // }
        // if($this->has_permission('batcheditstore')){
        //     $group[] = array('label'=>app::get('b2c')->_('统一调库存'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit&p[0]=uniformStore','target'=>'dialog');
        //     $group[] = array('label'=>app::get('b2c')->_('分别调库存'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit&p[0]=differenceStore','target'=>'dialog');
        //     $group[] = array('label'=>'_SPLIT_');
        // }
        //     $group[] = array('label'=>app::get('b2c')->_('商品名称'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit&p[0]=name','target'=>'dialog');
        //     $group[] = array('label'=>app::get('b2c')->_('商品简介'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit&p[0]=brief','target'=>'dialog');
        // if($this->has_permission('brandgoods')){
        //     $group[] = array('label'=>app::get('b2c')->_('商品品牌'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit&p[0]=brand','target'=>'dialog');
        // }
        //     $group[] = array('label'=>app::get('b2c')->_('商品排序'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit&p[0]=dorder','target'=>'dialog');
        //     $group[] = array('label'=>app::get('b2c')->_('商品重量'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit&p[0]=weight','target'=>'dialog');
        // if($this->has_permission('catgoods')){
        //     $group[] = array('label'=>app::get('b2c')->_('分类转换'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit&p[0]=cat','target'=>'dialog');
        //     $group[] = array('label'=>'_SPLIT_');
        // }
             $group[] = array('label'=>app::get('b2c')->_('重新生成图片'),'icon'=>'download.gif','submit'=>'index.php?app=b2c&ctl=admin_goods&act=batchImage','target'=>'dialog');

        foreach(kernel::servicelist('b2c.goods_finder_edit_group') as $object)
        {
            if(is_object($object) && method_exists($object,'get_extends_group')) $object->get_extends_group($group);
        }

        // if($this->has_permission('addgoods')){
        //     $custom_actions[] = array(
        //         'label'=>app::get('b2c')->_('添加商品'),
        //         'icon'=>'add.gif',
        //         //'disabled'=>'false',
        //         'href'=>'index.php?app=b2c&ctl=admin_goods_editor&act=add',
        //         'target'=>'_blank'
        //     );
        // }
        $custom_actions[] = array(
            'label'=>app::get('b2c')->_('批量操作'),
            'icon'=>'batch.gif',
            'group'=>$group,
         );
        $actions_base['title'] = app::get('b2c')->_('商品列表');
        $actions_base['actions'] = $custom_actions;
        $actions_base['use_buildin_set_tag'] = true;
        $actions_base['use_buildin_filter'] = true;
        if($this->has_permission('exportgoods')){
            $actions_base['use_buildin_export'] = true;
        }
        if($this->has_permission('importgoods')){
            $actions_base['use_buildin_import'] = true;
        }
        if(!$this->has_permission('deletegoods')){
            $actions_base['use_buildin_recycle'] = false;
        }
        $actions_base['allow_detail_popup'] = true;
        $actions_base['use_view_tab'] = true;

        $this->finder('b2c_mdl_goods',$actions_base);
    }

   function _views(){

        $sub_menu = array();
        $mdl_goods = $this->app->model('goods');
        if($_GET['view'] || !$_GET['filter']){
            //已下架商品
            $filter = array('marketable'=>'false','goods_type'=>'normal');
            $market_count = $mdl_goods->count($filter);
            if($market_count >0){
                $sub_menu[1] = array('label'=>app::get('b2c')->_('已下架商品'),'optional'=>true,'filter'=>$filter,'addon'=>$market_count,'href'=>'index.php?app=b2c&ctl=admin_goods&act=index&view=1&view_from=dashboard');
            }
            //缺货商品
            $mdl_products = $this->app->model('products');
            //鸡肋的功能，先这样优化了，至少4000-5000的量不会加载慢了
            $goods_id_arr = $mdl_products->db->select("select DISTINCT goods_id from sdb_b2c_products where goods_type='normal' and store='0'");
            if(is_array($goods_id_arr)){
                foreach($goods_id_arr as $gk=>$gv){
                    $fgoods['goods_id'][] = $gv['goods_id'];
                }
            }

            if(count($fgoods['goods_id']) >0){
                $sub_menu[2] = array('label'=>app::get('b2c')->_('缺货商品'),'optional'=>true,'filter'=>$fgoods,'addon'=>count($fgoods['goods_id']),'href'=>'index.php?app=b2c&ctl=admin_goods&act=index&view=2&view_from=dashboard');
            }

            unset($fgoods);

            //库存报警
            $alert_num = $this->app->getConf('system.product.alert.num');
            //同缺货商品的情况 
            $goods_id_arr = $mdl_products->db->select("select DISTINCT goods_id from sdb_b2c_products where goods_type='normal' and store <='".$alert_num."'");
            if(is_array($goods_id_arr)){
                foreach($goods_id_arr as $gk=>$gv){
                    $fgoods['goods_id'][] = $gv['goods_id'];
                }
            }

            if(count($fgoods['goods_id']) >0){
                $sub_menu[3] = array('label'=>app::get('b2c')->_('库存报警'),'optional'=>true,'filter'=>$fgoods,'addon'=>count($fgoods['goods_id']),'href'=>'index.php?app=b2c&ctl=admin_goods&act=index&view=3&view_from=dashboard');
            }
            if($sub_menu){
                $sub_menu[0] = array('label'=>app::get('b2c')->_('全部'),'optional'=>true,'filter'=>'','addon'=>$mdl_goods->count());
            }
            
           
            unset($fgoods);
            $goods_id_arr = $mdl_products->db->select("select DISTINCT a.goods_id from sdb_b2c_goods_marketable_application as a join sdb_b2c_goods as g on a.goods_id=g.goods_id where a.status='0'");
            if(is_array($goods_id_arr)){
                foreach($goods_id_arr as $gk=>$gv){
                    $fgoods['goods_id'][] = $gv['goods_id'];
                }
            }
            if(count($fgoods['goods_id']) >0){
                $sub_menu[4] = array('label'=>app::get('b2c')->_('上架审核'),'optional'=>true,'filter'=>$fgoods,'addon'=>count($fgoods['goods_id']),'href'=>'index.php?app=b2c&ctl=admin_goods&act=index&view=4&view_from=dashboard');
            }
           
            
            ksort($sub_menu);
            $show_menu = $sub_menu;
            foreach($show_menu as $k=>$v){
                if($v['optional']==false){
                }elseif(($_GET['view_from']=='dashboard')&&$k==$_GET['view']){
                    $show_menu[$k] = $v;
                }
                if (!$v['addon']) {unset($show_menu[$k]);}
            }
        }

        return $show_menu;
    }

    function import(){
        $this->pagedata['thisUrl'] = 'index.php?app=b2c&ctl=admin_goods&act=index';
       // $import = new desktop_finder_builder_import($this);
       // $import_panel_html = $import->main();
        $oGtype = $this->app->model('goods_type');
        $this->pagedata['gtype'] = $oGtype->getList('type_id,name');
      //  $this->pagedata['import_panel_html'] = $import_panel_html;
        $this->page('admin/goods/goods_import.html');
    }

    function showfilter($type_id){

        $goods_filter = kernel::single('b2c_goods_goodsfilter');
        $return = $goods_filter->goods_goodsfilter($type_id,$this->app);
        $this->pagedata['filter'] = $return;


        $this->pagedata['filter_interzone'] = $_POST;
        $this->pagedata['view'] = $_POST['view'];
        $this->display('admin/goods/filter_addon.html');
    }

    function enable(){
        //@lujy--批量上架权限
        if(!$this->has_permission('batcheditmarketable')){
            $this->begin('');
            $this->end(false, app::get('b2c')->_('您无权批量操作商品上架'));
        }
        $this->begin('');
        $objGoods = &$this->app->model('goods');
        $glist = $objGoods->setEnabled($_POST,'true');
       
        $data['marketable_allow'] = 'true';
        if($_POST['goods_id'][0] == '_ALL_')  unset($_POST);
        $goods_id = $objGoods->getList('goods_id',$_POST);
        foreach($goods_id as $pk => $pv){
           $result['goods_id'][] = $pv['goods_id'];
        }
        $objGoods->update($data, $result);
        
        $this->end(true, app::get('b2c')->_('选中商品上架完成'));
    }

    function disable(){
        //@lujy--批量下架权限
        if(!$this->has_permission('batcheditmarketable')){
            $this->begin('');
            $this->end(false, app::get('b2c')->_('您无权批量操作商品下架'));
        }
        $this->begin('');
        $objGoods = &$this->app->model('goods');
        $glist = $objGoods->setEnabled($_POST,'false');
       
        $data['marketable_allow'] = 'false';
        if($_POST['goods_id'][0] == '_ALL_')  unset($_POST);
        $goods_id = $objGoods->getList('goods_id',$_POST);
        foreach($goods_id as $pk => $pv){
           $result['goods_id'][] = $pv['goods_id'];
        }
        $objGoods->update($data, $result);
        
        $this->end(true, app::get('b2c')->_('选中商品下架完成'));
    }


    function singleBatchEdit($editType=''){
        $objGoods = &$this->app->model('goods');
        $newFilter = $_POST;
        unset($newFilter['app']);
        unset($newFilter['ctl']);
        unset($newFilter['act']);
        unset($newFilter['_finder']);
        unset($newFilter['marketable']);
        unset($newFilter['_DTYPE_BOOL']);

        if($_POST['isSelectedAll'] == '_ALL_')
            $_POST['goods_id'][0] = '_ALL_';
        if(count($_POST['goods_id']) == 0 && $_POST['_finder']['select'] != 'multi' && !$_POST['_finder']['id'] && !$_POST['filter']){
            echo __('请选择商品记录');
            exit;
        }
        if($_POST['filter']){
            $_POST['_finder'] = unserialize($_POST['filter']);
            
            if(is_array($_POST['filter'])){
                $_POST['_finder']=$_POST['filter'];
                $_POST['cat_id'] = array($_POST['filter']['cat_id']);
            }

            $newFilter = $_POST['_finder'];

            if($_POST['updateAct']){
                 $editType = $_POST['updateAct'];
            }
        }
        if($_GET['cat_id']){
            $_POST['cat_id']=array($_GET['cat_id']);
        }

        $this->pagedata['editInfo'] = $objGoods->getBatchEditInfo($_POST);
        $oPro = &$this->app->model('products');
        $oLevel = &$this->app->model('member_lv');
        switch( $editType ){
            case 'uniformPrice':
                //@lujy--批量调价权限
                if(!$this->has_permission('batcheditprice')){
                    header('Content-Type:text/html; charset=utf-8');
                    echo app::get('b2c')->_("您无权批量操作统一调价");exit;
                }
                $priceList = array('mktprice'=>__('市场价'),'price'=>__('销售价'),'cost'=>__('成本价'));
                $levelList = $oLevel->getMLevel('member_lv_id,name',array('disabled' => 'false'));
                foreach($levelList as $v)
                    $priceList[$v['member_lv_id']] = $v['name'].__('价');

                $this->pagedata['updateName'] = $priceList;
                $this->pagedata['operator'] = array('+'=>'+','-'=>'-','*'=>'x');
                break;

            case 'differencePrice':
                //@lujy--批量调价权限
                if(!$this->has_permission('batcheditprice')){
                    header('Content-Type:text/html; charset=utf-8');
                    echo app::get('b2c')->_("您无权批量操作分别调价");exit;
                }
                $count = 0;
                $page = $_POST['pagenum']?$_POST['pagenum']:1;

                if( $_POST['pagenum'] ){

                    $oPro->batchUpdatePrice( $_POST['price'] );
                    $editType .= 'List';
                    $_POST = $_POST['_finder'];

                }

                if( empty( $_POST['cat_id'] ) || !$_POST['cat_id'][0] ){
                    unset($_POST['cat_id']);
                }
                if( empty( $_POST['goods_id'] ) || $_POST['goods_id'][0] == '_ALL_' ){
                    unset($_POST['goods_id']);
                }
                if($_POST['price']){
                    unset($_POST['price']);
                }

                $goodsList = $objGoods->getList('goods_id, name, bn, mktprice, cost,price',$_POST, ($page-1)*20, 20);

                $count = $objGoods->countGoods($_POST);
                $goodsId = array_map( create_function('$r','return$r["goods_id"];') ,$goodsList);

                $productList = $oPro->getProductLvPrice($goodsId);

                $levelList = $oLevel->getMLevel('member_lv_id,name',array('disabled' => 'false'));

                $pager = array(
                    'current'=> $page,
                    'total'=> ceil($count/20),
                    'link'=> 'javascript:$(\'pagenum\').value=_PPP_;W.page(\'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit\', {update:$(\'dialogContent\'), data:$(\'dialogContent\'), method:\'post\'});',
                    'token'=> '_PPP_'
                );
                $this->pagedata['levelList'] = $levelList;

                $this->pagedata['goodsList'] = $goodsList;
                $this->pagedata['productList'] = $productList;
                $this->pagedata['page'] = $page;
                $this->pagedata['pager'] = $pager;
                break;

            case 'uniformStore':
                //@lujy--批量调库存权限
                if(!$this->has_permission('batcheditstore')){
                    header('Content-Type:text/html; charset=utf-8');
                    echo app::get('b2c')->_("您无权批量操作统一调库存");exit;
                }
                $this->pagedata['operator'] = array('+'=>'+','-'=>'-');
                break;

            case 'differenceStore':
                //@lujy--批量调库存权限
                if(!$this->has_permission('batcheditstore')){
                    header('Content-Type:text/html; charset=utf-8');
                    echo app::get('b2c')->_("您无权批量操作分别调库存");exit;
                }
                $count = 0;
                $page = $_POST['pagenum']?$_POST['pagenum']:1;

                if( $_POST['pagenum'] ){
                    $oPro->batchUpdateStore( $_POST['store'] );
                    $oPro->synchronizationStore(array_keys($_POST['store']));
                    $editType .= 'List';
                    $_POST = $_POST['_finder'];
                }
                if( empty( $_POST['cat_id'] ) || !$_POST['cat_id'][0] ){
                    unset($_POST['cat_id']);
                }
                if( empty( $_POST['goods_id'] ) || $_POST['goods_id'][0] == '_ALL_' ){
                    unset($_POST['goods_id']);
                }

                // print_r($_POST);
                $goodsList = $objGoods->getList('goods_id, name, bn',$_POST, ($page-1)*20 , 20, $count);
                $count = $objGoods->countGoods($_POST);
                $goodsId = array_map( create_function('$r','return$r["goods_id"];') ,$goodsList);
                $productList = $oPro->getProductStore($goodsId);
                $this->pagedata['goodsList'] = $goodsList;
                $this->pagedata['productList'] = $productList;
                $pager = array(
                    'current'=> $page,
                    'total'=> ceil($count/20),
                    'link'=> 'javascript:$(\'pagenum\').value=_PPP_;W.page(\'index.php?app=b2c&ctl=admin_goods&act=singleBatchEdit\', {update:$(\'dialogContent\'), data:$(\'dialogContent\'), method:\'post\'});',
                    'token'=> '_PPP_'
                );
                $this->pagedata['page'] = $page;
                $this->pagedata['pager'] = $pager;
                break;

            case 'name':

                break;

            case 'cat':
                //@lujy--批量调库存权限
                if(!$this->has_permission('catgoods')){
                    header('Content-Type:text/html; charset=utf-8');
                    echo app::get('b2c')->_("您无权批量操作商品分类");exit;
                }
                $oCat = &$this->app->model('goods_cat');
                $catMap  = $oCat->getMapTree();
                $catList = array();
                foreach( $catMap as $v )
                    $catList[$v['cat_id']] = $v['pid']=='0'?$v['cat_name']:'``'.$v['cat_name'];
                $this->pagedata['cat'] =  $catList;
                break;

            case 'brief':

                break;

            case 'dorder':

                break;

            case 'brand':
                //@lujy--批量调品牌权限
                if(!$this->has_permission('brandgoods')){
                    header('Content-Type:text/html; charset=utf-8');
                    echo app::get('b2c')->_("您无权批量操作商品品牌");exit;
                }
                $oBrand = &$this->app->model('brand');
                $brandMap  = $oBrand->getAll();
                $brandList = array();
                foreach( $brandMap as $v )
                    $brandList[$v['brand_id']] = $v['brand_name'];
                $this->pagedata['brand'] =  $brandList;
                break;

            case 'score':
                $this->pagedata['operator'] = array('+'=>'+','-'=>'-','*'=>'x');
                break;

            case 'weight':
                $this->pagedata['operator'] = array('+'=>'+','-'=>'-','*'=>'x');
                break;
                
           
            case 'orderDown':
                if(!$this->has_permission('batcheditorderdown')){
                    header('Content-Type:text/html; charset=utf-8');
                    echo app::get('b2c')->_("您无权批量操作统一商品降权");exit;
                }
                $this->pagedata['operator'] = array('+'=>'+','-'=>'-');
                break;
            

        }
        unset($_POST['finder']);
        $this->pagedata['filter'] = htmlspecialchars(serialize($newFilter));
        $this->pagedata['finder'] = $_GET['finder'];
        #echo "<pre>";print_r($this->pagedata);exit;
        $this->display('admin/goods/batch/batchEdit'.ucfirst($editType).'.html');
    }

    function saveBatchEdit(){
        $this->begin('');
        $filter = unserialize($_POST['filter']);
        $oPro = &$this->app->model('products');
        $objGoods = &$this->app->model('goods');


//        if( !in_array( $_POST['updateAct'], array('differencePrice', 'differenceStore') ) && $filter['_finder']['select'] == 'multi' ){
            $filter['goods_id'] = $objGoods->getGoodsIdByFilter($filter);
//        }


        $haserror = false;

        switch( $_POST['updateAct'] ){
            case 'uniformPrice':
                if( is_numeric($_POST['updateName'][$_POST['updateType']]) ){ //修改会员价
                     $oPro->batchUpdateMemberPriceByOperator( $filter['goods_id'], $_POST['updateName'][$_POST['updateType']] ,abs(floatval(trim($_POST['set'][$_POST['updateType']]))), $_POST['operator'][$_POST['updateType']], $_POST['fromName'][$_POST['updateType']]  );
                }else{ //修改市场价 销售价 成本价
//                    $oPro->batchUpdateByOperator( $filter['goods_id'], 'sdb_goods', $_POST['updateName'][$_POST['updateType']] ,abs(floatval($_POST['set'][$_POST['updateType']])), $_POST['operator'][$_POST['updateType']], $_POST['fromName'][$_POST['updateType']] );

//                    if( $_POST['updateName'][$_POST['updateType']] != 'mktprice' ){
//                    $tableName = 'sdb_products';
//                    if( $_POST['fromName'][$_POST['updateType']] == 'mktprice' ){
//                        $tableName = 'sdb_goods a, sdb_products b';
//                        $_POST['updateName'][$_POST['updateType']] = 'b.'.$_POST['updateName'][$_POST['updateType']];
//                    }

                    foreach( array( 'sdb_b2c_goods','sdb_b2c_products' ) as $aTableName ){
                        $oPro->batchUpdateByOperator( $filter['goods_id'], $aTableName,$_POST['updateName'][$_POST['updateType']] ,abs(floatval(trim($_POST['set'][$_POST['updateType']]))), $_POST['operator'][$_POST['updateType']], $_POST['fromName'][$_POST['updateType']] );
                    }

//                    }
                }
                break;

            case 'differencePrice':
                $oPro->batchUpdatePrice( $_POST['price'] );
                break;

            case 'uniformStore':
                $oPro->batchUpdateByOperator( $filter['goods_id'], 'sdb_b2c_products', 'store' ,$_POST['set'][$_POST['updateType']], $_POST['operator'][$_POST['updateType']], $_POST['fromName'][$_POST['updateType']] );
                $oPro->synchronizationStore($filter['goods_id']);
                break;

            case 'differenceStore':
                $oPro->batchUpdateStore( $_POST['store'] );
                $oPro->synchronizationStore(array_keys($_POST['store']));
                break;

            case 'name':
                if( $_POST['updateType'] != 'name' || $_POST['set']['name'] != '' )
                    $oPro->batchUpdateText( $filter['goods_id'], $_POST['updateType'], 'name', $_POST['set'][$_POST['updateType']] );
                $oPro->syncProNameByGoodsId($filter['goods_id']);
                break;

            case 'cat':
                $oPro->batchUpdateInt( $filter['goods_id'], 'cat_id', intval($_POST['set']['cat']) );
                break;

            case 'brief':
                $oPro->batchUpdateText( $filter['goods_id'], $_POST['updateType'],'brief', $_POST['set'][$_POST['updateType']] );
                break;

            case 'brand':
                $oBrand = &$this->app->model('brand');
                $aBrand = $oBrand->dump(array('brand_id'=>$_POST['set']['brand']),'brand_name');
                $oPro->batchUpdateArray( $filter['goods_id'] , 'sdb_b2c_goods', array('brand_id'), array( intval($_POST['set']['brand']), $aBrand['brand_name'] ) );
                break;

            case 'dorder':
                $oPro->batchUpdateInt( $filter['goods_id'], 'd_order', intval($_POST['set']['dorder']) );
                break;

            case 'score':
                $oPro->batchUpdateByOperator( $filter['goods_id'], 'sdb_b2c_goods', 'score' ,abs(intval($_POST['set'][$_POST['updateType']])), $_POST['operator'][$_POST['updateType']] );
                break;

            case 'weight':

                $oPro->batchUpdateByOperator( $filter['goods_id'], 'sdb_b2c_goods', 'weight' ,abs(floatval($_POST['set'][$_POST['updateType']])), $_POST['operator'][$_POST['updateType']] );
                $oPro->batchUpdateByOperator( $filter['goods_id'], 'sdb_b2c_products', 'weight' ,abs(floatval($_POST['set'][$_POST['updateType']])), $_POST['operator'][$_POST['updateType']] );
                break;

           
            case 'enable':
                if(!$this->has_permission('batcheditmarketable')){
                    $this->end(false, app::get('b2c')->_('您无权批量操作商品上架'));
                }
                $glist = $objGoods->setEnabled(array('goods_id'=>$filter['goods_id']),'true');
                $data['marketable_allow'] = 'true';
                $data['marketable_content'] = $_POST['set']['marketable_content'];
                if($filter['goods_id'][0] == '_ALL_')  unset($filter);
                $goods_id = $objGoods->getList('goods_id',$filter);
                $date = time();
                $obj_apply = $this->app->model('goods_marketable_application');
                foreach($goods_id as $pk => $pv){
                   $result['goods_id'][] = $pv['goods_id'];
                   $apply_data = array();
                   $apply_data['goods_id'] = $pv['goods_id'];
                   $apply_data['status'] = '1';
                   $apply_data['restore'] = $data['marketable_content'];
                   $apply_data['audit_time'] = $date;
                   $apply_data['audit_user'] = $this->user->user_id;
                   $apply_data['last_modify'] = $date;
                   $obj_apply->insert($apply_data);
                }
                $objGoods->update($data, $result);
                $this->end(true, app::get('b2c')->_('选中商品上架完成'));
                break;
                
            case 'disable':
                if(!$this->has_permission('batcheditmarketable')){
                    $this->end(false, app::get('b2c')->_('您无权批量操作商品下架'));
                }
                $glist = $objGoods->setEnabled(array('goods_id'=>$filter['goods_id']),'false');
                $data['marketable_allow'] = 'false';
                $data['marketable_content'] = $_POST['set']['marketable_content'];
                if($filter['goods_id'][0] == '_ALL_')  unset($filter);
                $goods_id = $objGoods->getList('goods_id',$filter);
                $date = time();
                $obj_apply = $this->app->model('goods_marketable_application');
                foreach($goods_id as $pk => $pv){
                   $result['goods_id'][] = $pv['goods_id'];
                   $apply_data = array();
                   $apply_data['goods_id'] = $pv['goods_id'];
                   $apply_data['status'] = '2';
                   $apply_data['restore'] = $data['marketable_content'];
                   $apply_data['audit_time'] = $date;
                   $apply_data['audit_user'] = $this->user->user_id;
                   $apply_data['last_modify'] = $date;
                   $obj_apply->insert($apply_data);
                }
                $objGoods->update($data, $result);
                $this->end(true, app::get('b2c')->_('选中商品下架完成'));
                break;
                
            case 'orderDown':
                $oPro->batchUpdateByOperator( $filter['goods_id'], 'sdb_b2c_goods', 'goods_order_down' ,abs(floatval($_POST['set'][$_POST['updateType']])), $_POST['operator'][$_POST['updateType']] );
                break;
            
        }

        ini_set('track_errors','1');
        restore_error_handler();
        if(!$haserror){
            $this->end(true, app::get('b2c')->_('保存成功'));
        }else{
            echo $GLOBALS['php_errormsg'];
        }
    }


    function batchImage(){
        $goods = &$this->app->model('goods');
        $count = $goods->count($_POST);
        $this->pagedata['goodscount'] = $count;
        $this->pagedata['filter'] = $_POST;
        $this->display('admin/goods/batch/batchImage.html');
    }

    function nextImage($same_file_name=false){
        $filter = $_POST;
        $goods = &$this->app->model('goods');
        $oImage = &app::get('business')->model('image');
        $goodsList = $goods->getList('goods_id,udfimg',$filter,intval($_POST['present_id']),1);
        $subsdf = array(
            'images' => array('*',array('image'=>array('*')))
        );
        $aGoods = $goods->dump($goodsList[0]['goods_id'],'*',$subsdf);
        if(is_array($aGoods['images'])){
            foreach($aGoods['images'] as $mk=>$mv){
                $test = $oImage->delete_image($mv['image_id'],'goods',$aGoods['store_id'],0); 
                    $test = $oImage->rebuild($mv['image_id'],array('S','M','L'),true,$aGoods['store_id']);
            }
        }
        $_POST['present_id'] = $_POST['present_id']+1;
        usleep(20);
        header('Content-Type: text/html;charset=utf-8');
        if($_POST['present_id']<=$_POST['allcount']){
            echo __('<font color="red">正在重新生成商品图片：').$_POST['present_id'].'/'.$_POST['allcount'].'</font><script>batchImage_rebulidRequest('.json_encode($_POST).')</script>';
        }else{
            echo __('<font color="green">图片生成完毕</font>').__('<script>$("batchImage_rebulid").retrieve("closebtn").setStyle("visibility","visible");$("batchImage_rebulid").getElement(".btnbuild").removeEvents().set("html","<span><span>完成</span></span>").addEvent("click",function(){$("batchImage_rebulid").retrieve("closebtn").fireEvent("click")});</script>');
        }
    }

    function finder_goods_select($page=1){
        $this->get_finder_goods_items($page,$_POST['isgroupbuy'],$_POST['istimedbuy']);
        $this->pagedata['isgroupbuy'] = $_POST['isgroupbuy'];
        $this->pagedata['istimedbuy'] = $_POST['istimedbuy'];
        $this->pagedata['timedbuy'] = $_POST['timedbuy'];

        /** 得到商品的全部分类  根据catid 获取分类**/
        $o_cat = $this->app->model('goods_cat');
        if($_POST['cat_id']){
            $arr_cats = $o_cat->getList('cat_id,cat_name',array('parent_id'=>'0','cat_id'=>$_POST['cat_id']),0,-1,'p_order ASC,cat_id ASC');
        }else{
            $arr_cats = $o_cat->getList('cat_id,cat_name',array('parent_id'=>'0'),0,-1,'p_order ASC,cat_id ASC');
        }
        if ($arr_cats){
            foreach ($arr_cats as $key=>$cat){
                $arr_sub_cats = $o_cat->get_subcat_list($cat['cat_id']);
                $arr_cats[$key]['sub_cats'] = $arr_sub_cats;
            }
        }
        $this->pagedata['cats'] = $arr_cats;
        if ($_GET['ids']&&$_POST['widgets']){
            /** 获取选中的商品信息 **/
            $id = explode(',',$_GET['ids']);
            $this->pagedata['selected_cnt'] = count($id);
            $o = $this->app->model('goods');
            $goods = $o->getList('*',array('goods_id|in'=>$id));

            if(!empty($id)){
                $goods_temp = array();
                foreach($id as $k=>$v){
                    foreach($goods as $row){
                        if($v == $row['goods_id']){
                            $goods_temp[$k] = $row;
                        }
                    }
                }
                unset($goods);
                $goods = $goods_temp;
                unset($goods_temp);
            }
            $arr_widgets = json_decode($_POST['widgets'],true);
            foreach ($goods as $key=>$ids){
                $goods[$key]['alias'] = $arr_widgets[$key]['nice'];
                $goods[$key]['pic'] = $arr_widgets[$key]['pic'];
            }

            $this->pagedata['goods_selected'] = $goods;
            $this->pagedata['product_ids'] = $_GET['ids'];
            /** end **/
        }

        /** 得到所有的tags **/
        $o_tag = app::get('desktop')->model('tag');
        $this->pagedata['tags'] = $o_tag->getList('tag_id,tag_name');
        /** end **/
        // 将cat_id传到页面
        if($_POST['cat_id']){
            $this->pagedata['cat_filter'] = $_POST['cat_id'];
        }

        $this->display('admin/goods/goods_select.html');
    }

    function finder_goods_items($page=1){
        $this->get_finder_goods_items($page,$_GET['isgroupbuy'],$_GET['istimedbuy']);
        echo $this->fetch('admin/goods/goods_select_body.html');exit;
    }

    private function get_finder_goods_items($page=1,$isgroupbuy='false',$istimedbuy='false'){
        /**
         * 过滤base filter其中提交过来的obj_filter
         */
        $base_filter = array();
        $arr_obj_filter = array();
        // 获取所有分类下的子分类
        $o_cat = $this->app->model('goods_cat');
        if($_POST['cat_id']){
            $arr_cats = $o_cat->getList('cat_id',array('parent_id'=>'0','cat_id'=>$_POST['cat_id']),0,-1,'p_order ASC,cat_id ASC');
        }else{
            $arr_cats = $o_cat->getList('cat_id',array('parent_id'=>'0','cat_id'=>$_GET['cat_filter']),0,-1,'p_order ASC,cat_id ASC');
        }
        if ($arr_cats){
            foreach ($arr_cats as $key=>$cat){
                //$arr_sub_cats = $o_cat->get_subcat_list($cat['cat_id']);
                $arr_sub_cats = $o_cat->db->select("select * FROM sdb_b2c_goods_cat WHERE cat_path like '%,".$cat['cat_id'].",%' ORDER BY p_order,cat_id");
                $arr_cats[$key]['sub_cats'] = $arr_sub_cats;
            }
        }
        
        $cat_ids = array($arr_cats[0]['cat_id']);
        foreach($arr_cats[0]['sub_cats'] as $k=>$v){
            $cat_ids[] = $v['cat_id'];
        }

        //end
        if (isset($_GET['obj_filter'])&&$_GET['obj_filter']){
            $arr_obj_filter = explode('&',$_GET['obj_filter']);
            foreach ($arr_obj_filter as $obj_filter){
                $arr = explode('=',$obj_filter);
                $base_filter[$arr[0]] = $arr[1];
            }
        }


        $page_link = 'index.php?app=b2c&ctl=admin_goods&act=finder_goods_items';

        if (isset($_GET['goods_filter'])&&$_GET['goods_filter']){
            $arr_obj_filter = explode('|',$_GET['goods_filter']);
            foreach ($arr_obj_filter as $obj_filter){
                $arr = explode('=',$obj_filter);
                $base_filter[$arr[0]] = $arr[1];
            }
            //增加活动状态的过滤
            if(isset($base_filter['act_type']) && $base_filter['act_type'] != 'normal'){
                $base_filter['act_type'] = explode('_',$base_filter['act_type']);
                $act_status = isset($base_filter['act_type'][1])?$base_filter['act_type'][1]:1;
                $base_filter['act_type'] = $base_filter['act_type'][0];
                switch($base_filter['act_type']){
                    case 'timedbuy':
                        $applyObj = app::get('timedbuy')->model('businessactivity');
                        $goods_ids = $applyObj -> getGidByTimedbuy($act_status);
                        break;
                    case 'group':
                        $applyObj = app::get('groupbuy')->model('groupapply');
                        $goods_ids = $applyObj -> getOnActGIdByStatus($act_status);
                        break;
                    case 'spike':
                        $applyObj = app::get('spike')->model('spikeapply');
                        $goods_ids = $applyObj -> getOnActGIdByStatus($act_status);
                        break;
                    case 'score':
                        $applyObj = app::get('scorebuy')->model('scoreapply');
                        $goods_ids = $applyObj -> getOnActGIdByStatus($act_status);
                        break;
                }
            }
            $page_link .= '&goods_filter='.$_GET['goods_filter'];
            $this->pagedata['goods_filter'] = $_GET['goods_filter'];
        }
        
        if($_POST['cat_id']){
            $base_filter['cat_id|in'] = $cat_ids;
            $page_link .= '&cat_filter='.$_POST['cat_id'];
        }

        if($_POST['timedbuy']){
            $base_filter['act_type'] = 'timedbuy';
            $page_link .= '&timedbuy='.$_POST['timedbuy'];
        }

        if($_GET['cat_filter']){
            $base_filter['cat_id|in'] = $cat_ids;
            $page_link .= '&cat_filter='.$_GET['cat_filter'];
        }
        /** 分类筛选 **/
        if ($_GET['cat_id']){
            $base_filter['cat_id'] = $_GET['cat_id'];
            $page_link .= '&cat_id='.$base_filter['cat_id'];
        }

        if($_GET['timedbuy']){
            $base_filter['act_type'] = 'timedbuy';
            $page_link .= '&timedbuy='.$_GET['timedbuy'];
        }

        /** 标签筛选 **/
        if ($_GET['tag_id']){
            $o_tag = app::get('desktop')->model('tag_rel');
            $arr_tags = $o_tag->getList('rel_id',array('tag_id'=>$_GET['tag_id'],'tag_type'=>'goods'));
            $base_filter['goods_id|in'] = array_map('current',$arr_tags);
            $page_link .= '&tag_id='.$_GET['tag_id'];
            if($_GET['cat_filter']){
                $base_filter['cat_id|in'] = $cat_ids;
                $page_link .= '&cat_filter='.$_GET['cat_filter'];
            }
        }

        /** 获取挂件的信息 **/
        $arr_widgets = array();
        if ($_POST['widgets']){
            $arr_widgets = json_decode($_POST['widgets'],true);
        }

        /** 商品名称模糊搜索 **/
        $name = trim(urldecode($_GET['name']));
        if ($name){
            $base_filter['name|has'] = $name;
            $page_link .= '&name='.$name;
            if($_GET['cat_filter']){
                $base_filter['cat_id|in'] = $cat_ids;
                $page_link .= '&cat_filter='.$_GET['cat_filter'];
            }
        }

        /** 得到全部商品 **/
        $o = $this->app->model('goods');
        $limit = 5;
        //if($isgroupbuy=='true'){
            //$o_group = app::get('groupactivity')->model('purchase');
            //$group_goods_ids = $o_group->getList('gid',array('state|in'=>array('1','2'),'act_open'=>'true'));
            //$goods_ids = '';
            //foreach($group_goods_ids as $gk=>$gv){
                //$goods_ids[] = $gv['gid'];
            //}
            //$base_filter['goods_id|in'] = $goods_ids;
            //$page_link .= '&isgroupbuy=true';
        //}
        //if($istimedbuy=='true'){
            //$sql="select gid from sdb_timedbuyactivity_activity as a left join sdb_timedbuyactivity_activity_goods as g using(act_id) where a.notice_time<=".time()." and a.end_time>=".time()." and a.act_open=true and g.buy_num<g.max_buy_num order by act_id desc";
            //$timedbuy_goods_ids=kernel::database()->select($sql);
            //$goods_ids = '';
            //foreach($timedbuy_goods_ids as $gk=>$gv){
                //$goods_ids[] = $gv['gid'];
            //}
            //$base_filter['goods_id|in'] = $goods_ids;
            //$page_link .= '&istimedbuy=true';
        //}

        //增加活动状态的过滤
        if (isset($goods_ids) && $goods_ids){
            $base_filter['goods_id']=$goods_ids;
        }
        $totalPage = $o->count($base_filter);
        $goods_arr = $o->getList('*',$base_filter,($page-1)*$limit,$limit,'d_order DESC,goods_id DESC');
        
        $this->pagedata['goods'] = $goods_arr;

        if ($arr_widgets){
            $arr_goods_id = array_map('current',$arr_widgets);
            foreach((array)$this->pagedata['goods'] as $key=>$arr){
                $k = array_search($arr['goods_id'],$arr_goods_id);
                if ($k===false) continue;
                $this->pagedata['goods'][$key]['alias'] = $arr_widgets[$k]['nice'];
                $this->pagedata['goods'][$key]['pic'] = $arr_widgets[$k]['pic'];
            }
        }

        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];

        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($totalPage/$limit),
            'link' =>$page_link.'&p[0]='.($tmp=time()),
            'token'=>$tmp,
        );
        /** end **/
    }

   
    function update_apply(){
        $this->begin('');
        if(!$_POST['gid']){
            $this->end(true, app::get('b2c')->_('参数错误'));
        }
        $objGoods = $this->app->model('goods');
        $goods = $objGoods->getList('goods_id',array('goods_id'=>intval($_POST['gid']),'marketable'=>'false','marketable_allow'=>'false'));
        $goods_id = $goods[0]['goods_id'];
        if(!$goods_id){
            $this->end(true, app::get('b2c')->_('参数错误'));
        }
        $obj_apply = app::get('b2c')->model('goods_marketable_application');
        $date = time();
        $apply_data = array();
        $apply_data['goods_id'] = $goods_id;
        $apply_data['status'] = intval($_POST['status'])==1?'1':'2';
        $apply_data['restore'] = $_POST['restore'];
        $apply_data['audit_time'] = $date;
        $apply_data['audit_user'] = $this->user->user_id;
        $apply_data['last_modify'] = $date;
        $obj_apply->update($apply_data,array('goods_id'=>$goods_id,'status'=>'0'));
        $objGoods->update(array('marketable_allow'=>($apply_data['status']=='1'?'true':'false')),array('goods_id'=>$goods_id));
        $this->end(true, app::get('b2c')->_('审核成功'));
    }
    
}
