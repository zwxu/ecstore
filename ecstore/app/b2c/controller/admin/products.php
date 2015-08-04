<?php
 

class b2c_ctl_admin_products extends desktop_controller{
    function set_spec_index(){
        if(!$this->has_permission('editgoods')){//没有编辑权限则没有编辑货品权限
            header('Content-Type:text/html; charset=utf-8');
            echo app::get('desktop')->_("您无权操作");exit;
        }
        if($_GET['goods']['images']){
            $this->pagedata['goods_spec_images'] = $_GET['goods']['images'];
        }else{
            $oImage = app::get('image')->model('image_attach');
            $image_arr = $oImage->getList('image_id',array('target_id'=>$_GET['goods_id'],'target_type'=>'goods'));
            $image_arr_tmp = array();
            foreach($image_arr as $k=>$v){
                $image_arr_tmp[] = $v['image_id'];
            }
            $this->pagedata['goods_spec_images'] = $image_arr_tmp;
        }
        $goods_id = $_GET['goods_id'];
        $oGoods = &$this->app->model('goods');
        if($_GET['nospec'] == 1){
            $this->pagedata['type_id'] = $_GET['type_id'];
            $this->pagedata['params_spec'] = json_encode(array());
        }
        else{
            $goods = $oGoods->dump($goods_id,'goods_id,type_id,spec_desc');
            $this->pagedata['params_spec'] = json_encode($goods['spec']);
            $this->pagedata['type_id'] = $goods['type']['type_id'];
        }
        $this->pagedata['spec_image_request_url'] = "&quot;index.php?app=desktop&act=alertpages&goto=".urlencode("index.php?app=image&ctl=admin_manage&act=image_broswer")."&quot;";
        $this->pagedata['goods_id'] = $goods_id;
        $this->singlepage('admin/goods/detail/spec/set_spec.html');
    }
    function set_spec(){

        $goods_id = $_POST['goods_id'];
        $spec_goods_images = app::get('image')->model('image_attach')->getList('image_id',array('target_id'=>$goods_id));
        $typeId = $_GET['type_id'];
        if(is_string($_POST['spec'])){
            $_POST['spec'] = json_decode($_POST['spec'],1);
        }

        if( $_POST['spec'] && count($_POST['spec'])){
            $aReturn = $this->_set_spec($_POST['spec']);
        }else{
            $aReturn = $this->_set_type_spec($typeId);
        }
        echo json_encode($aReturn);
        //$this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');
        //$this->singlepage('admin/goods/detail/spec/set_spec.html');
    }

    function _set_type_spec($typeId){
        $oGtype = &$this->app->model('goods_type');
        $spec = (array)$oGtype->dump($typeId,'type_id',array(
                'spec'=>array('spec_id',
                    array(
                        'spec:specification'=>array('*',
                            array(
                                'spec_value' =>array('*')
                            )
                        )
                    )
                )
            )
        );

        $aSpec = array();
        foreach($spec['spec'] as $k1=>$v1){
            $spec_values = array();
            foreach($v1['spec']['spec_value'] as $k2=>$v2){
                if($v1['spec']['spec_type'] == "image"){
                    $v2['color'] = $v2['spec_value'];
                    $v2['view'] = base_storager::image_path($v2['spec_image'],'s');
                    $spec_values[] = $v2;
                }
                else{
                    $spec_values[] = $v2;
                }
            }


            $aSpec[$v1['spec_id']]['spec_id'] = $v1['spec']['spec_id'];
            $aSpec[$v1['spec_id']]['text'] = $v1['spec']['spec_name'];
            $aSpec[$v1['spec_id']]['spec_type'] = $v1['spec']['spec_type'];
            $aSpec[$v1['spec_id']]['tp'] = 'tp'.$v1['spec_id'];
            $aSpec[$v1['spec_id']]['index'] = $v1['spec_id'];
            $aSpec[$v1['spec_id']]['value'] = $spec_values;
            $spec['spec'][$k1] = $v1;
        }
        sort($aSpec);
        return array('all_use_spec'=>array(), 'spec_info'=>array(), 'spec'=>$aSpec,'selectedSpec'=>array(),'products'=>array());
    }

