<?php
 

class b2c_ctl_admin_goods_editor extends desktop_controller{
    var $simpleGoodsId = 1;
    var $workground = 'b2c_ctl_admin_goods_editor';

    function nospec($cat_id=0){
        $this->_editor($_POST['type_id']);
        $this->display('admin/goods/detail/spec/nospec.html');
    }

   //新增商品页面ctl
    function add(){
        $this->pagedata['title'] = app::get('b2c')->_('添加商品');
        $this->pagedata['cat']['type_id'] = $this->simpleGoodsId;
        $this->pagedata['goods']['type']['type_id'] = $this->simpleGoodsId;
        $this->_editor($this->simpleGoodsId);
        header("Cache-Control:no-store");
        $this->pagedata['IMAGE_MAX_SIZE'] = IMAGE_MAX_SIZE;
        $this->singlepage('admin/goods/detail/frame.html');
    }

    function _editor($type_id){
        $cat = &$this->app->model('goods_cat');
        $this->pagedata['cats'] = $cat->getMapTree(0,'');
        $this->pagedata['goodsbn_display_switch'] = ($this->app->getConf('goodsbn.display.switch') == 'true');
        $objGtype = &$this->app->model('goods_type');
        $this->pagedata['gtype'] = $objGtype->getList('*','',0,-1);
        if( !$this->pagedata['gtype'] ){
            header('Content-Type:text/html; charset=utf-8');
            echo app::get('b2c')->_('请先添加商品类型');
            exit;
        }

//        $gimage = &$this->app->model('gimages');
//        $this->pagedata['uploader'] = $gimage->uploader();

/*{{{*/
        $prototype = $objGtype->dump($type_id,'*',array('brand'=>array('*',array(':brand'=>array('brand_id,brand_name')))));
        if( $type_id == 1 ){
            $oBrand = &$this->app->model('brand');
            $this->pagedata['brandList'] = $oBrand->getList('brand_id,brand_name','',0,-1);
        }else if($prototype['setting']['use_brand']){
            if(!empty($prototype['brand'])){
                foreach( $prototype['brand'] as $typeBrand ){
                    $this->pagedata['brandList'][] = $typeBrand['brand'];
                }
            }
        }

        $this->pagedata['sections'] = array();
        $sections = array(
            'basic'=>array(
                'label'=>app::get('b2c')->_('基本信息'),
                'options'=>'',
                'file'=>'admin/goods/detail/basic.html',
            ),
            'adj'=>array(
                'label'=>app::get('b2c')->_('配件'),
                'options'=>'',
                'file'=>'admin/goods/detail/adj.html',
            ),
            'content'=>array(
                'label'=>app::get('b2c')->_('详细介绍'),
                'options'=>'',
                'file'=>'admin/goods/detail/content.html',
            ),
            'params'=>array(
                'label'=>app::get('b2c')->_('属性参数'),
                'options'=>'',
                'file'=>'admin/goods/detail/params.html',
            ),
            'rel'=>array(
                    'label'=>app::get('b2c')->_('相关商品'),
                    'options'=>'',
                    'file'=>'admin/goods/detail/rel.html',
            ),
            'seo'=>array(
                'label' => app::get('b2c')->_('HTML页面参数设置'),
                'options'=>'',
                'file'=>'admin/goods/detail/seo.html'
            ),
        );
        // add for limitedarea app
        foreach( kernel::servicelist( 'b2c_admin_goods_edit_menus' ) as $object ) {
            if( is_object( $object ) ){
                if( method_exists( $object, 'add_edit_menu' ) ) {
                    $menu_data = $object->add_edit_menu( $this,$type_id ,$sections);
                    $sections = array_merge( $sections, $menu_data );
                }
                if( method_exists( $object, 'set_page_data' ) ) {
                    $object->set_page_data( $this,$type_id );
                }
            }
        }
        $extends_goods_edit_html = '';

        foreach(kernel::servicelist('b2c.goods_eidt_extends_html') as $extends_html_obj)
        {
               if(is_object($extends_html_obj)  && method_exists( $extends_html_obj, 'get_extends_html' ) )
                $extends_goods_edit_html.=$extends_html_obj->get_extends_html($this->pagedata);
        }
        if(!empty($extends_goods_edit_html)) $this->pagedata['extends_goods_edit_html'] = $extends_goods_edit_html;
        // end of add
        if(!$prototype['setting']['use_params'] || !$prototype['params'])
             unset($sections['params']);
        foreach($sections as $key=>$section){
            if (!isset($prototype['setting']['use_'.$key]) || $prototype['setting']['use_'.$key] ){
                if(method_exists($this,($func = '_editor_'.$key))){
                    $this->$func();
                }
            }
            $this->pagedata['sections'][$key] = $section;
        }
        $this->pagedata['goods']['type']['type_id'] = $type_id;
        if($this->pagedata['goods']['spec']){ // || $prototype['spec']
            $prototype['setting']['use_spec'] = 1;
            if(!$this->pagedata['goods']['products']){
                $this->pagedata['goods']['products'] = array(1);
            }
        }
        $this->pagedata['goods']['type'] = $prototype;
/*}}}*/
        $this->pagedata['point_setting'] = $this->app->getConf('point.get_policy');
        $this->pagedata['url'] = str_replace( "\\",'/', dirname( $_SERVER['PHP_SELF'] ));
        $memberLevel = &$this->app->model('member_lv');
        $this->pagedata['mLevels'] = $memberLevel->getList('member_lv_id,dis_count');
        $oTag = &app::get('desktop')->model('tag');
        $this->pagedata['tagList'] = $oTag->getList('*',array('tag_mode'=>'normal','tag_type'=>'goods'),0,-1);
        $oTrel = &app::get('desktop')->model('tag_rel');
        $this->pagedata['tag'] =  $oTrel->getList('tag_id',array('rel_id'=>$this->goods_id));
        $this->pagedata['image_dir'] = &app::get('image')->res_url;
        $this->pagedata['storeplace'] = $this->app->getConf('storeplace.display.switch');
        $this->pagedata['site_min_order'] = $this->app->getConf('site.min_order');
        $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');

        //过滤下架商品,过滤商品本身（相关商品）@lujy
        $this->pagedata['goodslink_filter'] = array('goods_id|noequal'=>$this->goods_id,'marketable'=>'true');
    }

