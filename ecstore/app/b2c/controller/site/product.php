<?php
 

class b2c_ctl_site_product extends b2c_frontpage{

    var $_call = 'call';
    var $type = 'goods';
    var $seoTag = array('shopname','brand','goods_name','goods_cat','goods_intro','goods_brief','brand_kw','goods_kw','goods_price','update_time','goods_bn');
    function __construct($app){
        parent::__construct($app);
        $shopname = $app->getConf('system.shopname');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('商品页').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('商品页').'_'.$shopname;
            $this->description = app::get('b2c')->_('商品页').'_'.$shopname;
        }
        $this->custom_view = "";
        if(isset($_POST['view'])  && $_POST['view']){
            $default_theme = kernel::single('site_theme_base')->get_default();
        	$o_themes = app::get('site')->model('themes')->getList('*', array('theme'=>$default_theme));
            //$theme_dir =  THEME_DIR."/".$o_themes[0]['theme'];
            $theme_dir =  $o_themes[0]['theme'];

            $this->custom_view = $theme_dir."/".$_POST['view'];
        }
        $this->pagedata['request_url'] = $this->gen_url( array('app'=>'b2c','ctl'=>'site_product','act'=>'get_goods_spec') );
    }

    /**
     *获取商品规格
     *@params POST goods_id
     */
    public function get_goods_spec() {
        $gid = $this->_request->get_get('gid');
        if( !$gid ) {
            echo '';
            exit;
        }

        //按钮类型
        $button_type = $this->_request->get_get('type');
        $this->pagedata['type'] = $button_type;

        $form_url = $this->_request->get_get('form_url');
        if($form_url){
            $this->pagedata['form_url'] = urldecode($form_url);
        }

        //购物车弹出方式
        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;

        $this->pagedata['goodshtml']['name'] = kernel::single("b2c_goods_detail_name")->show( $gid,$arrGoods );
        if( $arrGoods['spec'] && is_array($arrGoods['spec']) )  {
            foreach( $arrGoods['spec'] as $row ) {
                $option = $row['option'];
                if( $option && is_array($option) ) {
                    foreach( $option as $img ) {
                        foreach( (array)explode(',',$img['spec_goods_images']) as $imageid )
                            $return[$imageid] = base_storager::image_path($imageid,'s');
                    }
                }
            }
        }
        $this->pagedata['spec2image'] = json_encode( $return );

        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');
        $arrGoods['spec2image'] = json_encode($return);
        $this->pagedata['goods'] = $arrGoods;

        list($usec,$sec) = explode(" ",microtime());
        $microtime = substr($usec,strpos($usec,'.')+1).$sec;
        $this->pagedata['goodsspec_classname'] = "goods-spec-".$gid."-".$microtime;

        $this->pagedata['goodshtml']['spec'] = kernel::single("b2c_goods_detail_spec")->show( $gid,$arrGoods );
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_default_id'] = $imageDefault['S']['default_image'];
        $this->pagedata['goodshtml']['button'] = kernel::single('b2c_goods_detail_button')->show( $gid,$arrGoods );
        $this->page( 'site/gallery/spec_dialog.html',true );

    }

    function call(){
        $args = func_get_args();
        $action = array_shift($args);
        if(method_exists($this,$action)){
            call_user_func_array(array(&$this,$action),$args);
        }else{
            $objSchema = &$this->app->model('goods/schema');
            $gid = array_shift($args);
            if(!is_int($gid)) {
                echo 'Invalid Schema calling';
                die();
            }
            $objSchema->runFunc($gid,$action,$args);
        }
    }

    function getVirtualCatById($cat_id=0){
        $vobjCat = &$this->app->model('goods_virtual_cat');
        $xml = kernel::single('site_utility_xml');
        $result=$vobjCat->getVirtualCatById(intval($cat_id));

        $searchtools = &$this->app->model('search');
        foreach($result as $k=>$v){
            $filter=$vobjCat->_mkFilter($result[$k]['filter']);
            $cat_id=$filter['cat_id'];
            $filter=$searchtools->encode($filter);
			$result[$k]['url']=$this->gen_url(array('app'=>'b2c','ctl'=>'site_gallery','act'=>$this->app->getConf('gallery.default_view'),'args'=>array(implode(',',$cat_id),$filter)));
        }
        echo json_encode($result);exit;
    }
    public function index() {
        $objProduct = &$this->app->model('products');

        //获取参数
        $_getParams = $this->_request->get_params();

        $gid = $_getParams[0];
        $specImg = $_getParams[1];
        $spec_id = $_getParams[2];
        $this->id = $gid;
        $this->customer_template_id=$gid;
        $objGoods = &$this->app->model('goods');

        if(!$this->id){
            $this->splash('failed', 'back', app::get('b2c')->_('无效商品！<br>可能是商品未上架'));
        }else{
            $rs = $objGoods->dump(array('goods_id'=>$this->id),'goods_id');
            if(!$rs || empty($rs)){
                $this->splash('failed', 'back', app::get('b2c')->_('无效商品！<br>可能是商品未上架'));
            }
        }



       
        $GLOBALS['runtime']['path'] = $objGoods->getPath($gid,'');
        $GLOBALS['runtime']['goods_id'] = $gid;

        //当前登陆用户信息
        $siteMember = $this->get_current_member();
        //当前登陆用户等级
        $this->site_member_lv_id = $siteMember['member_lv'];
        $this->pagedata['this_member_lv_id'] = $this->site_member_lv_id;

        //商品基本信息 goods表获取
        //$aGoods_list = $objGoods->getList("goods_id,name,bn,price,cost,mktprice,marketable,store,notify_num,score,weight,unit,brief,image_default_id,udfimg,thumbnail_pic,small_pic,big_pic,min_buy,package_scale,package_unit,package_use,score_setting,nostore_sell,goods_setting,disabled,spec_desc,adjunct,p_1,p_2,p_3,p_4,p_5,p_6,p_7,p_8,p_9,p_10,p_11,p_12,p_13,p_14,p_15,p_16,p_17,p_18,p_19,p_20,p_21,p_22,p_23,p_24,p_25,p_26,p_27,p_28,p_29,p_30,p_31,p_32,p_33,p_34,p_35,p_36,p_37,p_38,p_39,p_40,p_41,p_42,p_43,p_44,p_45,p_46,p_47,p_48,p_49,p_50,brand_id,type_id,cat_id,seo_info",array('goods_id'=>$gid));
        $aGoods_list = $objGoods->getList("store_id,goods_state,buy_m_count,fav_count,freight_bear,comments_count,avg_point,goods_id,name,bn,price,cost,mktprice,marketable,store,store_freeze,notify_num,score,weight,unit,brief,image_default_id,udfimg,thumbnail_pic,small_pic,big_pic,min_buy,package_scale,package_unit,package_use,score_setting,nostore_sell,goods_setting,disabled,spec_desc,adjunct,p_1,p_2,p_3,p_4,p_5,p_6,p_7,p_8,p_9,p_10,p_11,p_12,p_13,p_14,p_15,p_16,p_17,p_18,p_19,p_20,p_21,p_22,p_23,p_24,p_25,p_26,p_27,p_28,p_29,p_30,p_31,p_32,p_33,p_34,p_35,p_36,p_37,p_38,p_39,p_40,p_41,p_42,p_43,p_44,p_45,p_46,p_47,p_48,p_49,p_50,brand_id,type_id,cat_id,seo_info,act_type,goods_kind,goods_kind_detail",array('goods_id'=>$gid,'store_id|than'=>0));
        
        $goodsProcessor = null;
        if ($aGoods_list[0]['goods_kind'] == '3rdparty') {
            foreach(kernel::servicelist('3rdparty_goods_processor') as $processor) {
                if ($processor->goodsKindDetail() == $aGoods_list[0]['goods_kind_detail']) {
                    $goodsProcessor = $processor;
                    break;
                }
            }
        }
        
        if ($goodsProcessor && $goodsProcessor->isCustom('product')) {
            $goodsProcessor->productPage($aGoods_list[0], $this);
            return;
        }
        
        //获取详细的商品数据（包含货品，品牌，规格，类型,图片）
        $list2dump = kernel::single("b2c_goods_list2dump");
        $aGoods = $list2dump->get_goods($aGoods_list[0],$this->site_member_lv_id);

        //商品的真实库存（扣去了活动冻结库存）
        $goods_real_store = $aGoods_list[0]['store'] - $aGoods_list[0]['store_freeze'];

      
        $aGoods['store_id'] = $aGoods_list[0]['store_id'];
        $aGoods['goods_state'] = $aGoods_list[0]['goods_state'];
        $aGoods['buy_m_count'] = $aGoods_list[0]['buy_m_count'];
        $aGoods['fav_count'] = $aGoods_list[0]['fav_count'];
        $aGoods['gain_score'] = $aGoods_list[0]['score'];
        $aGoods['freight_bear'] = $aGoods_list[0]['freight_bear'];
        $aGoods['store_freeze'] = $aGoods_list[0]['store_freeze'];

        if($spec_id) $aGoods['spec_node'] = $spec_id;
        //$this->pagedata['store_info'] = $objGoods->getStoreInfo($aGoods['store_id']); // 店铺信息
        $this->pagedata['store_id'] = $aGoods['store_id'];
        $aInfo = app::get('business')->model('storemanger')->getList('area,account_id',array('store_id'=>$aGoods['store_id']));
        if($aInfo[0]['area'])
        list($a,$area_from,$b) = explode(':', $aInfo[0]['area']);
        else $area_from ='';
        if($area_from) $area_from = explode('/', $area_from);
        if(is_array($area_from)) $area_from = (($area_from[0] == '北京' || $area_from[0] == '天津' || $area_from[0] == '上海' || $area_from[0] == '重庆')?'':$area_from[0]).$area_from[1];
        else $area_from = '';
        $this->pagedata['area_from'] = $area_from;
        
        $this->pagedata['dlytype_info'] = $objGoods->getDlytype($aGoods); // 运费信息
        $objRegions = app::get('ectools')->model('regions');
        $this->pagedata['region_info'] = $objRegions->getList('region_id,local_name', array('region_grade'=>1,'disabled'=>'false')); // 地区信息
        $objPoint = app::get('business')->model('comment_goods_point');
        $this->pagedata['goods_point'] = array('avg_num'=>$aGoods_list[0]['avg_point'],'avg'=>$objPoint->star_class($aGoods_list[0]['avg_point']));
        $this->pagedata['total_discuss_nums'] = $aGoods_list[0]['comments_count'];
        
        $oMem = &$this->app->model('members');
       

        if(!$aGoods || $aGoods === false || !$aGoods['product']){
            $this->_response->clean_all_headers()->set_http_response_code('404')->send_headers();
            $this->splash('failed', 'back', app::get('b2c')->_('无效商品！<br>可能是商品未上架'));
            echo '无效商品！<br>可能是商品未上架';
            exit;
        }

        //反序列化商品配件信息
        if(!is_array($aGoods['adjunct'])){
            $aGoods['adjunct'] = unserialize($aGoods['adjunct']);
            $adjunct_goods_num = 0;
            foreach($aGoods['adjunct'] as $goods_adjunct_key => $goods_adjunct_value){
                if($goods_adjunct_value['items']['product_id']){
                    $adjunct_goods_num += count($goods_adjunct_value['items']['product_id']);
                }
            }
            $this->pagedata['adjunctGoodsNum'] = $adjunct_goods_num; //配件的商品数量
            $this->pagedata['adjunctNum'] = count($aGoods['adjunct']); //配件组的数量
        }

        //设置模板
        if( $aGoods['goods_setting']['goods_template'] ){
            $this->set_tmpl_file($aGoods['goods_setting']['goods_template']);                 //添加模板
        }
        $this->set_tmpl('product');

       if(is_array($aGoods['spec'])){
              foreach($aGoods['spec'] as $sv){
               $specValue[] = $sv['spec_name'];
              }
       }
       $this->pagedata['specShowItems'] =$specValue;

        //计算商品冻结总数
        $aGoods['freez'] = 0;
        if(count($aGoods['product'])){
            foreach($aGoods['product'] as $pdk=>$pdv){
                if($pdv['freez']) {
                    $aGoods['freez'] +=  $pdv['freez'];
                }
            }
        }

        //======商品会员价======
        if ($aGoods['product']){ //如果商品有货品处理价格
            $priceArea = array();
            if ($siteMember['member_lv'])
                $mlv = $siteMember['member_lv'];
            else{
                $level=&$this->app->model('member_lv');
                $mlv=$level->get_default_lv();
            }
            if ($mlv){
                foreach($aGoods['product'] as $gpk => &$gpv){
                   $currentPriceArea[]=$gpv['price']['price']['current_price'];//销售价区域
                   $priceArea[]=$gpv['price']['price']['price'];//销售价区域
                   if( $gpv['price']['mktprice']['price'] == '' || $gpv['price']['mktprice']['price'] == null ){
                       $mktpriceArea[]= $objProduct->getRealMkt($gpv['price']['mktprice']['price']);
                   }else{
                       $mktpriceArea[]=$gpv['price']['mktprice']['price'];//市场价区域
                   }
                }
                if (count($currentPriceArea)>1){
                   $aGoods['current_price'] = min($currentPriceArea);
                }
                if (count($priceArea)>1){
                    $minprice = min($priceArea);
                    $maxprice = max($priceArea);
                    if ($minprice<>$maxprice){
                        $aGoods['minprice'] = $minprice;
                        $aGoods['maxprice'] = $maxprice;
                    }
                }
                if ($this->app->getConf('site.show_mark_price')=="true" && count($mktpriceArea)>1){
                    $mktminprice = min($mktpriceArea);
                    $mktmaxprice = max($mktpriceArea);
                    if ($mktminprice<>$mktmaxprice){
                        $aGoods['minmktprice'] = $mktminprice;
                        $aGoods['maxmktprice'] = $mktmaxprice;
                    }
                }
            }
        }

        //换算积分
        if($aGoods_list[0]['score_setting'] == 'percent'){
            $point_money_value = $this->app->getConf('site.point_money_value');
            if($point_money_value == ''){
                $point_money_value = 1;
            }
            $aGoods['gain_score'] = intval($aGoods_list[0]['price'] * ($aGoods_list[0]['score']/100) * $point_money_value);

        }

        //======商品会员价 end======
        if(!$siteMember['member_id']){
            $this->pagedata['login'] = 'nologin';
        }
       
        else{
            $obj_store = app::get('business')->model('storemanger');
            $obj_smb = app::get('business')->model('storemember');
            $is_business = $obj_store->count(array('account_id'=>$siteMember['member_id']));
            if(!$is_business) $is_business = $obj_smb->count(array('member_id'=>$siteMember['member_id']));
            if($is_business > 0) $this->pagedata['login'] = 'business';
            else $this->pagedata['login'] = 'member';
        }
      

        //分配商品冻结库存总数
        $this->pagedata['goods']['product_freez'] = $aGoods['freez'];

        //当前用户使用货币相关信息
        $cur = app::get('ectools')->model('currency');
        $cur_info = $_COOKIE["S"]["CUR"]?$cur->getcur($_COOKIE["S"]["CUR"]):$cur->getFormat();
        if($cur_info['cur_sign']) {
            $cur_info['sign'] = $cur_info['cur_sign'];
        }
        $ret =array(
            'decimals'=>$this->app->getConf('system.money.decimals'),
            'dec_point'=>$this->app->getConf('system.money.dec_point'),
            'thousands_sep'=>$this->app->getConf('system.money.thousands_sep'),
            'fonttend_decimal_type'=>$this->app->getConf('system.money.operation.carryset'),
            'fonttend_decimal_remain'=>$this->app->getConf('system.money.decimals'),
            'sign' => $cur_info['sign']
        );
        if(isset($cur_info['cur_default']) && $cur_info['cur_default'] === "false") {
            $ret['cur_rate'] = $cur_info['cur_rate'];
        }
        unset($cur_info);

        $this->pagedata['goods']['setting']['score'] = $this->app->getConf('site.get_policy.method');
        $this->pagedata['money_format'] = json_encode($ret);
        $this->pagedata['goodsbndisplay'] = $this->app->getConf('goodsbn.display.switch');
        $this->pagedata['goodsBnShow'] = $this->app->getConf('goodsbn.display.switch');

        //配置数据
        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $setting['saveprice'] = $this->app->getConf('site.save_price');
        $setting['mktprice'] = $this->app->getConf('site.show_mark_price');
        $aGoods['setting'] = $setting;

        $this->pagedata['goods']['images'] = $aGoods['images'];

        $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');

        $tTitle=(empty($aGoods['seo']['seo_title']) ? $aGoods['name'] : $aGoods['seo']['seo_title']).(empty($aCat['cat_name'])?"":" - ".$aCat['cat_name']);
        if(empty($this->title)) $this->title = $tTitle;

        $this->setSeo('site_product','index',$this->prepareSeoData(array('goods'=>$aGoods)));

        if( is_string($aGoods['seo_info']) ){
            $aGoods['seo_info'] = unserialize( $aGoods['seo_info'] );
        }
        if( $aGoods['seo_info']['seo_title'] ){
            $this->title = $aGoods['seo_info']['seo_title'];
        }
        if( $aGoods['seo_info']['seo_keywords'] ){
            $this->keywords = $aGoods['seo_info']['seo_keywords'];
        }
        if( $aGoods['seo_info']['seo_description'] ){
            $this->description = $aGoods['seo_info']['seo_description'];
        }

        $setting['acomment']['switch']['ask'] = $this->app->getConf('comment.switch.ask');
        $setting['acomment']['switch']['discuss'] = $this->app->getConf('comment.switch.discuss');
        $this->pagedata['setting'] = $setting;
        /**** start 商品评分 ****/
       
        //$objPoint = app::get('business')->model('comment_goods_point');
        $goods_point_status = app::get('b2c')->getConf('goods.point.status');
        $this->pagedata['point_status'] = $goods_point_status ? $goods_point_status: 'on';
        //$this->pagedata['goods_point'] = $objPoint->get_single_point($gid);
        //$this->pagedata['total_discuss_nums'] = $this->app->model("member_comments")->count(array('object_type'=>'discuss', 'display'=>'true', 'type_id'=>$gid, 'for_comment_id'=>0, 'comments_type'=>'1'));
        $this->pagedata['seelist'] = kernel::single("b2c_goods_description_see2see")->showlist($gid);
        $this->pagedata['gpromotion_info'] = kernel::single('business_goods_detail_promotion')->show($gid,$siteMember);
        
        kernel::single('b2c_mdl_goods_view_history') -> add_history($siteMember['member_id'],$gid);
       
        /**** end 商品评分 ****/

        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];

        //相关商品数量统计
        $aGoods['goodslink'] = $objGoods->getLinkListNums($gid);

        //判断限时抢购活动是否开启
        if($aGoods_list[0]['act_type'] == 'timedbuy'){
            $timed_bus_obj = app::get('timedbuy')->model('businessactivity');
            $act_id = $timed_bus_obj->getList('aid,nums,discription,price,remainnums',array('gid'=>$gid,'disabled'=>'false','status'=>'2'));
            if(isset($act_id[0]['aid'])){
                $timed_act_obj = app::get('timedbuy')->model('activity');
                $info = $timed_act_obj->getList('*',array('act_id'=>$act_id[0]['aid']));
                if($info[0]['act_open'] == 'false' || $info[0]['active_status'] == 'start' || $info[0]['active_status'] == 'end'){
                    $this->pagedata['isEnd'] = true;
                }
                $this->pagedata['TimedbuyNums'] = $act_id[0]['remainnums'];
                $this->pagedata['TimedbuyPrice'] = $act_id[0]['price'];
                $this->pagedata['TimedbuyDis'] = $act_id[0]['discription'];
                $this->pagedata['isTimedbuy'] = true;
                $this->pagedata['timedbuyInfo'] = $info[0];
                $this->pagedata['timedbuyInfo']['stime'] = $info[0]['start_time'];
                $this->pagedata['timedbuyInfo']['start_time'] = date('Y年m月d日 H:i',$info[0]['start_time']);
                $this->pagedata['timedbuyInfo']['etime'] = $info[0]['end_time'];
                $this->pagedata['timedbuyInfo']['end_time'] = date('Y年m月d日 H:i',$info[0]['end_time']); 
                $this->pagedata['goodshtml']['store'] = kernel::single('timedbuy_goods_detail_store')->show($gid,$aGoods);
				$this->pagedata['timedbuy'] = 'true';
                $this->pagedata['NOWTIME'] = time();
            }else{
                $this->pagedata['goodshtml']['store'] = kernel::single('b2c_goods_detail_store')->show($gid,$aGoods);
            }
        }else{
            $this->pagedata['goodshtml']['store'] = kernel::single('b2c_goods_detail_store')->show($gid,$aGoods);
        }
        if ($goodsProcessor && $goodsProcessor->isCustom('product_store')) {
            $this->pagedata['goodshtml']['store'] = $goodsProcessor->productStoreHtml($this->pagedata['goodshtml']['store'], array('gid'=>$gid, 'aGoods'=>$aGoods));
        }
       
        $this->pagedata['goodshtml']['pic'] = kernel::single('b2c_goods_detail_pic')->show($gid,$aGoods);
        $this->pagedata['goodshtml']['mlv_price'] = kernel::single('b2c_goods_detail_mlvprice')->show($gid,$aGoods,$siteMember);
        $this->pagedata['goodshtml']['promotion_info'] = kernel::single('b2c_goods_detail_promotion')->show($gid,$siteMember);
        $this->pagedata['async_request_list'] = json_encode($this->get_body_async_url($aGoods));

        //计算商品冻结总数
        $aGoods['freez'] = 0;
        if(count($aGoods['product'])){
            foreach($aGoods['product'] as $pdk=>$pdv){
                if($pdv['freez']) {
                    $aGoods['freez'] +=  $pdv['freez'];
                }
            }
        }
        //分配商品冻结库存总数
        $this->pagedata['goods']['product_freez'] = $aGoods['freez'];

        //页面基本信息  servicelist  
        $this->pagedata['info_page_list'] = $this->_get_servicelist_by('b2c_products_index_info');
        if ($goodsProcessor && $goodsProcessor->isCustom('product_info')) {
            $this->pagedata['info_page_list'] = $goodsProcessor->productInfoPageList($this->pagedata['info_page_list']);
        }
        ///按钮  servicelist 
        $this->pagedata['btn_page_list'] = $this->_get_servicelist_by('b2c_products_index_btn',$this->pagedata['timedbuy']);
        if ($goodsProcessor && $goodsProcessor->isCustom('product_btn')) {
            $this->pagedata['btn_page_list'] = $goodsProcessor->productBtnPageList($this->pagedata['btn_page_list'], array('sign'=>$this->pagedata['timedbuy']));
        }

        // 商品详情页添加项埋点
        foreach( kernel::servicelist('goods_description_add_section') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'addSection') ) {
                    $services->addSection($this,$this->pagedata['goods']);
                }
            }
        }

        //检查买家是否是店家
        $checkSeller = kernel::service('business_check_goods_isMy');
        if($checkSeller){
            if($checkSeller->check_isSeller($msg)){
                $this->pagedata['isSeller'] = 'true';
            }else{
                $this->pagedata['isSeller'] = 'false';
            }
        }
        $this->page('site/product/index.html');
   }

    private function get_body_async_url($aGoods) {
        foreach( kernel::servicelist("b2c_product_index_async") as $object ) {
            if( !$object ) continue;
            $index = null;
            if( !method_exists($object,'getAsyncInfo') ) {
                continue;
            }

            if( method_exists($object,'get_order') )
                $index = $object->get_order();

            while(true) {
                if( !isset($list[$index]) ) break;
                $index++;
            }

            $asyncinfo = $object->getAsyncInfo($aGoods);
            if(!$asyncinfo) continue;
            $list[key($asyncinfo)] = ($asyncinfo[key($asyncinfo)]);

        }
        krsort($list);
        return $list;
    }

    function prepareSeoData($data){
        $brief = $this->get_goods_brief($data);
        $goodsCat = $this->get_goods_cat($data);
        return array(
            'goods_name'=>$data['goods']['name'],
            'goods_brand'=>$data['goods']['brand']['brand_name'],
            'goods_bn'=>$data['goods']['bn'],
            'goods_cat'=>$goodsCat,
            'goods_brief'=>$brief,
            'goods_price'=>$data['goods']['price']
        );
    }


    function goodsSpec( $gid = 0, $spec=null,&$aGoods = null ){
        $this->pagedata['goods_id'] = $gid;
        if($spec) $this->pagedata['spec_node'] = $spec; 
        list($usec,$sec) = explode(" ",microtime());
        $microtime = substr($usec,strpos($usec,'.')+1).$sec;
        $this->pagedata['goodsspec_classname'] = "goods-spec-".$gid."-".$microtime;
        $file = $this->custom_view?$this->custom_view:"site/product/goodsspec.html";
        echo $this->fetch($file);
        // 商品规格添加项埋点
        foreach( kernel::servicelist('goods_spec_add_html') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'addHtml') ) {
                    $services->addHtml();
                }
            }
        }
    }

       /*
      
       $gids like:  2,3,4,5,6,7

       @return like:
       [{"goods_id":"39","thumbnail_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/77900fbf8fcc94de.jpg","small_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/4d927b00ab29b199.jpg","big_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/389e97389f1616f7.jpg"},{"goods_id":"42","thumbnail_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/54d1c53bc455244f.jpg","small_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/9dce731f131aab5e.jpg","big_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/ac4420118e680927.jpg"}]
    */
    function picsJson(){
        $gids = explode(',',$_GET['gids']);

        if(!$gids)return '';
        $o = $this->app->model('goods');
        $imageDefault = app::get('image')->getConf('image.set');

        $data = $o->db_dump(current($gids),'image_default_id');
        if( !$data['image_default_id'] ){
            $data = base_storager::image_path( $imageDefault['S']['default_image'],'s' );
        }else{
            $img = base_storager::image_path($data['image_default_id'],'s' );
            if( $img )
                $data = $img;
            else
                $data = base_storager::image_path( $imageDefault['S']['default_image'],'s' );
        }
        echo json_encode($data);
        exit;
    }

     function diff(){
        $oMlv = &$this->app->model('member_lv');


        $this->_response->set_header('Cache-Control', 'no-store');
        $imageDefault = app::get('image')->getConf('image.set');
        $comare=explode("|",$_COOKIE['S']['GCOMPARE']);

        foreach($comare as $ci){
           $ci = stripslashes($ci);
           $ci = json_decode($ci,true);
           $gids[] = $ci['gid'];
        }

        $oGoods = &$this->app->model('goods');
         $aData = $this->get_current_member();
        if(!$aData['member_id']){
            $this->pagedata['login'] = 'nologin';
        }

        $this->pagedata['diff'] = $oGoods->diff($gids);

        foreach($this->pagedata['diff']['goods'] as $key=>$row){
             $this->pagedata['diff']['goods'][$key]['defaultImage'] = $imageDefault['S']['default_image'];
             $goods_name[] = $row['name'];
        }
        if(is_array($goods_name))
            $this->pagedata['goods']['name'] = implode(',',$goods_name);

        $objProduct = $this->app->model('products');
        $oGoodsLv = &$this->app->model('goods_lv_price');
        $siteMember = $this->get_current_member();
        $this->site_member_lv_id = $siteMember['member_lv'];
        $mlv = $oMlv->db_dump( $this->site_member_lv_id,'dis_count' );
        foreach ($this->pagedata['diff']['goods'] as $key=>&$val) {
            $temp = $objProduct->getList('product_id, spec_info, price, freez, store,   marketable, goods_id',array('goods_id'=>$val['goods_id'],'marketable'=>'true'));
            if( $this->site_member_lv_id ){
                $tmpGoods = array();
                foreach( $oGoodsLv->getList( 'product_id,price',array('goods_id'=>$val['goods_id'],'level_id'=>$this->site_member_lv_id ) ) as $k => $v ){
                    $tmpGoods[$v['product_id']] = $v['price'];
                }
                foreach( $temp as &$tv ){
                    $tv['price'] = (isset( $tmpGoods[$tv['product_id']] )?$tmpGoods[$tv['product_id']]:( $mlv['dis_count']*$tv['price'] ));
                }
                $val['price'] = (isset( $tmpGoods[$tv['product_id']] )?$tmpGoods[$tv['product_id']]:( $mlv['dis_count']*$val['price'] ));
            }
            $promotion_price = kernel::single('b2c_goods_promotion_price')->process($val);
            if(!empty($promotion_price['price'])){
                $val['price'] = $promotion_price['price'];
                $val['show_button'] = $promotion_price['show_button'];
                $val['timebuy_over'] = $promotion_price['timebuy_over'];
            }
            $this->pagedata['diff']['goods'][$key]['spec_desc_info'] = $temp;
            $this->pagedata['diff']['goods'][$key]['product_id'] = $temp[0]['product_id'];
        }
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];


        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;

        $this->page('site/product/diff.html');
    }


    function viewpic($goodsid, $selected='def'){
        $objGoods = &$this->app->model('goods');
        $o = &app::get('image')->model('image_attach');
        $dImg = $o->getList('*',array('target_id'=>$goodsid));
        $thumbnail_pic = $objGoods->getList('thumbnail_pic',array('goods_id'=>$goodsid));
        $aGoods = $objGoods->dump($goodsid,'name');
        $this->pagedata['goods_name'] = urlencode(htmlspecialchars($aGoods['name'],ENT_QUOTES));
        $this->pagedata['goods_name_show'] = $aGoods['name'];
        $this->pagedata['company_name'] = str_replace("'","&apos;",htmlspecialchars($this->app->getConf('system.shopname')));
        if(!empty($thumbnail_pic[0]['thumbnail_pic'])){
            $dImg[]['image_id'] = $thumbnail_pic[0]['thumbnail_pic'];
        }
        if(!$dImg){
            $imageDefault = app::get('image')->getConf('image.set');
            $dImg[]['image_id'] = $imageDefault['L']['image_id'];
            /*
            $selected=0;
            $id=rand(0,10);
            $dImg[$id]=array(
                'gimage_id'=>$id,
                'goods_id'=>$goodsid,
                'small'=>($this->app->getConf('site.default_small_pic')),
                'big'=>($this->app->getConf('site.default_big_pic')),
                'thumbnail'=>($this->app->getConf('site.default_thumbnail_pic'))
            );*/
        }
        if(is_array($dImg)){
            foreach($dImg as $dk=>$dv){
                $json_image[] = '\''.base_storager::image_path($dv['image_id'],'l').'\'';
            }
        }
        $this->pagedata['image_file'] = $dImg;
        $this->pagedata['image_file_total'] = count($dImg);
        if(count($json_image>0)){
            $this->pagedata['json_image'] = implode(',',$json_image);
        }

        if($selected=='def'){
            $selected=current($dImg);
            $selected=$selected['target_id'];
        }
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_default_id'] = $imageDefault['S']['default_image'];
        $this->pagedata['selected'] = $selected;
        $this->pagedata['goods_id'] = $goodsid;
        $shop['url']['shipping'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'shipping'));
        $shop['url']['total'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'total'));
        $shop['url']['region'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_tools','act'=>'selRegion'));
        $shop['url']['payment'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'payment'));
        $shop['url']['purchase_shipping'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'purchase_shipping'));
        $shop['url']['purchase_def_addr'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'purchase_def_addr'));
        $shop['url']['purchase_payment'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'purchase_payment'));
        $shop['url']['diff'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'diff'));
        $shop['base_url'] = $url;
        $this->pagedata['shopDefine'] = json_encode($shop);

        $this->page('site/product/viewpic.html',true);

    }


    function photo(){
    }

    function pic(){
    }

    function gnotify($goods_id=0,$product=0){
        if($_POST['goods']['goods_id']){
            $goods_id = $_POST['goods']['goods_id'];
            $product_id = $_POST['goods']['product_id'];
        }
        $this->id =$goods_id;
        $objGoods = &$this->app->model('goods');
        $aProduct = $objGoods->getProducts($goods_id, $product_id);
        $this->pagedata['goods'] = $aProduct[0];
        if($this->member[member_id]){
            #$objMember = &$this->system->loadModel('member/member');
            #$aMemInfo = $objMember->getFieldById($this->member[member_id], array('email'));
            $this->pagedata['member'] = $aMemInfo;
        }

        $this->page('site/product/gnotify.html');
    }

    function toNotify(){

        if (empty($_POST['email']) && empty($_POST['cellphone'])) {
            $this->splash('failed', 'back', app::get('b2c')->_('邮箱或手机号请至少填一项'),'','',true);
        }
        if(!empty($_POST['email']) && !preg_match('/\S+@\S+/',$_POST['email'])){
            $this->splash('failed', 'back', app::get('b2c')->_('邮箱格式错误'),'','',true);
        }
        if(!empty($_POST['cellphone']) && !preg_match('/^0?1[3458]\d{9}$/',$_POST['cellphone'])){
            $this->splash('failed', 'back', app::get('b2c')->_('手机格式错误'),'','',true);
        }
        if(!$_POST['item'][0]['goods_id'] || !$_POST['item'][0]['product_id']){
            $this->splash('failed', 'back', app::get('b2c')->_('参数错误'),'','',true);
        }
        $objGoods = &$this->app->Model('goods');
        $objProducts = &$this->app->Model('products');
        $ret = $objProducts->getList('product_id',array('product_id' => $_POST['item'][0]['product_id'],'goods_id' => $_POST['item'][0]['goods_id']));
        if(!$ret) $this->splash('failed', 'back', app::get('b2c')->_('参数错误'),'','',true);
        $back_url = app::get('site')->base_url(1);
        $member_goods = $this->app->model('member_goods');
        if($member_goods->check_gnotify($_POST)){
            $this->splash('failed','back',app::get('b2c')->_('不能重复登记'),'','',true);
        }else{
            $member_data = $this->get_current_member();
            if($member_goods->add_gnotify($member_data['member_id']?$member_data['member_id']:null,$_POST['item'][0]['goods_id'],$_POST['item'][0]['product_id'],$_POST['email'],$_POST['cellphone'])){
                $objGoods->db->exec("update sdb_b2c_goods set notify_num=notify_num+1 where goods_id = ".intval($_POST['item'][0]['goods_id']));
                $this->splash('success',$back_url,app::get('b2c')->_('登记成功'),'','',true);
            }else{
                $this->splash('failed','back',app::get('b2c')->_('登记失败'),'','',true);
            }
        }
    }

    function selllog($gid,$nPage=0){
        $nPage = $nPage?$nPage:1;
        $oPro = &$this->app->model('products');
        $sellLogList = $oPro->getGoodsSellLogList($gid, $nPage-1, $this->app->getConf('selllog.display.listnum') );
        $this->pagedata['sellLogList'] = $sellLogList;
        $this->pagedata['pager'] = array(
                'current'=> $nPage,
                'total'=> $sellLogList['page'],
                'link'=>  $this->gen_url( array('app'=>'b2c','ctl'=>'site_product',
                                'act'=>'selllog','args'=>array($gid,($tmp = time())))),
                'token'=>$tmp);
        $this->page('site/product/selllog.html');
    }

    function goodspics($goodsId,$images=array(),$imgGstr=''){
        $Goods=&$this->app->model('goods/gimage');
        $objGoods = &$this->app->model('trading/goods');
        $gimg=$_POST['gimages'];
        $goodsId=$_POST['goodsId'];
        if ($gimg){
            $tmpGimg=explode(",",$_POST['gimages']);
            if ($tmpGimg){
                foreach($tmpGimg as $key => $val){
                    if (!$val)
                        unset($tmpGimg[$key]);
                }
                $tmpImage=$Goods->get_by_gimage_id($goodsId,$tmpGimg);
            }
            $this->pagedata['imgtype'] = 'spec';
       }
        else{
            $tmpImage = $Goods->get_by_goods_id($goodsId);
        }
        $this->pagedata['images']['gimages']=$tmpImage;
        $this->pagedata['goods'] = $objGoods->getGoods($goodsId);
        if($this->pagedata['goods'] === false){
            echo '无效商品';
            $this->_response->clean_all_headers()->set_http_response_code('404')->send_headers();
            exit;
        }
        $this->__tmpl='product/goodspics.html';
        $this->output();
    }

    function get_brand($result){
        return $result['goods']['brand'];
    }
    function get_goods_name($result){
        return $result['goods']['name'];
    }
    function get_goods_bn($result){
        return $result['goods']['bn'];
    }
    function get_goods_cat($result){
        $pcat=$this->app->model('goods_cat');
        $cat_id = $result['goods']['category']['cat_id'];
        if(!cachemgr::get('goods_cat'.intval($cat_id),$row)){
            cachemgr::co_start();
            $row=$pcat->getList("cat_name",array('cat_id'=>$result['goods']['category']['cat_id']));
            cachemgr::set('goods_cat'.intval($cat_id), $row, cachemgr::co_end());
        }
        return $row[0]['cat_name'];
    }
    function get_goods_intro($result){
        $intro= strip_tags($result['goods']['intro']);
        if (strlen($intro)>50)
            $intro=substr($intro,0,50);
        return $intro;
    }
    function get_goods_brief($result){
        $brief= strip_tags($result['goods']['brief']);
        //$brief=preg_split('/(<[^<>]+>)/',$result['goods']['brief'],-1);
        if (strlen($brief)>50)
            $brief=substr($brief,0,50);
        return $brief;
    }
    function get_brand_kw($result){
        $brand=$this->app->model('goods/brand');
        $row=$brand->instance($result['goods']['brand_id'],'brand_keywords');
        return $row['brand_keywords'];
    }
    function get_goods_kw($result){
        /*
        $goods=$this->app->model('trading/goods');
        $row=$goods->getKeywords($result['goods']['goods_id']);
        if ($row){
            foreach($row as $key => $val){
                $tmpRow[]=$val['keyword'];
            }
            return implode(",",$tmpRow);
        }*/
            return;
    }
    function get_goods_price($result){
        return $result['goods']['price'];
    }
    function get_update_time($result){
        return date("c",$result['goods']['last_modify']);
    }

    function recooemd(){
        $back_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg'=>$_POST['goods_id']));
        $app = app::get('desktop');
        $aTmp['usermail'] = $app->getConf('email.config.usermail');
        $aTmp['smtpport'] = $app->getConf('email.config.smtpport');
        $aTmp['smtpserver'] = $app->getConf('email.config.smtpserver');
        $aTmp['smtpuname'] = $app->getConf('email.config.smtpuname');
        $aTmp['smtppasswd'] = $app->getConf('email.config.smtppasswd');
        $aTmp['sendway'] = $app->getConf('email.config.sendway');
        $aTmp['acceptor'] = $user_email;     //收件人邮箱
        $aTmp['shopname'] = $this->app->getConf('system.shopname');
        $acceptor = $_POST['email'];     //收件人邮箱
        $subject =app::get('b2c')->_("来自").$_POST['name'].'[]'.app::get('b2c')->_("的商品推荐");
        $url = &app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','full'=>1,'act'=>'index','arg'=>$_POST['goods_id']));
        $body = app::get('b2c')->_("尊敬的客户,您的好友").$_POST['name'].app::get('b2c')->_(',为您推荐了一款商品,请您点击查看').'<a href='.$url.'>'.$_POST['goods_name'].'</a>';
        $email = kernel::single('desktop_email_email');
        if ($email->ready($aTmp)){
             $res = $email->send($acceptor,$subject,$body,$aTmp);
            if ($res) {
                $this->splash('success',$back_url,app::get('b2c')->_('发送成功'),'','',true);
            }else{
                $this->splash('failed',$back_url,app::get('b2c')->_('发送失败,请联系管理员'),'','',true);
            }
        }else{

            $this->splash('failed',$back_url,app::get('b2c')->_('发送失败,请联系管理员'),'','',true);
        }
    }


    //////////////////////////////////////////////////////////////////////////
    // 返回servicelist
    // @param servicelist名称
    ///////////////////////////////////////////////////////////////////////////
    private function _get_servicelist_by($servicelist,$sign=false)
    {
        if( !$servicelist ) return false;
        $list = array();
        foreach( kernel::servicelist($servicelist) as $object ) {
            if( !$object ) continue;
            $index = null;
            if( !$object->file ) continue; //模板文件 没有直接跳过
            if( method_exists($object,'get_order') )
                $index = $object->get_order();

            while(true) {
                if( !isset($list[$index]) ) break;
                $index++;
            }
            $path = explode('_',get_class($object));

			if($sign&&method_exists($object,'get_type')&&($object->get_type())=='buy' ) {

			}else{
				$list[$index] = array(
									'file' => $object->file,
									'app'  => $object->_app ? $object->_app : $path[0],
								);
			}
            if( method_exists($object,'set_page_data') ) {
                $object->set_page_data($this->customer_template_id,$this);//设置html内容
            }

            if( $servicelist=='b2c_products_index_btn' ) {
                if( method_exists($object,'unique') ) {
                    if( $object->unique() ) {
                        $tmp = array_pop($list);
                        $list = array($tmp);break;
                    }
                }
            }

        }

        krsort($list);
        return $list;
    }

    function cron($goods_id){
        kernel::single('b2c_goods_crontab')->run($goods_id);
    }

    //配件
    function goodsAdjunct($gid=0, $aGoods=null){
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        echo kernel::single('b2c_goods_detail_adjunct')->show($gid,$aGoods,$this->custom_view);
    }

    function goodsBodyContent($gid=0, $aGoods=null) {
        if(!$aGoods){
            $objGoods = $this->app->model('goods');

            $aGoods_row = $objGoods->getList('goods_id,intro,price,cost,mktprice,type_id,brand_id,params,p_1,p_2,p_3,p_4,p_5,p_6,p_7,p_8,p_9,p_10,p_11,p_12,p_13,p_14,p_15,p_16,p_17,p_18,p_19,p_20,p_21,p_22,p_23,p_24,p_25,p_26,p_27,p_28,p_29,p_30,p_31,p_32,p_33,p_34,p_35,p_36,p_37,p_38,p_39,p_40,p_41,p_42,p_43,p_44,p_45,p_46,p_47,p_48,p_49,p_50',array('goods_id'=>intval($gid)));
            #$aGoods = kernel::single("b2c_goods_list2dump")->get_goods($aGoods_row[0]);
            $aGoods['type']['type_id'] = $aGoods_row[0]['type_id'];
            $aGoods['brand']['brand_id'] = $aGoods_row[0]['brand_id'];
            $aGoods['current_price'] = $aGoods_row[0]['price'];
            foreach ($aGoods_row[0] as $aGoods_k => $aGoods_v) {
                if(strpos($aGoods_k,"p_")===0)$aGoods['props'][$aGoods_k]['value'] = $aGoods_v;
            }
            $aGoods['description'] = $aGoods_row[0]['intro'];
        }
         #$aGoods = $objGoods->dump($gid,'goods_id,intro,price,cost,mktprice,type_id,brand_id,params,p_1,p_2,p_3,p_4,p_5,p_6,p_7,p_8,p_9,p_10,p_11,p_12,p_13,p_14,p_15,p_16,p_17,p_18,p_19,p_20,p_21,p_22,p_23,p_24,p_25,p_26,p_27,p_28,p_29,p_30,p_31,p_32,p_33,p_34,p_35,p_36,p_37,p_38,p_39,p_40,p_41,p_42,p_43,p_44,p_45,p_46,p_47,p_48,p_49,p_50');
        //$aGoods = $this->goodsDescription($gid, $aGoods);
        $aGoods['description'] = preg_replace("/(\<img[\s\S]+)src=/Us","\\1src='".$this->app->res_url."/images/transparent.gif' img-lazyload=",$aGoods['description']);
        /**** begin 商品评论 ****/
        $aComment['switch']['ask'] = $this->app->getConf('comment.switch.ask');
        $aComment['switch']['discuss'] = $this->app->getConf('comment.switch.discuss');
        if($aComment['switch']['ask'] == "on") {
            $aComment['askCount'] = $this->app->model("member_comments")->count(array('object_type'=>'ask', 'display'=>'true', 'type_id'=>$gid));
        }
        if($aComment['switch']['discuss'] == "on") {
            $aComment['discussCount'] = $this->app->model("member_comments")->count(array('object_type'=>'discuss', 'display'=>'true', 'type_id'=>$gid, 'for_comment_id'=>0, 'comments_type'=>'1'));
        }
        $this->pagedata['comment'] = $aComment;
        $this->pagedata['askshow'] = $this->app->getConf('comment.verifyCode.ask');
        $this->pagedata['discussshow'] = $this->app->getConf('comment.verifyCode.discuss');
        /**** end 商品评论 ****/

        /**** start 销售记录 ****/
        $this->pagedata['sellLogList'] = $this->app->model('products')->getGoodsSellLogList($gid,0,1);
        $this->pagedata['sellLog']['display'] = array(
            'switch' => $this->app->getConf('selllog.display.switch'),
            'limit' => $this->app->getConf('selllog.display.limit'),
            'listnum'=>$this->app->getConf('selllog.display.listnum')
        );
        /**** end 销售记录 ****/

        /**** start 商品推荐 ****/
        $this->pagedata['goodsRecommend'] = $this->app->getConf('goods.recommend');
        /**** end 商品推荐 ****/
        echo kernel::single("b2c_goods_description_intro")->show($gid,$aGoods,$this->custom_view);
    }

    function goodsDescription($gid=0, $aGoods=null){
        if(!$aGoods){
            $objGoods = $this->app->model('goods');

            /*$subsdf = array(
                'product'=>array(
                    'goods_id,product_id,price,marketable,bn',array(
                            'price/member_lv_price'=>array('*')
                        )
                ),
            );*/
            $aGoods = $objGoods->dump($gid,'goods_id,intro,price,cost,mktprice,type_id,brand_id,params,p_1,p_2,p_3,p_4,p_5,p_6,p_7,p_8,p_9,p_10,p_11,p_12,p_13,p_14,p_15,p_16,p_17,p_18,p_19,p_20,p_21,p_22,p_23,p_24,p_25,p_26,p_27,p_28,p_29,p_30,p_31,p_32,p_33,p_34,p_35,p_36,p_37,p_38,p_39,p_40,p_41,p_42,p_43,p_44,p_45,p_46,p_47,p_48,p_49,p_50');
        }
        //延迟加载详情的图片
        $aGoods['description'] = preg_replace("/(\<img[\s\S]+)src=/Us","\\1src='".$this->app->res_url."/images/transparent.gif' img-lazyload=",$aGoods['description']);
        return $aGoods;
    }

    //商品参数
    function goodsParams($gid=0, $aGoods=null){
        echo kernel::single("b2c_goods_description_params")->show($gid, $aGoods, $this->custom_view);
    }

    //获取相关商品
    function goodsLink($gid=0, $aGoods=null){
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        echo kernel::single("b2c_goods_description_linkgoods")->show($gid,$aGoods,$this->custom_view);
    }

    //获得销售记录
    function goodsSellLoglist($gid=0, $aGoods=null){
       
        $siteMember = $this->get_current_member();
        if(!$siteMember['member_id']){
            $this->pagedata['login'] = 'nologin';
        }
       
        echo kernel::single("b2c_goods_description_selllog")->show($gid,$aGoods,$this->custom_view);
    }

    //获得商品评论
    function goodsDiscuss($gid=0, $aGoods=null){
        $member_data = $this->get_current_member();
        if(!$member_data['member_id']){
            $this->pagedata['login'] = 'nologin';
        }
        $memInfo['member_id'] = $member_data['member_id'];
       
        $filter = $gid;
        if(is_array($filter)){
            if($filter['append']) $this->pagedata['toolbar']['append'] = 1;
            if(!$filter['content']) $this->pagedata['toolbar']['content'] = 1;
            $this->pagedata['toolbar']['orderb'] = $filter['orderb'];
            unset($filter['append'],$filter['content']);
            $gid = $filter['type_id']?$filter['type_id']:0;
        }else{
            $filter = array('type_id'=>$gid,'comment|noequal'=>'','orderb' => '1');
            $this->pagedata['toolbar']['orderb'] = '1';
        }
        $this->pagedata['filter'] = json_encode($filter);
        
        $objGoods = &$this->app->model('goods');
        $objPoint = app::get('business')->model('comment_goods_point');
        $aGoods_list = $objGoods->getList('avg_point,comments_count',array('goods_id'=>$gid,'store_id|than'=>0));
        $this->pagedata['goods_point'] = array('avg_num'=>$aGoods_list[0]['avg_point'],'avg'=>$objPoint->star_class($aGoods_list[0]['avg_point']));
        $this->pagedata['total_point_nums'] = $aGoods_list[0]['comments_count'];
        $this->pagedata['comment_goods_type'][] = array('type_id'=>0,'name'=>'商品评分');
        $this->pagedata['addition'] = $objGoods->db->selectrow("select count(distinct for_comment_id) as count from sdb_b2c_member_comments where type_id={$gid} and ifnull(for_comment_id,0)!=0 and object_type='discuss' and comments_type='3'");
       
        $this->pagedata['discuss_status'] = kernel::single('b2c_message_disask')->toValidate('discuss',$gid,$memInfo,$discuss_message);
        $this->pagedata['discuss_message'] = $discuss_message;
        $this->pagedata['discussshow'] = $this->app->getConf('comment.verifyCode.discuss');

        echo kernel::single("b2c_goods_description_comments")->show($filter,$aGoods,'discuss',$this->custom_view);
    }

    //获得商品咨询
    function goodsConsult($gid=0, $aGoods=null){
        $member_data = $this->get_current_member();
        if(!$member_data['member_id']){
            $this->pagedata['login'] = 'nologin';
        }
        $memInfo['member_id'] = $member_data['member_id'];
        $this->pagedata['ask_status'] = kernel::single('b2c_message_disask')->toValidate('ask',$gid,$memInfo,$ask_message);
        $this->pagedata['ask_message'] = $ask_message;
        $this->pagedata['askshow'] = $this->app->getConf('comment.verifyCode.ask');
        echo kernel::single("b2c_goods_description_comments")->show($gid,$aGoods,'ask', $this->custom_view);
    }

    //商品推荐
    function goodsRecommend($gid=0, $aGoods=null){
        $goods_model = app::get('b2c')->model('goods');
        $goods_list = $goods_model->getList('goods_id,name',array('goods_id'=>$gid));
        $this->pagedata['goods']=$goods_list[0]; 
        echo kernel::single("b2c_goods_description_recommend")->show($this->custom_view);
    }

    //商品促销,订单促销
    function goodsPromotion($gid){
        //$siteMember = $this->get_current_member();
        $file = 'site/product/goods_promotion.html';
        echo kernel::single("b2c_goods_detail_promotion")->show($gid,null,$file);
    }

   
    function get_city_area(){
        $city_id = intval($_POST['city_id']);
        $objRegions = app::get('ectools')->model('regions');
        $return_data = array();
        switch($city_id){
            case 1:
            case 21:
            case 42:
            case 62:
            break;
            default:
            $data = $objRegions->dump(array('region_id'=>$city_id),'p_region_id,region_id,local_name,region_grade,ordernum');
            do{
                $region_grade = $data['region_grade'];
                if($region_grade == 2){
                    $return_data = $objRegions->getList('region_id,local_name,ordernum',array('p_region_id'=>$data['p_region_id'],'region_id|noequal'=>$data['region_id']),0,-1);
                    if($data){
                        $data['self'] = 1;
                        $return_data[] = $data;
                    }
                    break;
                }elseif($region_grade == 1){
                    $return_data = $objRegions->getList('region_id,local_name,ordernum',array('p_region_id'=>$data['region_id']),0,-1);
                    break;
                }
            }while ($data = $objRegions->dump(array('region_id'=>$data['p_region_id']),'p_region_id,region_id,local_name,region_grade,ordernum'));
            break;
        }
        foreach ($return_data as $key => $row) {
            $vol1[$key]  = $row['ordernum'];
            $vol2[$key] = $row['region_id'];
        }
        array_multisort($vol1, SORT_DESC, $vol2, SORT_ASC, $return_data);
        //usort($return_data, "valuecmp");
        echo json_encode($return_data);
    }
    
    function valuecmp($a, $b){
        return strcmp($a["region_id"], $b["region_id"]);
    }
    
    function get_dlytype(){
        $gid = intval($_POST['goods_id']);
        $area_id = intval($_POST['area_id']);
        $objGoods = &$this->app->model('goods');
        $aGoods = $objGoods->getList("store_id,goods_state,weight",array('goods_id'=>$gid));
        echo json_encode($objGoods->getDlytype($aGoods[0],$area_id));
    }
    
    function comment_filter($gid=0){
        $filter = array();
        $filter['type_id'] = $gid;
        if($_POST['append']){
            $filter['comments_type'] = '3';
            $filter['append'] = true;
        }
        if($_POST['content']){
            $filter['comment|noequal'] = '';
            $filter['content'] = true;
        }else{
            $filter['comment'] = '';
        }
        switch($_POST['orderb']){
            case '1':
            $filter['orderb'] = '1';
            break;
            default:
            $filter['orderb'] = '1';
            break;
        }
        $this->goodsDiscuss($filter);
    }
    
    public function ajax_selllog(){
        $gid = $_POST['goods_id'];
        $page = $_POST['page'];
        if(!$gid or !$page) exit;

        $objProduct = $this->app->model('products');
        $sellLogList = $objProduct->getGoodsSellLogList($gid,$page,$this->app->getConf('selllog.display.listnum'));
        $sellLogSetting['display'] = array(
        'switch'=>$this->app->getConf('selllog.display.switch') ,
            'limit'=>$this->app->getConf('selllog.display.limit') ,
            'listnum'=>$this->app->getConf('selllog.display.listnum')
        );
        $this->pagedata['sellLog'] = $sellLogSetting;
        $this->pagedata['sellLogList'] = $sellLogList;

        for($i=0;$i<$sellLogList['page'];$i++){
            $this->pagedata['sellLog']['page'][] = $i;
        }
        $oSellLog = $this->app->model('sell_logs');
        $this->pagedata['sellLog']['all'] = $oSellLog->count(array('goods_id'=>$gid));
        $this->pagedata['goods']['goods_id'] = $gid;
        $siteMember = $this->get_current_member();
        if(!$siteMember['member_id']){
            $this->pagedata['login'] = 'nologin';
        }
        echo $this->fetch('site/product/selllog_content.html','b2c');
        exit;
    }
    
    public function goodssee($gid=0, $aGoods=null){
        echo kernel::single("b2c_goods_description_see2see")->show($gid,$aGoods,'site/product/description/see2see1.html');
    }
    
    public function ajax_see(){
        $gid = $_POST['goods_id'];
        $start = $_POST['start'];
        if(!$gid or !$start) exit;

        $objProduct = $this->app->model('products');
        $this->pagedata['goods'] = $objProduct->getGoodsSeeList($gid,$start);
        $this->pagedata['goods']['goods_id'] = $gid;
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['setting']['buytarget'] = $this->app->getConf('site.buy.target');
        $str_html = $this->fetch('site/product/see_list.html','b2c');
        echo '{data:"'.addslashes(str_replace("\n","",str_replace("\r\n","",$str_html))).'"}';
        exit;
    }
   

    //商品分类获得全部分类
    function show_3menu(){
        $mdl_goodsCat = app::get('b2c')->model('goods_cat');
        if($_POST['sel_cats'] == 0){
            $children = $this->get_cat_ids($_POST['cat_id']);
            $this->pagedata['data'] = $children;
        }else{
            $data = $mdl_goodsCat->get_subcat_list($_POST['cat_id']);
            foreach($data as $k=>&$v){
                $data[$k]{'sub_cats'} = $mdl_goodsCat->get_subcat_list($v['cat_id']);
            }
            $this->pagedata['data'] = $data;
        }
        $this->pagedata['sel_cats'] = $_POST['sel_cats'];
        $this->display('site/product/show_3menu.html');
    }


 public function get_cat_ids($cat_id){
         
          $mdl_goodsCat = app::get('b2c')->model('goods_cat');
          $child_cat=array();
		  $parent_id_arr=array($cat_id);
		  $child_type_ids=array();
		  $child_cat=$mdl_goodsCat->getList('cat_id,parent_id,cat_name,hidden'); 
          $cat_child_arr=array();
          $this->get_child_cat_ids($child_cat,$cat_child_arr,array($cat_id));
          return $cat_child_arr;
    }


    /*递归取得所有子类下的id */
    public function get_child_cat_ids(&$child_cat,&$cat_child_arr,$parent_id){
          $find_count=0;
          $flag_count=count($cat_child_arr);
          if(count($parent_id)>0){
             foreach ($parent_id as $k => $v) {
                foreach ($child_cat as $key => $value) {
                       if($value['parent_id']==$v){
                            $flag_count++;
                          $cat_child_arr[$flag_count]['cat_name']=$value['cat_name'];
                          $cat_child_arr[$flag_count]['cat_id']=$value['cat_id'];
                          $cat_child_arr[$flag_count]['hidden']=$value['hidden'];
                          unset($child_cat[$key]);
                          $find_count++;
                      }else{

                      }
                }
            }  
            unset($parent_id);
            if($find_count==0){
                return;
            }else{
                foreach ($cat_child_arr as $key => $value) {
                    $parent_id[]=$value['cat_id'];
                }
                $parent_id=array_unique($parent_id);
            }
          } 
          $this->get_child_cat_ids($child_cat,$cat_child_arr,$parent_id);


    }

    function getCurrentTime(){
        echo time();
    }
}