    function _set_spec($spec){
        $oSpec = &$this->app->model('specification');
        $subSdf = array(
            'spec_value' =>array('*')
        );
        $specdata = $specinfo = $_POST['spec'];
        $default_spec_image = $this->app->getConf('spec.default.pic');
        $tmp_spec_goods_imgsrc = array();
        foreach($specinfo as $k=>$v){
            foreach($v['option'] as $k2=>$v2){
                $v['option'][$k2]['spec_goods_imagesrc'] = base_storager::image_path($v2['spec_image']&&$v2['spec_image']!='null'?$v2['spec_image']:$default_spec_image);
                $tmp_spec_goods_img[$v2['private_spec_value_id']] = $v2['spec_goods_images']&&$v2['spec_goods_images']!='null'?$v2['spec_goods_images']:''; // modified by cam
                $aSpecTmp = explode(",", $tmp_spec_goods_img[$v2['private_spec_value_id']]);
                if($aSpecTmp){
                    foreach($aSpecTmp as $ks=>$vs){
                        $tmp_spec_goods_imgsrc[$v2['private_spec_value_id']][] = base_storager::image_path($vs&&$vs!='null'?$vs:$default_spec_image);
                    }
                }
                else{
                    $tmp_spec_goods_imgsrc[$v2['private_spec_value_id']][] = base_storager::image_path($default_spec_image);
                }
            }
            sort($v['option']);
            $specinfo[$k] = $v;
        }
        $specifications = $oSpec->batch_dump( array('spec_id'=>array_keys($spec)), '*' , $subSdf, 0 ,-1 );
        $aSpec = array();
        $selectedSpec = array();
        $i = 0;

        foreach($specifications as $k1=>$v1){
            $spec_values = array();
            $j = 0;
            foreach($v1['spec_value'] as $k2=>$v2){
                if($v1['spec_type'] == "image"){
                    $v2['color'] = $v2['spec_value'];
                    $v2['view'] = base_storager::image_path($v2['spec_image'],'s');
                }

                foreach($spec[$v1['spec_id']]['option'] as $k3=>$v3){
                    if($v3['spec_value_id'] == $v2['spec_value_id']){
                        $v2['private_spec_value_id'] = $v3['private_spec_value_id'];
                        $tmp_specvalue = array(
                            'spec_type'=>$v1['spec_type'],
                            'spec_id'=>$v1['spec_id'],
                            'spec_name'=>$v1['spec_name'],
                            'trSpec'=>'tp'.$v1['spec_id'],
                            'trPoint'=>'tp'.$v1['spec_id'].$j,
                            'spec_value_id'=>$v3['spec_value_id'],
                            'private_spec_value_id'=>$v3['private_spec_value_id'],
                            'spec_value'=>$v2['spec_value'],
                            'spec_goods_images'=>explode(",",$tmp_spec_goods_img[$v3['private_spec_value_id']]),
                            'spec_goods_images_url'=>$tmp_spec_goods_imgsrc[$v3['private_spec_value_id']]
                        );
                        if($v1['spec_type'] == "image"){
                            $tmp_specvalue['spec_image'] = $v2['spec_image'];
                            $tmp_specvalue['has_img']['spec_image_url'] = base_storager::image_path($v2['spec_image']&&$v2['spec_image']!='null'?$v2['spec_image']:$default_spec_image,'s');
                        }
                        $selectedSpec[]['specValue'] = $tmp_specvalue;
                    }
                }
                $spec_values[] = $v2;
                $j++;
            }

            $aSpec[$v1['spec_id']]['spec_id'] = $v1['spec_id'];
            $aSpec[$v1['spec_id']]['text'] = $v1['spec_name'];
            $aSpec[$v1['spec_id']]['spec_type'] = $v1['spec_type'];
            $aSpec[$v1['spec_id']]['tp'] = 'tp'.$v1['spec_id'];
            $aSpec[$v1['spec_id']]['index'] = $i;
            $aSpec[$v1['spec_id']]['value'] = $spec_values;
            $spec[$k1] = $v1;
            $i++;
        }


        //if(isset($_POST['goods_id']) && $_POST['goods_id']){
        $products = $this->getProducts($_POST['goods_id'], $_POST['product'],$specdata);

        //}
        sort($aSpec);

        return array('all_use_spec'=>$this->get_all_spec($all_use_spec,$_POST['spec']),'spec_info'=>$specinfo, 'spec'=>$aSpec,'selectedSpec'=>$selectedSpec,'product'=>$products);
    }