    function _prepareGoodsData( &$data ){

        $objGoods = $this->app->model('goods');
        $objPro = $this->app->model('products');
        $objGtype = $this->app->model('goods_type');
        $lastGoodsId = $objGoods->getlist('goods_id',array(),0,1,'goods_id desc');

        if( !$data['cat_id'] ) $data['cat_id'] = 0;
        $lastGoodsId = $lastGoodsId[0]['goods_id'];

        $goods = $data['goods'];
        if(is_numeric($goods['type']['type_id'])){
            $floatstore = $objGtype->getlist('floatstore',array('type_id'=>$goods['type']['type_id']));
            if(!$floatstore[0]['floatstore']){
                foreach((array)$goods['product'] as $key=>$val){
                    if( $val['store'] )
                        $goods['product'][$key]['store']= intval($val['store']);
                }
            }
        }


        $goods['adjunct'] = $data['adjunct'];
        if(is_array($goods['images']))
            $goods['image_default_id'] = $data['image_default'];
        else
            $goods['image_default_id'] = null;
        if( $data['keywords'] ){
            foreach( explode( '|', $data['keywords']) as $keyword ){
                $goods['keywords'][] = array(
                    'keyword' => $keyword,
                    'res_type' => 'goods'
                );
            }
        }
        if( $goods['spec'] ){
            $goods['spec'] = unserialize($goods['spec'] );
        }else{
            $goods['spec'] = null;
        }
        if( $goods['params'] ){
            $goodsParams = array();
            foreach( $goods['params'] as $gpk => $gpv ){
                $goodsParams[$data['goodsParams']['group'][$gpk]][$data['goodsParams']['item'][$gpk]] = $gpv;
            }
            $goods['params'] = $goodsParams;
        }
        //处理配件

        if( !$goods['min_buy'] )unset( $goods['min_buy'] );
        if( !$goods['brand']['brand_id'] ) $goods['brand']['brand_id'] = null;
        $images = array();
        foreach( (array)$goods['images'] as $imageId ){
            $images[] = array(
                'target_type'=>'goods',
                'image_id'=>$imageId,
                );
        }
        $goods['images'] = $images;
        unset($images);
        if(isset($goods['adjunct']['name'])){
           foreach($goods['adjunct']['name'] as $key => $name){
                $aItem = array();
                $aItem['name'] = $name;
                $aItem['type'] = $goods['adjunct']['type'][$key];
                $aItem['min_num'] = $goods['adjunct']['min_num'][$key];
                $aItem['max_num'] = $goods['adjunct']['max_num'][$key];
                $aItem['set_price'] = $goods['adjunct']['set_price'][$key];
                $aItem['price'] = $goods['adjunct']['price'][$key];
                if($aItem['type'] == 'goods') $aItem['items']['product_id'] = $goods['adjunct']['items'][$key];
                else $aItem['items'] = $goods['adjunct']['items'][$key];//.'&dis_goods[]='.$aData['goods_id']
                if($aItem['set_price']  == 'discount' && $aItem['price']>1){
                    $this->end(false,app::get('b2c')->_( '配件折扣不能大于1' ));
                }
                $aAdj[] = $aItem;
            }
        }
        $goods['adjunct'] = $aAdj;
        $goods['product'][key((array)$goods['product'])]['default'] = '1';

        foreach( $goods['product'] as $prok => $pro ){
            if($goods['unit'])
                $goods['product'][$prok]['unit'] = $goods['unit'];
            if( !$pro['product_id'] || substr( $pro['product_id'],0,4 ) == 'new_' )
                unset( $goods['product'][$prok]['product_id'] );
            if( $pro['status'] != 'true' ){
                $goods['product'][$prok]['status'] = 'false';
            }else{
                $upgoods = true;
            }
            $mprice = array();
            if( $pro['weight'] === '' )
                $goods['product'][$prok]['weight'] = '0';
            if( $pro['store'] === '' )
                $goods['product'][$prok]['store'] = null;
            foreach( (array)$pro['price']['member_lv_price'] as $mLvId => $mLvPrice )
                if( $mLvPrice )
                    $mprice[] = array( 'level_id'=>$mLvId,'price'=>$mLvPrice );
            $goods['product'][$prok]['price']['member_lv_price'] = $mprice;
            foreach( array('cost','price') as $pCol ){
                if( !$pro['price'][$pCol]['price'] && $pro['price'][$pCol]['price'] !== 0 ){
                    $goods['product'][$prok]['price'][$pCol]['price'] = '0';
                }
            }
            if( $pro['price']['mktprice']['price'] == '' )
                $goods['product'][$prok]['price']['mktprice']['price'] = $objPro->getRealMkt($pro['price']['price']['price']);
            $goods['product'][$prok]['price']['mktprice']['price'] = trim($goods['product'][$prok]['price']['mktprice']['price']);
            $goods['product'][$prok]['price']['cost']['price'] = trim($goods['product'][$prok]['price']['cost']['price']);
            $goods['product'][$prok]['price']['price']['price'] = trim($goods['product'][$prok]['price']['price']['price']);


        }

        if(is_array($data['linkid'])){
            foreach($data['linkid'] as $k => $id){
                if(!empty($goods['goods_id']))
                    $lastId = $goods['goods_id'];
                else
                    $lastId = intval($lastGoodsId)+1;
                $aLink[] = array('goods_1' => $lastId, 'goods_2' => $id, 'manual' => $data['linktype'][$id], 'rate' => 100);
            }
            $goods['rate'] = $aLink;
        }
        $goods['rate'] = $aLink;
        if( !$goods['category']['cat_id']) $goods['category']['cat_id'] = 0;
        if( !$goods['tag'] ) $goods['tag'] = array();
        if( !$goods['adjunct'] ) $goods['adjunct'] = array();
        if( !$goods['rate'] ) $goods['rate'] = array();
        if( $goods['gain_score'] === '' ) $goods['gain_score'] = null;
        if( empty($goods['package_scale']) ) $goods['package_scale'] = '1';
        if( empty($goods['package_unit']) ) $goods['package_unit'] = '';
        if( $goods['props'] ){
            foreach( $goods['props'] as $pk => $pv ){
                if( substr($pk,2) <= 20 && $pv['value'] === '' )
                    $goods['props'][$pk]['value'] = null;
            }
        }
        $tmpProduct = $goods['product'];
        foreach( $tmpProduct as $k=>$v ) {
            if(!$k && $k!==0){
                unset($tmpProduct[$k]);
            }
        }

        if(empty($upgoods) && $tmpProduct){
            $goods['status'] = 'false';
        }
        
       
        $goods['marketable_allow'] = $goods['status'];
        $goods['goods_order_down'] = intval($goods['goods_order_down']);
        $goods['goods_order_down'] = ($goods['goods_order_down'] < 0 || $goods['goods_order_down'] > 100)?100:$goods['goods_order_down'];
       
        return $goods;
    }

