<?php


class spike_ctl_site_product extends b2c_ctl_site_product{

    function __construct($app){
        parent::__construct($app);
        
    }

    public function index() {
        $objProduct = &app::get('b2c')->model('products');
        //获取参数
        $_getParams = $this->_request->get_params();

        $gid = $_getParams[0];
        $specImg = $_getParams[1];
        $spec_id = $_getParams[2];
        $act_id = $_getParams[3];
        $this->id = $gid;
        $this->customer_template_id=$gid;
        $objGoods = &app::get('b2c')->model('goods');

        if(!$this->id){
            $this->splash('failed', 'back', app::get('b2c')->_('无效商品！<br>可能是商品未上架'));
        }else{
            $rs = $objGoods->dump(array('goods_id'=>$this->id),'goods_id');
            if(!$rs || empty($rs)){
                $this->splash('failed', 'back', app::get('b2c')->_('无效商品！<br>可能是商品未上架'));
            }
        }

        $this->pagedata['nowtime'] = time();

        $GLOBALS['runtime']['path'] = $objGoods->getPath($gid,'');

        //当前登陆用户信息
        $siteMember = $this->get_current_member();
        //当前登陆用户等级
        $this->site_member_lv_id = $siteMember['member_lv'];
        $this->pagedata['this_member_lv_id'] = $this->site_member_lv_id;

        //商品基本信息 goods表获取
        $aGoods_list = $objGoods->getList("store_id,goods_state,buy_m_count,fav_count,freight_bear,comments_count,avg_point,goods_id,name,bn,price,cost,mktprice,marketable,store,notify_num,score,weight,unit,brief,image_default_id,udfimg,thumbnail_pic,small_pic,big_pic,min_buy,package_scale,package_unit,package_use,score_setting,nostore_sell,goods_setting,disabled,spec_desc,adjunct,p_1,p_2,p_3,p_4,p_5,p_6,p_7,p_8,p_9,p_10,p_11,p_12,p_13,p_14,p_15,p_16,p_17,p_18,p_19,p_20,p_21,p_22,p_23,p_24,p_25,p_26,p_27,p_28,p_29,p_30,p_31,p_32,p_33,p_34,p_35,p_36,p_37,p_38,p_39,p_40,p_41,p_42,p_43,p_44,p_45,p_46,p_47,p_48,p_49,p_50,brand_id,type_id,cat_id,seo_info,act_type",array('goods_id'=>$gid,'store_id|than'=>0));
        
        //获取详细的商品数据（包含货品，品牌，规格，类型,图片）
        $list2dump = kernel::single("b2c_goods_list2dump");
        $aGoods = $list2dump->get_goods($aGoods_list[0],$this->site_member_lv_id);
        
       
        $aGoods['store_id'] = $aGoods_list[0]['store_id'];
        $aGoods['goods_state'] = $aGoods_list[0]['goods_state'];
        $aGoods['buy_m_count'] = $aGoods_list[0]['buy_m_count'];
        $aGoods['fav_count'] = $aGoods_list[0]['fav_count'];
        $aGoods['gain_score'] = $aGoods_list[0]['score'];
        $aGoods['freight_bear'] = $aGoods_list[0]['freight_bear'];
        $aGoods['act_type'] = $aGoods_list[0]['act_type'];

        //判断是否是团购商品不是跳转至普通商品详细页
        $normalG_url = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_product', 'act' => 'index', 'arg0' => $gid));
        //if($aGoods['act_type'] != 'spike'){
            //header('Location:'.$normalG_url);
            //exit;
        //}

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
        
        

        if(!$aGoods || $aGoods === false || !$aGoods['product']){
            $this->_response->clean_all_headers()->set_http_response_code('404')->send_headers();
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
                $level=&app::get('b2c')->model('member_lv');
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
                if (app::get('b2c')->getConf('site.show_mark_price')=="true" && count($mktpriceArea)>1){
                    $mktminprice = min($mktpriceArea);
                    $mktmaxprice = max($mktpriceArea);
                    if ($mktminprice<>$mktmaxprice){
                        $aGoods['minmktprice'] = $mktminprice;
                        $aGoods['maxmktprice'] = $mktmaxprice;
                    }
                }
            }

            //修改秒杀价格
            //if($aGoods['act_type'] == 'spike'){
                $spikeapplyObj = $this->app->model('spikeapply');
                $spikeObj = $this->app->model('activity');
                $filter = array('gid'=>$this->id,'id'=>$act_id,'status'=>'2');
                $spikeapply = $spikeapplyObj->dump($filter,'*');
                //无活动时跳转至普通商品
                if(!$spikeapply || empty($spikeapply)){
                    header('Location:'.$normalG_url);
                    exit;
                }
                $activity = $spikeObj->dump(array('act_id'=>$spikeapply['aid']),'start_time,end_time,act_open,price_tag,activity_tag');
                
                $this->pagedata['activity'] = $activity;
                $this->pagedata['actapply'] = $spikeapply;

                $nowTime = time();
                if($activity['start_time'] > $nowTime){
                    $this->pagedata['time_info'] = array('name'=>'活动开始时间','time'=>date('Y年m月d日 H:i',$activity['start_time']));
                }else{
                    $this->pagedata['time_info'] = array('name'=>'活动结束时间','time'=>date('Y年m月d日 H:i',$activity['end_time']));
                }
                $aGoods['current_price'] = $spikeapply['last_price'];
            //}

        }