    function doAddSpec(){
        $oImage = app::get('image')->model('image');//fetch($_POST['']);
        $defalut_spec_image = $this->app->getConf('spec.default.pic');
        $this->pagedata['goods']['spec'] = $_POST['spec'];
        foreach($this->pagedata['goods']['spec'] as $k=>$v){
            foreach($v['option'] as $k2=>$v2){
                if(!$v2['spec_value'] && $v2['spec_value'] !== "0") {
                    echo json_encode(array("msg"=>app::get('b2c')->_('新增规格项未填写规格值，请填写')));
                    exit;
                }
                $v['option'][$k2]['spec_goods_imagesrc'] = base_storager::image_path($v2['spec_image']&&$v2['spec_image']!='null'?$v2['spec_image']:$defalut_spec_image);
            }
            sort($v['option']);
            $this->pagedata['goods']['spec'][$k] = $v;
        }
        if( $_GET['create'] == 'true' ){
            $spec_names = array();
            foreach($_POST['spec'] as $v){
                $spec_names[$v['spec_id']] = $v['spec_name'];
            }
            $pro = $this->_doCreatePro( $pro, $_POST['spec'], $spec_names, $all_spec);
            $this->pagedata['fromType'] = 'create';
            foreach($pro as $k=>$v){
                //$pro[$k]['spec_desc']['a']['k'] = array_keys($v['spec_desc']['spec_value']);
            }

            $memberLevel = &$this->app->model('member_lv');
            $mLevels = $memberLevel->getList('member_lv_id');
            foreach($pro as $k=>$v) {
                $pro[$k]['mLevelPrice'] = $mLevels;
                $pro[$k]['marketable'] = 'true'; //默认上架商品
            }
            $this->pagedata['goods']['product'] = $pro;
        }
        $aReturn = $this->_set_spec( $_POST['spec'] );
        $this->pagedata['spec_tmpl'] = $this->pagedata['spec'];
        //$this->pagedata['needUpValue'] = json_encode($_POST['needUpValue']);
//        $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');
        $this->pagedata['app_dir'] = app::get('b2c')->app_dir;
        $data = array('goods'=>array('all_use_spec'=>$all_spec, 'spec'=>$this->pagedata['goods']['spec'],'products'=>$this->pagedata['goods']['product']));
        echo json_encode(array('goods'=>array('all_use_spec'=>$all_spec, 'spec'=>$this->pagedata['goods']['spec'],'products'=>$this->pagedata['goods']['product'])));
        $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');

        //$this->display('admin/goods/detail/spec/spec.html');
    }

    function _doCreatePro( $pro, $spec, $spec_names, &$all_spec=array() ){
        if( empty( $spec ) ){
            $defalut_spec_image = $this->app->getConf('spec.default.pic');
            $res = array();
            foreach( $pro as $pk => $pv ){
                $res['new_'.$pk]['product_id'] = 'new_'.$pk;
                foreach( $pv as $pvk => $pvv ){

                    $res['new_'.$pk]['spec_desc'][] = array(
                        'spec_value'=>$pvv['spec_value'],
                        'spec_id'=>$pvv['spec_id'],
                        'spec_private_value_id'=>$pvv['private_spec_value_id'],
                        'spec_value_id'=>$pvv['spec_value_id'],
                        'spec_name'=>$spec_names[$pvv['spec_id']],
                        'spec_image'=>$pvv['spec_image'],
                        'spec_image_url'=>base_storager::image_path($pvv['spec_image']&&$pvv['spec_image']!='null'?$pvv['spec_image']:$defalut_spec_image),
                    );
                    $all_spec[$pk][$pvv['spec_id']] =  $pvv['private_spec_value_id'];

                    //$res['new_'.$pk]['spec_desc']['spec_type']['s'][] = array('id'=>$pvv['spec_id'],'value'=>$spec_names[$pvv['spec_id']]);
                }
            }
            return $res;
        }
        $firstSpec = array_shift( $spec );

        $rs = array();
        foreach( $firstSpec['option'] as $sitem ){
            foreach( (array)$pro as $pitem ){
                $apitem = $pitem ;
                array_push( $apitem , array('spec_id'=>$firstSpec['spec_id']) + $sitem );
                $rs[] = $apitem;
            }
            if( empty($pro) )
                $rs[] = array( array_merge( array('spec_id'=>$firstSpec['spec_id']) , $sitem) );
        }

       return $this->_doCreatePro( $rs, $spec, $spec_names, $all_spec);
    }