    function toAdd(){
        $this->begin('');
        $oGoods = &$this->app->model('goods');
        if (isset($_POST['goods']['brief'])&&$_POST['goods']['brief']&&strlen($_POST['goods']['brief'])>210){
            $this->end(false,app::get('b2c')->_( '简短的商品介绍,请不要超过70个字！' ));
        }
        if(isset($_POST['spec_load'])){
            $this->end(false,app::get('b2c')->_( '规格未加载完毕' ));
        }

        if(isset($_POST['new_goods_spec']) && $_POST['new_goods_spec']) {
            unset($_POST['goods']['product']);
            $goodsinfo = json_decode($_POST['new_goods_spec'],1);
            $_POST['goods']['product'] = $goodsinfo['product'];
            $_POST['goods']['spec'] = serialize($goodsinfo['spec']);
        }

        if(is_array($_POST['goods']['product'])){
            foreach($_POST['goods']['product'] as $pk=>$pv){
                if(is_array($_POST['goods']['product']) && is_string($_POST['goods']['spec'])){
                    if(count($pv['spec_desc']['spec_value_id']) < count(unserialize($_POST['goods']['spec']))){
                         $this->end(false,app::get('b2c')->_( '未选定全部规格' ));
                    }
                }
            }
        }
        if($_POST['adjunct']['min_num'][0] > $_POST['adjunct']['max_num'][0]){
            $this->end(false,app::get('b2c')->_( '配件最小购买量大于最大购买量' ));
        }
        if(!$oGoods->checkPriceWeight($_POST['goods']['product'])){
            $this->end(false,app::get('b2c')->_( '商品价格或重量格式错误' ));
        }
        if(!$oGoods->checkStore($_POST['goods']['product'])){
            $this->end(false,app::get('b2c')->_( '库存格式错误' ));
        }
        $goods = $this->_prepareGoodsData($_POST);
        if( $goods['udfimg'] == 'true' && !$goods['thumbnail_pic'] ){
            $goods['udfimg'] = 'false';
        }

        if(is_string($_POST['productkey'])){
            $productkey = unserialize($_POST['productkey']);
            if(is_array($_POST['goods']['product'])){
                foreach($_POST['goods']['product'] as $pk => $pv){
                    $newpk[] = $pv['product_id'];
                }
            }
            if(is_array($newpk) && is_array($productkey)){
                $diff = array_diff($productkey,$newpk);
            }
            if(count($diff) > 0){
                if(!$this->pre_recycle_spec($_POST['goods']['goods_id'],$diff)){
                    $this->end(false,app::get('b2c')->_( '有的规格订单未处理' ));
                }
            }
        }



        if( count( $goods['product'] ) == 0 ){
            //$this->end(false,'货品未添加');
            exit;
        }
        if( strlen($goods['brief']) > 255 ){
            $this->end(false,app::get('b2c')->_( '商品介绍请不要超过70个汉字' ));
        }

        if( !$goods['name'] )
            $this->end(false,app::get('b2c')->_('商品名称不能为空'));
        if( $goods['bn']  ){
            if( $oGoods->checkProductBn($goods['bn'], $goods['goods_id']) ){
                $this->end(false,app::get('b2c')->_('您所填写的商品编号已被使用，请检查！'));
            }
        }

        foreach($goods['product'] as $k => $p){
            if(!$k && $k !== 0) {
                unset($goods['product'][$k]);
                continue;
            }
            if (is_null( $p['store'] )){$goods['product'][$k]['freez'] = null;$goods['product'][$k]['store'] = null;}
            if(empty($p['bn'])) continue;
            if($oGoods->checkProductBn($p['bn'], $goods['goods_id']) ){
                $this->end(false,app::get('b2c')->_('您所填写的货号已被使用，请检查！'));
            }
        }
        if(!$goods['product']) {
            unset($goods['product']);
            unset($goods['spec']);
        }

        $oUrl = kernel::single('site_route_app');

        $arr_remove_image = array();
        if( $_POST['goods']['images'] ){
            $oImage_attach = app::get('image')->model('image_attach');
            $arr_image_attach = $oImage_attach->getList('*',array('target_id'=>$goods['goods_id'],'target_type'=>'goods'));
            foreach ((array)$arr_image_attach as $_arr_image_attach){
                if (!in_array($_arr_image_attach['image_id'],$_POST['goods']['images'])){
                    $arr_remove_image[] = $_arr_image_attach['image_id'];
                }
            }
        }

        if ( !$oGoods->save($goods) ){
            $this->end(false,app::get('b2c')->_('您所填写的货号重复，请检查！'));
        }else{
            if( $goods['images'] ){
                $oImage = &app::get('image')->model('image');
                if ($arr_remove_image){
                    foreach($arr_remove_image as $_arr_remove_image)
                        $test = $oImage->delete_image($_arr_remove_image,'goods');
                }
                foreach($goods['images'] as $k=>$v){
                    $test = $oImage->rebuild($v['image_id'],array('S','M','L'),true);
                }
            }

            if( $_POST['goods_static'] ){
                $url = $oUrl->fetch_static( array( 'static'=>$_POST['goods_static'] ) );
                $goods_url = app::get('site')->router()->gen_url( array( 'app'=>'b2c','real'=>1,'ctl'=>'site_product','args'=>array($goods['goods_id']) ) );
                $goods_url = substr( $goods_url , strlen( app::get('site')->base_url() ) );
                $goods_url_info = $oUrl->fetch_static( array( 'static'=>$goods_url ) );
                if(empty($goods_url_info['url'])){
                    $goods_url_info['url'] = $goods_url;
                }
                $goods_url_info['static'] = $_POST['goods_static'];
                $goods_url_info['enable'] = 'true';
                if( $url['url'] && $url['url'] != $goods_url_info['url'] ){
                    $this->end(false,app::get('b2c')->_('您填写的自定义链接已存在'));
                }
                $oUrl->store_static( $goods_url_info );
           }else{
                $goods_url = app::get('site')->router()->gen_url( array( 'app'=>'b2c','real'=>1,'ctl'=>'site_product','args'=>array($goods['goods_id']) ) );
                $goods_url = substr( $goods_url , strlen( app::get('site')->base_url() ) );
                $oUrl->delete_static( array( 'static'=>$goods_url ) );
           }

        }

        $_POST['goods'] = $goods;
        $goodsServiceList = kernel::servicelist("goods.action.save");
        foreach( $goodsServiceList as $aGoodsService ){
            if(!$aGoodsService->save( $_POST, $error_msg )){
                $this->end(false, $error_msg);
            }
        }

        if(app::get('base')->getConf('server.search_server.search_goods') == 'search_goods'){
            $obj = search_core::segment();
            if(search_core::instance('search_goods')->status($msg)){
                $luceneIndex = search_core::instance('search_goods')->link();
            }else{
                $luceneIndex = search_core::instance('search_goods')->create();
            }
            $luceneIndex = search_core::instance('search_goods')->update($goods);
        }
        $this->end(true,app::get('b2c')->_('操作成功'),null,array('goods_id'=>$goods['goods_id'] ) );


    }