        //换算积分
        if($aGoods_list[0]['score_setting'] == 'percent'){
            $point_money_value = app::get('b2c')->getConf('site.point_money_value');
            if($point_money_value == ''){
                $point_money_value = 1;
            }
            $aGoods['gain_score'] = intval($aGoods['current_price'] * ($aGoods_list[0]['score']/100) * $point_money_value);

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
            'decimals'=>app::get('b2c')->getConf('system.money.decimals'),
            'dec_point'=>app::get('b2c')->getConf('system.money.dec_point'),
            'thousands_sep'=>app::get('b2c')->getConf('system.money.thousands_sep'),
            'fonttend_decimal_type'=>app::get('b2c')->getConf('system.money.operation.carryset'),
            'fonttend_decimal_remain'=>app::get('b2c')->getConf('system.money.decimals'),
            'sign' => $cur_info['sign']
        );
        if(isset($cur_info['cur_default']) && $cur_info['cur_default'] === "false") {
            $ret['cur_rate'] = $cur_info['cur_rate'];
        }
        unset($cur_info);

        $this->pagedata['goods']['setting']['score'] = app::get('b2c')->getConf('site.get_policy.method');
        $this->pagedata['money_format'] = json_encode($ret);
        $this->pagedata['goodsbndisplay'] = app::get('b2c')->getConf('goodsbn.display.switch');
        $this->pagedata['goodsBnShow'] = app::get('b2c')->getConf('goodsbn.display.switch');

        //配置数据
        $setting['buytarget'] = app::get('b2c')->getConf('site.buy.target');
        $setting['saveprice'] = app::get('b2c')->getConf('site.save_price');
        $setting['mktprice'] = app::get('b2c')->getConf('site.show_mark_price');
        $aGoods['setting'] = $setting;

        $this->pagedata['goods']['images'] = $aGoods['images'];

        $this->pagedata['spec_default_pic'] = app::get('b2c')->getConf('spec.default.pic');

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

        $setting['acomment']['switch']['ask'] = app::get('b2c')->getConf('comment.switch.ask');
        $setting['acomment']['switch']['discuss'] = app::get('b2c')->getConf('comment.switch.discuss');
        $this->pagedata['setting'] = $setting;
        /**** start 商品评分 ****/
      
        //$objPoint = app::get('business')->model('comment_goods_point');
        $goods_point_status = app::get('b2c')->getConf('goods.point.status');
        $this->pagedata['point_status'] = $goods_point_status ? $goods_point_status: 'on';
        //$this->pagedata['goods_point'] = $objPoint->get_single_point($gid);
        //$this->pagedata['total_discuss_nums'] = app::get('b2c')->model("member_comments")->count(array('object_type'=>'discuss', 'display'=>'true', 'type_id'=>$gid, 'for_comment_id'=>0, 'comments_type'=>'1'));
        $this->pagedata['seelist'] = kernel::single("b2c_goods_description_see2see")->showlist($gid);
        $this->pagedata['gpromotion_info'] = kernel::single('business_goods_detail_promotion')->show($gid,$siteMember);
        
        kernel::single('b2c_mdl_goods_view_history') -> add_history($siteMember['member_id'],$gid);
        
        /**** end 商品评分 ****/

        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];

        //相关商品数量统计
        $aGoods['goodslink'] = $objGoods->getLinkListNums($gid);

        $this->pagedata['goodshtml']['pic'] = kernel::single('b2c_goods_detail_pic')->show($gid,$aGoods);
        $this->pagedata['goodshtml']['store'] = kernel::single('spike_goods_detail_store')->show($gid,$aGoods);
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
        $this->pagedata['info_page_list'] = $this->_get_servicelist_by('spike_products_index_info');
        ///按钮  servicelist
        $this->pagedata['btn_page_list'] = $this->_get_servicelist_by('spike_products_index_btn');

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

    //////////////////////////////////////////////////////////////////////////
    // 返回servicelist
    // @param servicelist名称
    ///////////////////////////////////////////////////////////////////////////
    private function _get_servicelist_by($servicelist)
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


            $list[$index] = array(
                                'file' => $object->file,
                                'app'  => $object->_app ? $object->_app : $path[0],
                            );

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

    public function get_current_member()
    {
        if($this->member) return $this->member;
        $obj_members = app::get('b2c')->model('members');
        $this->member = $obj_members->get_current_member();
        //登陆受限检测
        if(is_array($this->member)){
            $minfo = $this->member;
            $mid = $minfo['member_id'];
            $res = $this->loginlimit($mid,$redirect);
            if($res){
                $this->redirect($redirect);
            }
        }
        return $this->member;
    }

    function get_goods_cat($result){
        $pcat = app::get('b2c')->model('goods_cat');
        $cat_id = $result['goods']['category']['cat_id'];
        if(!cachemgr::get('goods_cat'.intval($cat_id),$row)){
            cachemgr::co_start();
            $row=$pcat->getList("cat_name",array('cat_id'=>$result['goods']['category']['cat_id']));
            cachemgr::set('goods_cat'.intval($cat_id), $row, cachemgr::co_end());
        }
        return $row[0]['cat_name'];
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

    function joinspike(){
        $this->page('site/product/joinspike.html',true);
    }

    //function spikelist(){
        //$this->set_tmpl('spikelist');
        //$this->page('site/product/list.html');
    //}

}