    function getProducts($gid=0, $pdata=array(), $specdata=array()){
        if($gid){
            $defalut_spec_image = $this->app->getConf('spec.default.pic');
            $oGoods = app::get('b2c')->model('goods');
            $goodsinfo = $oGoods->dump($gid,'goods_id,spec_desc',
                            array('product'=>array('product_id,bn,price,cost,mktprice,store,store_place,weight,marketable,spec_desc',
                            array('price/member_lv_price'=>array('*'))
                        )));

            $products = $goodsinfo['product'];
            $spec_desc = $goodsinfo['spec_desc'];
        }
        else{
            $products = $pdata;
        }

        $returnValue = array();

        $goods_lv_price = $this->app->model('goods_lv_price')->getList('level_id,price,product_id',array('goods_id'=>$gid));
        if($goods_lv_price) {
            foreach($goods_lv_price as $k=>$v) {
                $goods_lv_price[$v['product_id']][$v['level_id']] = $v;
                unset($goods_lv_price[$k]);
            }
        }

        if(!$gid){
            foreach($products as $k=>$v){
                $mLevelPrice = array();
                if(!$gid){
                    foreach($v['price']['member_lv_price'] as $k2=>$v2) {
                        $products[$k]['price']['member_lv_price'][$k2] = array('price'=>$v2,'level_id'=>$k2, 'product_id'=>$v['product_id']);

                    }
                }
            }
        }

        foreach($products as $k=>$v){
            $mLevelPrice = array();
            foreach($v['price']['member_lv_price'] as $k2=>$v2) {
                $mLevelPrice[$k2]['member_lv_id'] = $v2['level_id'];
                if($gid){
                    if(isset($goods_lv_price[$v['product_id']]) && isset($goods_lv_price[$v['product_id']][$v2['level_id']])){
                        $mLevelPrice[$k2]['ml_price'] = $goods_lv_price[$v['product_id']][$v2['level_id']]['price'];
                    }
                }
                else{
                    $mLevelPrice[$k2]['ml_price'] = $v2['price'];
                }

            }
            sort($mLevelPrice);
            $returnValue[$v['product_id']] = array(
                'product_id'=>$v['product_id'],
                'bn'=>$v['bn'],
                'store'=>$v['store'],
                'price'=>$v['price']['price']['price'],
                'cost'=>$v['price']['cost']['price'],
                'mktprice'=>isset($v['price']['mktprice']['price'])?$v['price']['mktprice']['price']:$v['mktprice'],
                'mlv_price'=>$v['price']['member_lv_price'],
                'weight'=>$v['weight'],
                'store_place'=>$v['store_place'],
                'marketable'=>$v['status'],
                'mLevelPrice'=>$mLevelPrice,
            );

            $spec_info = isset($goodsinfo['spec'])?$goodsinfo['spec']:$specdata;
            $spec_desc = $v['spec_desc'];
            $spec = array();
            foreach($spec_desc['spec_value'] as $k2=>$v2){
                $spec[$k2]['spec_id'] = $k2;
                $spec[$k2]['spec_value'] = $v2;
                $spec[$k2]['spec_name'] = $spec_info[$k2]['spec_name'];
            }
            foreach($spec_desc['spec_private_value_id'] as $k2=>$v2){
                $spec_image = $spec_info[$k2]['option'][$v2]['spec_image'];

                $spec[$k2]['spec_private_value_id'] = $v2;
                $spec[$k2]['spec_image'] = $spec_image;
                $spec[$k2]['spec_image_url'] = base_storager::image_path($spec_image&&$spec_image!='null'?$spec_image:$defalut_spec_image);
            }
            foreach($spec_desc['spec_value_id'] as $k2=>$v2){
                $spec[$k2]['spec_value_id'] = $v2;
            }
            sort($spec);
            $returnValue[$v['product_id']]['spec_desc'] = $spec;
        }
        sort($returnValue);
        return $returnValue;
    }

    function addSpecValue(){
        $_POST = utils::stripslashes_array($_POST);

        $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');

        $aTmp = explode(",",$_POST['specGoodsImages']);
        $goods_spec_images = array();
        $goods_spec_images_url = array();
        foreach($aTmp as $k=>$v){
            $goods_spec_images[] = $v;
            $goods_spec_images_url[] = base_storager::image_path($v&&$v!=''?$v:$this->pagedata['spec_default_pic']);
        }

        $specValue = array(
            'spec_type' => $_POST['specType'],
            'spec_id' => $_POST['specId'],
            'spec_name' => $_POST['specName'],
            'trSpec' => $_POST['trSpec'],
            'trPoint' => $_POST['trPoint'],
            'spec_value_id' => $_POST['specValueId'],
            'spec_value' => $_POST['spec_value'],
            'private_spec_value_id'=>time().$_POST['specValueId'],
            'spec_goods_images'=>$goods_spec_images,
            'spec_goods_images_url'=>$goods_spec_images_url
        );

        if($_POST['specType'] == "image") {
            $specValue['spec_image'] = $_POST['specImage'];
            $specValue['has_img']['spec_image_url'] = base_storager::image_path(isset($_POST['specImage'])&&$_POST['specImage']!=''?$_POST['specImage']:$this->pagedata['spec_default_pic']);
        }

        $specinfo = $_POST['spec'];
        $specinfo[$specValue['spec_id']]['option'][$specValue['private_spec_value_id']] = array(
            'private_spec_value_id'=>$specValue['private_spec_value_id'],
            'spec_value'=>$specValue['spec_value'],
            'spec_value_id'=>$specValue['spec_value_id'],
            'spec_image'=>$specValue['spec_image'],
            'spec_goods_image'=>$specValue['spec_goods_image']
        );

        foreach($specinfo as $k=>$v){
            $res['spec_desc'][] = array(
                'spec_name'=>$v['spec_name'],
                'spec_id'=>$v['spec_id'],
                'spec_value'=>'',
                'spec_private_value_id'=>'',
                'spec_value_id'=>'',
                'spec_image'=>'',
                'spec_image_url'=>base_storager::image_path($defalut_spec_image),
            );
            foreach($v['option'] as $k2=>$v2){
                $v['option'][$k2]['spec_goods_imagesrc'] = base_storager::image_path($v2['spec_image']&&$v2['spec_image']!='null'?$v2['spec_image']:$defalut_spec_image);
            }
            sort($v['option']);
            $specinfo[$k] = $v;
        }




        echo json_encode (array('spec'=>$specinfo, 'specValue'=>$specValue,'spec_default_pic'=>base_storager::image_path($this->pagedata['spec_default_pic'])));
    }