    function edit($goods_id){
        $this->goods_id = $goods_id;
        $oGoods = &$this->app->model('goods');
        $goods = $oGoods->dump($goods_id,'*','default');
        ksort($goods['images']);
        $this->_editor($goods['type']['type_id']);
        if(is_numeric($goods['store'])) $goods['store'] = (float)$goods['store'];
        if(is_array($goods['product'])){
            foreach($goods['product'] as $k=>$v){
                $goods['product'][$k]['store'] = $v['store']!==null ? (float)$v['store'] : '';

            }
        }
        $this->pagedata['productkey'] = serialize(array_keys($goods['product']));
        $this->pagedata['brandList'] = empty($goods['brand']['brand_id'])?array():$this->app->model('brand')->getList('brand_id,brand_name',array('brand_id'=>$goods['brand']['brand_id']),0,-1);
        
       
        foreach((array)$goods['dlytypes'] as $items){
            $goods['gdlytype'][] = $items['dly_id']; 
        }
        unset($goods['dlytypes']);
       
        
        $this->pagedata['goods'] = $goods;
        $this->pagedata['app_dir'] = app::get('b2c')->app_dir;
        if(!is_array($goods['adjunct']))
            $this->pagedata['goods']['adjunct'] = unserialize($goods['adjunct']);
        else
            $this->pagedata['goods']['adjunct'] = $goods['adjunct'];
        foreach($oGoods->getLinkList($goods_id) as $rows){
            if($rows['goods_1'] == $goods_id){
                $aLinkList[] = $rows['goods_2'];
                $linkType[$rows['goods_2']] = array('manual'=>$rows['manual']);
            }else{
                $aLinkList[] = $rows['goods_1'];
                $linkType[$rows['goods_1']] = array('manual'=>$rows['manual']);
            }
        }

        $oUrl = kernel::single('site_route_app');
        $goods_url = app::get('site')->router()->gen_url( array( 'app'=>'b2c','real'=>1,'ctl'=>'site_product','args'=>array($goods_id) ) );
        $goods_url = substr( $goods_url , strlen( app::get('site')->base_url() ) );
        $url = $oUrl->fetch_static( array( 'static'=>$goods_url ) );
        $this->pagedata['goods_static'] = $url['static'];

        if($this->pagedata['goods']['spec']) {
            foreach($this->pagedata['goods']['spec'] as $k=>$v) {
                $goods_spec_desc[$k] = $v['option'];
                $used_spec[] = array(
                    'spec_name'=>$v['spec_name'],
                    'nums'=>count($v['option'])
                );
            }
            $this->pagedata['goods']['used_spec'] = $used_spec;
        }

        $this->pagedata['goods']['product_num'] = count($this->pagedata['goods']['product']);
        $this->pagedata['goods']['glink']['items'] = $aLinkList;
        $this->pagedata['goods']['glink']['moreinfo'] = $linkType;
        $this->pagedata['goods']['goods_setting'] = $goods['goods_setting'];
        $this->pagedata['IMAGE_MAX_SIZE'] = IMAGE_MAX_SIZE;
        $this->pagedata['related_return_url'] = 'index.php?app=b2c&ctl=admin_goods_editor&act=get_related_product&p[0]='.$goods_id;
        //判断 分类有没有规格 gexinfeng 2012-4-10
        if($goods['type']['type_id']!='1'){
            $gTypeSpecObj = $this->app->model('goods_type_spec');
            $goods_type_spec = $gTypeSpecObj->dump(array('type_id'=>$goods['type']['type_id']),'*');
            if($goods_type_spec){
                $this->pagedata['spec'] = true;
            }
        }
        //end
        $this->singlepage('admin/goods/detail/frame.html');
    }