    function addProduct(){
        $product_id = 'new_'.$_GET['product_id'];
        $specinfo = $_POST['spec'];
        $defalut_spec_image = $this->app->getConf('spec.default.pic');
        $memberLevel = &$this->app->model('member_lv');
        $mLevels = $memberLevel->getList('member_lv_id');

        $res['product_id'] = $product_id;
        $res['marketable'] = 'true';
        $res['mLevelPrice'] = $mLevels;

        foreach($specinfo as $k=>$v){
            foreach($v['option'] as $ks=>$vs) {
                if(!$vs['spec_value'] && $vs['spec_value'] !== "0") {
                    echo json_encode(array("msg"=>app::get('b2c')->_('新增规格项未填写规格值，请填写')));
                    exit;
                }
            }
            $res['spec_desc'][] = array(
                'spec_name'=>$v['spec_name'],
                'spec_id'=>$v['spec_id'],
                'spec_value'=>'',
                'spec_private_value_id'=>'',
                'spec_value_id'=>'',
                'spec_image'=>'',
                'spec_image_url'=>base_storager::image_path($defalut_spec_image),
                'marketable'=>true,
            );
            foreach($v['option'] as $k2=>$v2){
                $v['option'][$k2]['spec_goods_imagesrc'] = base_storager::image_path($v2['spec_image']&&$v2['spec_image']!='null'?$v2['spec_image']:$defalut_spec_image);
            }
            sort($v['option']);
            $specinfo[$k] = $v;
        }

        echo json_encode(array('product'=>$res,'spec'=>$specinfo,'all_use_spec'=>$this->get_all_spec($all_use_spec, $_POST['spec'])));
    }