    function get_related_product(){
        $filter = array();
        $current_goods_id = $_GET['p'][0];
        if (!$current_goods_id || !$_POST['data'] || !$_POST['filter']) return '';
        $obj_goods = $this->app->model('goods');

        $filter = $_POST['filter'];
        if (isset($_POST['data'][0])&&$_POST['data'][0]=='_ALL_'){
            if (isset($filter['advance'])&&$filter['advance']){
                $arr_filters = explode(',',$filter['advance']);
                foreach ($arr_filters as $obj_filter){
                    $arr = explode('=',$obj_filter);
                    $filter[$arr[0]] = $arr[1];
                }
                unset($filter['advance']);
            }
        }else{
            $filter = array_merge($filter,array($obj_goods->idColumn=>$_POST['data']));
        }
        $arr_goods = $obj_goods->getList('goods_id,name',$filter);
        if (!$arr_goods) return '';
        /** 查找相关商品 **/
        $render = kernel::single('base_render');
        $render->pagedata['goods_items'] = $arr_goods;
        $obj_goods_rate = $this->app->model('goods_rate');
        $arr_goods_rates = $obj_goods_rate->db->select("SELECT `goods_2`,`manual` FROM ".$obj_goods_rate->table_name(1)." WHERE `goods_1` IN (".$current_goods_id.")");
        if ($arr_goods_rates){
            $arr_goods_rates_ids = (array)array_map('current',(array)$arr_goods_rates);
            foreach ((array)$arr_goods as $k=>$_new_goods_id){
                if (($key=array_search($_new_goods_id['goods_id'], $arr_goods_rates_ids)) !== false){
                    $render->pagedata['goods_items'][$k]['manual'] = $arr_goods_rates[$key]['manual'];
                }
            }
        }

        $render->pagedata['desktop_res_url'] = app::get('desktop')->res_url;
        header('Content-Type:text/html; charset=utf-8');
        echo $render->fetch('admin/goods/detail/ajax_rel_items.html','b2c');exit;
    }