    function save_editor(){
        $goods = $this->pre_process($_POST);
        $spec = (array)$goods['spec'];
        unset($_POST);
        $oSpec = $this->app->model('specification');

        if(!$spec) {
            echo json_encode(array('result'=>'failed', 'msg'=>app::get('b2c')->_( '请选择规格' )));
            exit;
        }
        $subsdf = array(
            'spec_value'=>array('*')
        );
        $db = kernel::database();
        $db->beginTransaction();
        $new_spec = array();
        foreach($spec as $k=>$v) {
            $specfication = $oSpec->dump($v['spec_id'],'*',$subsdf);

            $spec_values = array();
            $i = 0;
            foreach($v['option'] as $k2=>$v2) {
                $is_save = false;

                if(!isset($v2['spec_value_id']) || !$v2['spec_value_id']) {
                    $spec_value_key = 'new_'.$i;
                    $specfication['spec_value'][$spec_value_key] = array(
                        'spec_value_id' => $v2['spec_value_id'],
                        'spec_value' => $v2['spec_value'],
                        'spec_image'=>$v2['spec_image'],
                        'private_spec_value_id'=>$v2['private_spec_value_id'],
                    );
                    $is_save = true;
                    $i++;
                }
            }
            if($is_save) {
                if(!$oSpec->save($specfication)) {
                    $db->rollback();
                    echo json_encode(array('result'=>'failed', 'msg'=>app::get('b2c')->_( '规格保存失败' )));
                    exit;
                }
                foreach($specfication['spec_value'] as $ks=>$ps) {
                    if(is_string($ks) || substr($ks,0,4)=="new_"){
                        if(isset($spec[$v['spec_id']]['option'][$ps['private_spec_value_id']]['spec_value_id'])) {
                            $spec[$v['spec_id']]['option'][$ps['private_spec_value_id']]['spec_value_id']=$ps['spec_value_id'];
                            $new_spec[$ps['private_spec_value_id']] = $ps['spec_value_id'];
                        }
                    }
                }
            }
        }

        if( $new_spec ) {
            foreach($goods['product'] as $pk=>$pv) {
                foreach($pv['spec_desc']['spec_value_id'] as $pk2=>$pv2) {
                    if(!$pv2 && $pv2!==0) {
                        $goods['product'][$pk]['spec_desc']['spec_value_id'][$pk2] = $new_spec[$pv['spec_desc']['spec_private_value_id'][$pk2]];

                    }
                }
            }
        }

        if(isset($goods['goods_id']) && $goods['goods_id']) {
            $oGoods = $this->app->model('goods');
            $oProduct = $this->app->model("products");
            $aGoods = $oGoods->getList('name, type_id, unit, marketable,spec_desc',array('goods_id'=>$goods['goods_id']));

            if(!$aGoods[0]['spec_desc']){
                $no_spec = true;
            }
            else{
                $no_spec = false;
            }
            $goods['name'] = $aGoods[0]['name'];
            $goods['type']['type_id'] = $aGoods[0]['type_id'];
            $floatstore = app::get('b2c')->model('goods_type')->getlist('floatstore',array('type_id'=>$goods['type']['type_id']));
            $floatstore = $floatstore[0]['floatstore'];

            //临时解决
            $old_products = (array)$oProduct->getList('product_id',array('goods_id'=>$goods['goods_id']));
            foreach($old_products as $v){
                $old_productids[] = $v['product_id'];
            }
            $_POST['productkey'] = serialize($old_productids);

            if(is_array($goods['product'])){
                foreach($goods['product'] as $pk=>$pv){
                    if($spec){
                        if($pv['spec_desc']['spec_value_id']){
                            foreach($pv['spec_desc']['spec_value_id'] as $spec_value) {
                                if(!isset($spec_value['spec_value_id']) || $spec_value['spec_value_id'] == ''){
                                    $db->rollback();
                                    echo json_encode(array('result'=>'failed', 'msg'=>app::get('b2c')->_( '未选定全部规格' )));
                                    exit;
                                }
                            }
                        }

                    }
                }
            }

            if(!$oGoods->checkPriceWeight($goods['product'])){
                $db->rollback();
                echo json_encode(array('result'=>'failed', 'msg'=>app::get('b2c')->_( '商品价格或重量格式错误' )));
                exit;
            }

            if(is_string($_POST['productkey'])){
                $productkey = unserialize($_POST['productkey']);
                if(is_array($goods['product'])){
                    foreach($goods['product'] as $pk => $pv){
                        $newpk[] = $pv['product_id'];
                    }
                }
                if(is_array($newpk) && is_array($productkey)){
                    $diff = array_diff($productkey,$newpk);
                }
                if(count($diff) > 0){
                    if(!$this->pre_recycle_spec($goods['goods_id'],$diff)){
                        $db->rollback();
                        echo json_encode(array('result'=>'failed', 'msg'=>app::get('b2c')->_( '有的规格订单未处理' )));
                        exit;
                    }
                }
            }

            if($diff){
                if(!$oProduct->delete(array('product_id'=>$diff))) {
                    $db->rollback();
                    echo json_encode(array('result'=>'failed', 'msg'=>app::get('b2c')->_( '删除货品失败' )));
                    exit;
                }
            }

            foreach($goods['product'] as $k => $p){
                if($goods['unit'])
                    $goods['product'][$k]['unit'] = $goods['unit'];
                if( !isset($p['status']) || $p['status'] === "" || $p['status'] === "false"){
                    $goods['product'][$k]['status'] = 'false';
                }else{
                    $goods['product'][$k]['status'] = 'true';
                    $upgoods = true;
                }

                $goods['product'][$k]['goods_id'] = $goods['goods_id'];
                $goods['product'][$k]['name'] = $goods['name'];
                if(!$p['product_id'] || substr( $p['product_id'],0,4 ) == 'new_') {
                    unset($goods['product'][$k]['product_id']);
                }
                //处理会员价开始
                if(isset($p['price']['member_lv_price'])) {
                    $mLevelPrice = array();
                    foreach($p['price']['member_lv_price'] as $level_id=>$mprice){
                        if(isset($mprice['price'])) {
                            $mLevelPrice[] = array(
                                'level_id'=>$level_id,
                                'price'=>$mprice
                            );
                        }
                        else {
                            unset($goods['product'][$k]['price']['member_lv_price'][$level_id]);
                        }
                    }
                    $goods['product'][$k]['price']['member_lv_price'] = $mLevelPrice;
                }
                //处理会员价结束
                if( !isset( $p['store'] ) || $p['store'] === '' ){
                    $goods['product'][$k]['store'] = null;
                    $goods['product'][$k]['freez'] = null;
                }
                if( !isset( $p['weight'] ) || $p['weight'] === '' ){
                    $goods['product'][$k]['weight'] = '0';
                }
                foreach( array('cost','price') as $pCol ){
                    if( !$p['price'][$pCol]['price'] && $p['price'][$pCol]['price'] !== 0 ){
                        $goods['product'][$k]['price'][$pCol]['price'] = '0';
                    }
                }
                //修改后台编辑规格市场价格
                if( $p['price']['mktprice']['price'] == '' && $p['mktprice']==""){
                    $goods['product'][$k]['price']['mktprice']['price'] = $oProduct->getRealMkt($p['price']['price']['price']);
                }else{
                    $goods['product'][$k]['price']['mktprice']['price'] = $p['mktprice'];
                }
                //end

                if (is_null( $v['store'] )){
                    $goods['product'][$k]['freez'] = null;
                }
                if(empty($p['bn'])) continue;
                if($oGoods->checkProductBn($p['bn'], $goods['goods_id']) ){
                    $db->rollback();
                    echo json_encode(array('result'=>'failed', 'msg'=>app::get('b2c')->_( '您所填写的货号已被使用，请检查！' )));
                    exit;
                }
            }
            $bnList = $pweight = array();
            $store = 0;

            foreach( $goods['product'] as $pk => $pv ){
                $pweight[] = $pv['weight'];
                if( $goods['goods_type'] ) //product add goods_type default normal
                    $goods['product'][$pk]['goods_type'] = $goods['goods_type'];


                if( !$pv['bn'] ) $goods['product'][$pk]['bn'] = strtoupper(uniqid('p'));
                if( array_key_exists( $goods['product'][$pk]['bn'],$bnList ) ){
                    $db->rollback();
                    echo json_encode(array('result'=>'failed', 'msg'=>app::get('b2c')->_( '您所填写的货号重复，请检查！' )));
                    exit;
                }
                $bnList[$goods['product'][$pk]['bn']] = 1;
                if( $pv['status'] != 'false' ) $goodsStatus = true;
                if( $pv['store'] === null || $pv['store'] === '' ){
                    $store = null;
                }elseif($store !== null){
                    if(!$floatstore) {
                        $pv['store'] = intval($pv['store']);
                    }
                    $store += $pv['store'];
                }

                //设置商品价格
                $xin_price[] = $goods['product'][$pk]['price']['price']['price'];

                if(!$oProduct->save($goods['product'][$pk])){
                    $db->rollback();
                    echo json_encode(array('result'=>'failed', 'msg'=>app::get('b2c')->_( '货品保存失败' )));
                    exit;
                }

                //ajx   货品编辑时没有进行规格的关联
                if(intval($goods['product'][$pk]['product_id']) <= 0){
                     $goods['product'][$pk]['product_id']=mysql_insert_id();
                }
                #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                if($obj_operatorlogs = kernel::service('operatorlog.b2c_mdl_goods')){
                    if(method_exists($obj_operatorlogs,'logproducts')){
                        $obj_operatorlogs->logproducts($goods['product'][$pk]);
                    }
                }
                #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
				/** 将规格（货品）数据保存到kvstore **/
				//$oProduct->storekv_product_info($goods['goods_id'],$goods['product'][$pk]);
            }


            $goods_spec_desc = array();
            $spec_use_num = array();
            foreach($spec as $k=>$v) {
                $goods_spec_desc[$k] = $v['option'];
                $used_spec[] = array(
                    'spec_name'=>$v['spec_name'],
                    'nums'=>count($v['option'])
                );
            }

            $goods_status = $aGoods[0]['marketable'];
            $aGoods = array(
                'store'=>$store,
                'spec_desc'=>$goods_spec_desc,
            );
            if($goods_status != 'false' && empty($upgoods)){
                $aGoods['marketable'] = 'false';
            }
            $minweight = min($pweight);

            $aGoods['price'] = min($xin_price);
            $aGoods['weight'] = empty($minweight) ? 0 : $minweight ;
            $oGoods->update($aGoods,array('goods_id'=>$goods['goods_id']));

            //ajx goods_spec_index
            if($goods['goods_id']){
                $oGoods->createSpecIndex($goods);
            }

            $db->commit();
            $returnData = array(
                'productNum'=>count($goods['product']),
                'used_spec'=>$used_spec
            );
            if($no_spec === true){
                $returnData['is_new'] = 1;
            }
            echo json_encode(array('result'=>'success', 'data'=>$returnData, 'msg'=>app::get('b2c')->_( '保存成功' )));
            exit;
        }
        else {
            foreach($spec as $k=>$v) {
                $used_spec[] = array(
                    'spec_name'=>$v['spec_name'],
                    'nums'=>count($v['option'])
                );
            }
            $returnData = array(
                'spec'=>$spec,
                'product'=>$goods['product'],
                'productNum'=>count($goods['product']),
                'used_spec'=>$used_spec,
                'is_new'=>'1'
            );

            //判断是否有新增的规格值  by zhoulei 2012-5-24
            $oSpec->createCustomSpec($spec);
            $db->commit();
            echo json_encode(array('result'=>'success', 'data'=>$returnData, 'msg'=>app::get('b2c')->_( '操作成功' )));
            exit;
        }

    }

    private function get_all_spec($all_use_spec, $spec) {
        if( empty( $spec ) ){
            $res = array();
            foreach( $all_use_spec as $pk => $pv ){
                foreach( $pv as $pvk => $pvv ){
                    $res[$pk][$pvv['spec_id']] =  $pvv['private_spec_value_id'];
                }
            }
            return $res;
        }
        $firstSpec = array_shift( $spec );

        $rs = array();
        foreach( $firstSpec['option'] as $sitem ){
            foreach( (array)$all_use_spec as $pitem ){
                $apitem = $pitem ;
                array_push( $apitem , array('spec_id'=>$firstSpec['spec_id']) + $sitem );
                $rs[] = $apitem;
            }
            if( empty($all_use_spec) )
                $rs[] = array( array_merge( array('spec_id'=>$firstSpec['spec_id']) , $sitem) );
        }
        return $this->get_all_spec( $rs, $spec);
    }

    function pre_recycle_spec($goods_id,$args){                                    //删除规格前的一些验证包括订单赠品
        $obj_check_order = kernel::single('b2c_order_checkorder');
        $objProduct = &$this->app->model('products');
        if(is_array($args)){
            foreach($args as $key => $val){
                $orderStatus = $obj_check_order->check_order_product(array('goods_id'=>$goods_id,'product_id'=>$val));
                if(!$orderStatus){
                    return false;
                }
                foreach( kernel::servicelist("b2c_allow_delete_goods") as $object ) {
                    if( !method_exists($object,'is_delete') ) continue;
                    if( !$object->is_delete($goods_id,$val) ) return false;
                }
            }

        }
        return true;
    }

    function pre_process($goods){
        $products = $goods['product'];
        $mlv_ids = array();
        foreach($products as $pid=>$product){
            $products[$pid]['price']=array();
            //处理product_spec，转换成spec sdf结构
            foreach($product['spec_desc'] as $spec){
                $spec_id = $spec['spec_id'];
                unset($products[$pid]['spec_value_'.$spec_id]);
                unset($products[$pid]['spec_value_id_'.$spec_id]);
                unset($products[$pid]['spec_private_value_id_'.$spec_id]);
                $spec_sdf['spec_value'][$spec_id] = $spec['spec_value'];
                $spec_sdf['spec_value_id'][$spec_id] = $spec['spec_value_id'];
                $spec_sdf['spec_private_value_id'][$spec_id] = $spec['spec_private_value_id'];
            }
            $products[$pid]['spec_desc'] = $spec_sdf;
            //处理会员价
            if(!$mlv_ids){
                foreach((array)$product['mLevelPrice'] as $mlv){
                    $mlv_ids[] = $mlv['member_lv_id'];
                }
            }
            unset($products[$pid]['mLevelPrice']);
            foreach($mlv_ids as $mlv_id){
                $mem_lv_price[$mlv_id] = $product['member_lv_price_'.$mlv_id];
                $products[$pid]['price']['member_lv_price'][$mlv_id] = $product['member_lv_price_'.$mlv_id];
                unset($mem_lv_price);
                unset($products[$pid]['member_lv_price_'.$mlv_id]);
            }
            unset($products[$pid]['mlv_price']);
            //处理其他数据
            $products[$pid]['price']['price']['price'] = $product['price'];
            $products[$pid]['price']['cost']['price'] = $product['cost'];
            $products[$pid]['price']['mktprice']['price'] = $product['mktprice'];
            $products[$pid]['status'] = $product['marketable'];
            unset($products[$pid]['marketable']);
        }
        $goods['product'] = $products;
        $goods['goods_id'] = $goods['goods']['goods_id'];
        unset($products);
        unset($goods['goods']);
        return $goods;
    }
    
  
    function set_audit(){
        /*if(!$this->has_permission('auditgoods')){
            header('Content-Type:text/html; charset=utf-8');
            echo app::get('desktop')->_("您无权操作");exit;
        }*/
        $goods_id = $_GET['goods_id'];
        $oGoods = &$this->app->model('goods');
        $obj_apply = app::get('b2c')->model('goods_marketable_application');
        $content['apply'] = $obj_apply->getList('*',array('goods_id'=>intval($goods_id)),0,-1,'apply_id asc');
        $account = array();
        if($content['apply'])
        foreach((array)$content['apply'] as $key => $value){
            switch(intval($value['status'])){
                case 0:
                $content['apply'][$key]['status'] = '待审核';
                break;
                case 1:
                $content['apply'][$key]['status'] = '审核通过';
                break;
                case 2:
                $content['apply'][$key]['status'] = '审核不通过';
                break;
            }
            if($value['apply_user']) $account[] = $value['apply_user'];
            if($value['audit_user']) $account[] = $value['audit_user'];
        }
        $account_data = array();
        if($account){
            $account_data = app::get('pam')->model('account')->getList('account_id,login_name',array('account_id'=>$account),0,-1);
            $account_data=utils::array_change_key($account_data,'account_id');
        }
        $this->pagedata['apply_info'] = $content;
        $this->pagedata['account_info'] = $account_data;
        $this->pagedata['goods_id'] = intval($goods_id);
        $this->singlepage('admin/goods/detail/set_audit.html');
    }
   
}