    function set_spec(){
        $typeId = $_GET['p'][0];
        $_POST['spec'] = unserialize($_POST['spec']);
        if( $_POST['spec'] ){
            $this->_set_spec($_POST['spec']);
        }else{
            $this->_set_type_spec($typeId);
        }
        $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');
        $this->display('admin/goods/detail/spec/set_spec.html');
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
            $aSpec[$v1['spec_id']]['tp'] = 'tp'.$v1['spec_id']."oo";
            $aSpec[$v1['spec_id']]['index'] = $v1['spec_id'];
            $aSpec[$v1['spec_id']]['value'] = $spec_values;
            $spec['spec'][$k1] = $v1;
        }
        sort($aSpec);
        //error_log(var_export($aSpec,1),3,"g:/aaa.log");
        $this->pagedata['spec'] = json_encode($aSpec);
    }

    function _set_spec($spec){
        $oSpec = &$this->app->model('specification');
        $subSdf = array(
            'spec_value' =>array('*')
        );
        $specifications = $oSpec->batch_dump( array('spec_id'=>array_keys($spec)), '*' , $subSdf, 0 ,-1 );
        //print_r($spec);
        //error_log(print_r($specifications,1),3,"g:/aaa.log");exit;
        $aSpec = array();
        foreach($specifications as $k1=>$v1){
            $spec_values = array();
            foreach($v1['spec_value'] as $k2=>$v2){
                if($v1['spec_type'] == "image"){
                    $v2['color'] = $v2['spec_value'];
                    $v2['view'] = base_storager::image_path($v2['spec_image'],'s');
                    $spec_values[] = $v2;
                }
                else{
                    $spec_values[] = $v2;
                }
    }


            $aSpec[$v1['spec_id']]['spec_id'] = $v1['spec_id'];
            $aSpec[$v1['spec_id']]['text'] = $v1['spec_name'];
            $aSpec[$v1['spec_id']]['spec_type'] = $v1['spec_type'];
            $aSpec[$v1['spec_id']]['tp'] = 'tp'.$v1['spec_id'];
            $aSpec[$v1['spec_id']]['index'] = $v1['spec_id'];
            $aSpec[$v1['spec_id']]['value'] = $spec_values;
            $spec[$k1] = $v1;
        }

        sort($aSpec);
        $this->pagedata['spec'] = json_encode($aSpec);
        $this->pagedata['goods_spec'] = $spec;
    }

    function set_spec_desc(){
        $spec = $_POST['spec'];
        $spec[$_POST['addSpecId']] = null;
        $this->_set_spec( $spec );
//        $oSpec = &$this->app->model('specification');
//        $this->pagedata['specs'] = $oSpec->getList('spec_id,spec_name,spec_memo',null,0,-1);

        $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');
        $this->display('admin/goods/detail/spec/set_spec_desc.html');
    }

    function addSpecValue(){
        $_POST = utils::stripslashes_array($_POST);
        $this->pagedata['aSpec'] = array(
            'spec_type' => $_POST['spec']['specType'],
            'spec_id' => $_POST['spec']['specId']
        );
        $this->pagedata['specValue'] = array(
            'spec_value_id' => $_POST['spec']['specValueId'],
            'spec_value' => $_POST['spec']['specValue'],
            'private_spec_value_id'=>time().$_POST['sIteration'],
            'spec_image'=>$_POST['spec']['specImage'],
//            'spec_image_id' => $_POST['spec']['specImageId'],
            'spec_goods_images'=>$_POST['spec']['specGoodsImages']
        );

        $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');
        $this->display('admin/goods/detail/spec/spec_value.html');
    }

    function doAddSpec(){
        $oImage = app::get('image')->model('image');//fetch($_POST['']);

        $this->pagedata['goods']['spec'] = &$_POST['spec'];
        if( $_GET['create'] == 'true' ){
            $pro = $this->_doCreatePro( $pro, $_POST['spec'] );
            $this->pagedata['fromType'] = 'create';
            $this->pagedata['goods']['product'] = $pro;
        }
        $this->_set_spec( $_POST['spec'] );
        $this->pagedata['spec_tmpl'] = $this->pagedata['spec'];
        $this->pagedata['needUpValue'] = json_encode($_POST['needUpValue']);
//        $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');
        $memberLevel = &$this->app->model('member_lv');
        $this->pagedata['mLevels'] = $memberLevel->getList('member_lv_id,dis_count');
        $this->pagedata['app_dir'] = app::get('b2c')->app_dir;

        $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');

        $this->display('admin/goods/detail/spec/spec.html');
    }

    function _doCreatePro( $pro, $spec ){
        if( empty( $spec ) ){
            $res = array();
            foreach( $pro as $pk => $pv ){
                foreach( $pv as $pvk => $pvv ){
                    $res['new_'.$pk]['spec_desc']['spec_value'][$pvv['spec_id']] = $pvv['spec_value'];
                    $res['new_'.$pk]['spec_desc']['spec_private_value_id'][$pvv['spec_id']] = $pvv['private_spec_value_id'];
                    $res['new_'.$pk]['spec_desc']['spec_value_id'][$pvv['spec_id']] = $pvv['spec_value_id'];
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
       return $this->_doCreatePro( $rs, $spec );
    }


    function update(){
        $goods = $this->_prepareGoodsData($_POST);
        $oType = &$this->app->model('goods_type');
        $goods['type'] = $oType->dump($goods['type']['type_id'],'*');
        unset($goods['spec'],$goods['product']);
        //判断 分类有没有规格 gexinfeng 2012-4-10
        if($goods['type']['type_id']!='1'){
            $gTypeSpecObj = $this->app->model('goods_type_spec');
            $goods_type_spec = $gTypeSpecObj->dump(array('type_id'=>$goods['type']['type_id']),'*');
            if($goods_type_spec){
                $this->pagedata['spec'] = true;
            }
        }
        //end
        $this->_editor($goods['type']['type_id']);
        $this->pagedata['goods'] = $goods;
        $this->pagedata['show'] = $_GET['show'];
        header("Cache-Control:no-store");
        header('Content-Type:text/html; charset=utf-8');
        $side_bar = $this->fetch('admin/goods/detail/sidebar.html');
        $goods_body = $this->fetch('admin/goods/detail/page.html');
        echo '<!-----#menu-desktop-----'.$side_bar.'-----#menu-desktop-----><!-----#gEditor-Body-----'.$goods_body.'-----#gEditor-Body----->';
    }

    function addGrp(){
        $this->pagedata['goods_id'] = $_GET['goods_id'];
        $this->pagedata['aOptions'] = array('goods'=>app::get('b2c')->_('选择几件商品作为配件'), 'filter'=>app::get('b2c')->_('选择一组商品搜索结果作为配件'));
        $this->display('admin/goods/detail/adj/info.html');
    }

    function doAddGrp($goodsid){
        $this->pagedata['adjunct'] =array('name'=>$_POST['name'],'type'=>$_POST['type']);
        $this->pagedata['key'] = time();
        //过滤下架商品,过滤商品本身（配件）@lujy
        $this->pagedata['adjgoods_filter'] = array('goods_id|noequal'=>$goodsid,'marketable'=>'true');
        $this->display('admin/goods/detail/adj/row.html');
    }

    function specValue(){
        $specId = $_GET['spec_id'];
        $objSpec = &$this->app->model('specification');

        $this->pagedata['aSpec'] = $objSpec->dump($specId,'*','default');
        $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');
        $this->display('admin/goods/detail/spec/spec_value_tmpl.html');
    }

    function showfilter($type_id){
        $obj = &$this->app->model('goods');
        $this->pagedata['filter'] = $obj->getFilterByTypeId(array('type_id'=>$type_id));
        $this->pagedata['filter_interzone'] = $_POST;
        $this->pagedata['view'] = $_POST['view'];
        $this->display('admin/goods/filter_addon.html');
    }

    function selAlbumsImg(){
        $this->pagedata['selImgs'] = explode(',',$_POST['selImgs']);
        $this->pagedata['img'] = $_POST['img'];
        $this->display('admin/goods/detail/spec/spec_selalbumsimg.html');
    }

    function set_mprice(){
        //@lujy--会员价权限
        if(!$this->has_permission('editmemberlevelprice')){
            header('Content-Type:text/html; charset=utf-8');
            echo app::get('b2c')->_("您无权操作会员价");exit;
        }
        $memberLevel = &$this->app->model('member_lv');
        foreach($memberLevel->getList('member_lv_id,name,dis_count,name') as $level){
            $level['dis_count'] = ($level['dis_count']>0 ? $level['dis_count'] : 1);
            $level['price'] = $_POST['level'][$level['member_lv_id']];
            $this->pagedata['mPrice'][$level['member_lv_id']] = $level;
        }
        $this->display('admin/goods/detail/level_price.html');
    }

    function getSpecHtml($goods_id){
        $oGoods = &$this->app->model('goods');
        $goods = $oGoods->dump($goods_id,'*',
        array(
                    'product'=>array(
                        '*',array(
                            'price/member_lv_price'=>array('*')
                        )
                    )
            )
        );
        $this->_editor($goods['type']['type_id']);
        $this->pagedata['goods'] = $goods;
        $html = $this->display("admin/goods/detail/spec/spec.html");

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
}
