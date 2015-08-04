<?php
class cellphoneseller_goods_goods extends cellphoneseller_cellphoneseller{

    var $store_id = NULL;
    var $store = NULL;

    public function __construct($app){
        parent::__construct();
        $this->app = $app;

        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID'
        );
        $this->check_params($must_params);
        $member= $this->get_current_member();

        if(!$member['member_id']){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
            exit;
        }

        if($member['seller'] != 'seller'){
            $this->send(false,null,app::get('b2c')->_('非卖家账号'));
            exit;
        }

        $member_id = $member['member_id'];
        $this->store = $this->get_current_store($member_id);
        $this->store_id = $this->store['store_id'];
    }

    /**
     * list_get 宝贝列表获取接口
     * @author qianlei
     * switch 'onsell'：获取出售中的宝贝
     *        'instock'：获取仓库中的宝贝
     *        'alert'：获取预警中的宝贝
     **/
    public function list_get(){
        $params = $this->params;

        //检查接口级必填参数
        $must_params = array(
            'switch'=>'商品类型'
        );

        $this->check_params($must_params);
        $nPage = isset($params['nPage'])?intval($params['nPage']):1;
        $pagelimit = isset($params['pagelimit'])?intval($params['pagelimit']):10;
        $switch = $params['switch'];
        $filter = json_decode($params['filter'],true);
        //$picSize = isset($params['picSize'])?$params['picSize']:'cl';
        $switch_arr = array('onsell','instock','alert');
        $filter_arr = array(
                            'name',
                            'bn',
                            'cat_id',
                            'price_from',
                            'price_to',
                            'bcount_from',
                            'bcount_to',
                            'custom_cat_id'
                        );

        if(!in_array($switch,$switch_arr) || !$switch){
            $this->send(false,null,app::get('b2c')->_('商品类型'.$switch.'无效'));
            exit;
        }

        foreach($filter as $k=>$v){
            if(!in_array($k,$filter_arr)){
                $this->send(false,null,app::get('b2c')->_('商品类型'.$v.'无效'));
                exit;
            }
        }

        $filter['store_id'] = $this->store_id;
        $filter['disabled'] = 'false';

        $orderBy = 0;

        $Goods = $this->goods_info($filter,$switch,$orderBy,$nPage,$pagelimit);

        $this->send(true, $Goods, app::get('b2c')->_('success'));

    }

    /**
     * count_get 宝贝个数获取接口
     * @author qianlei
     * type   'onsell'：获取出售中的宝贝
     *        'instock'：获取仓库中的宝贝
     *        'alert'：获取预警中的宝贝
     **/
    public function count_get(){
        $params = $this->params;
        $type = isset($params['type'])?$params['type']:'all';
        $type_arr = array('onsell','instock','alert','all');

        if(!in_array($type,$type_arr)){
            $this->send(false,null,app::get('b2c')->_('商品类型'.$type.'无效'));
            exit;
        }

        $objGoods = app::get('business')->model('goods');
        $Data = array();
        $filter['disabled'] = 'false';
        $filter['store_id'] = $this->store_id;
        switch($type){
            case 'all':
            case 'onsell':
                $filter['marketable'] = 'true';
                $Data['onsell'] = $objGoods->count($filter);
                if($type != 'all'){
                    break;
                }
            case 'instock':
                $filter['marketable'] = 'false';
                $Data['instock'] = $objGoods->count($filter);
                if($type != 'all'){
                    break;
                }
            case 'alert':
                $alert_num = app::get('b2c')->getConf('system.product.alert.num');
                $obj_strman = app::get('business')->model('storemanger');
                $alert_num = $obj_strman->getList('alert_num', array('store_id'=>$this->store_id));
                $alert_num = intval($alert_num[0]['alert_num']);
                $filter['marketable'] = 'true';
                $filter['store|sthan'] = $alert_num;
                $Data['alert'] = $objGoods->count($filter);
                if($type != 'all'){
                    break;
                }
        }

        $this->send(true, $Data, app::get('b2c')->_('success'));
    }

    public function detail_update(){
        $params = $this->params;

        //检查接口级必填参数
        $must_params = array(
            'goods_id'=>'商品ID'
        );
        //if(isset($params['price']) || isset($params['mktprice']) || isset($params['store'])){
            //$must_params['product_id'] = '货品ID';
        //}
        $this->check_params($must_params);
        $gid = intval($params['goods_id']);
        //$pid = isset($params['product_id'])?intval($params['product_id']):NULL;
        $price = isset($params['price'])?$params['price']:NULL;
        $mktprice = isset($params['mktprice'])?$params['mktprice']:NULL;
        $store = isset($params['store'])?$params['store']:NULL;
        $freight_bear = isset($params['freight_bear'])?$params['freight_bear']:NULL;
        $marketable = isset($params['marketable'])?$params['marketable']:NULL;

        $objGoods = &app::get('b2c')->model('goods');
        $objProduct = &app::get('b2c')->model('products');
        $cols = "store_id,goods_id,store,store_freeze,disabled";
        $gfilter = array(
            'goods_id'=>$gid,
            'store_id'=>$this->store_id,
            'disabled'=>'false'
        );
        $aGoods_list = $objGoods->getList($cols ,$gfilter);
        if(!$aGoods_list || empty($aGoods_list)){
            $this->send(false, null, app::get('b2c')->_('无效商品！'));
            exit;
        }

        $cols = "product_id,goods_id,store,freez,disabled";
        $pfilter = array(
            'goods_id'=>$gid,
            //'product_id'=>$pid,
            'disabled'=>'false'
        );
        $product_list = $objProduct->getList($cols ,$pfilter);
        if(!$product_list || empty($product_list)){
            $this->send(false, null, app::get('b2c')->_('无效商品！'));
            exit;
        }
        $products = array();
        foreach($product_list as $k=>$v){
            $products[$v['product_id']] = $v;
        }


        if($freight_bear){
            $freight_bear_arr = array('member','business');
            if(!in_array($freight_bear,$freight_bear_arr)){
                $this->send(false, null, app::get('b2c')->_('freight_bear参数值有误！'));
                exit;
            }
        }

        if($marketable !== NULL){
            $$marketable_arr = array('true','false',true,false);
            if(!in_array($marketable,$$marketable_arr)){
                $this->send(false, null, app::get('b2c')->_('marketable参数值有误！'));
                exit;
            }
        }

        $product = array();
        if($price !== NULL){
            
            $product['price'] = json_decode($price,true);
        }
        if($mktprice !== NULL){
            $product['mktprice'] = json_decode($mktprice,true);
        }

        if($store !== NULL){
            $store = json_decode($store,true);
            foreach($store as $k=>$v){
                if($v<0){
                    $this->send(false, null, app::get('b2c')->_('库存数必须是正整数！'));
                    exit;
                }

                $freez = $products[$k]['store'] - $v;

                if($freez > 0){
                    if(!$objGoods->check_freez($gid, $k, $freez)){
                        $this->send(false, null, app::get('b2c')->_('该货品含有冻结库存！库存数不能小于冻结库存'));
                        exit;
                    }
                }
            }
            $product['store'] = $store;

        }
        $pinfo = array();
        if(!empty($product)){
            foreach($product as $k=>$v){
                foreach($v as $key=>$value){
                    $pinfo[$key][$k] = $value;
                }
            }
        }

        if(!empty($pinfo)){
            foreach($pinfo as $k=>$v){
                if(!$objProduct->update($v,array('product_id'=>$k))){
                    $this->send(false, null, app::get('b2c')->_('更新失败'));
                    exit;
                }
            }
        }

        $goods = array();

        if($freight_bear !== NULL){
            $goods['freight_bear'] = $freight_bear;
        }

        if($marketable !== NULL){
             $goods['marketable'] = $marketable;
        }

        if($price !== NULL || $mktprice !== NULL || $store !==NULL){
            $cols = 'max(mktprice) as mktprice,min(price) as price ,sum(store) as gstore';
            $filter = array('goods_id'=>$gid,'disabled'=>'false');

            $goodsPrice = $objProduct->getList($cols,$filter);

            $goods['price'] = $goodsPrice[0]['price'];
            $goods['mktprice'] = $goodsPrice[0]['mktprice'];
            $goods['store'] = $goodsPrice[0]['gstore'];
        }

        if(!empty($goods)){
            if(!$objGoods->update($goods,array('goods_id'=>$gid))){
                $this->send(false, null, app::get('b2c')->_('更新失败'));
                exit;
            }
        }

        $this->send(true, null, app::get('b2c')->_('success'));

    }

    /**
     * detail_get 获取单个宝贝的信息接口方法
     * @author qianlei
     **/
    public function detail_get(){
        $params = $this->params;
        $must_params = array(
            'goods_id'=>'商品标识',
        );
        $this->check_params($must_params);
        $objGoods = &app::get('b2c')->model('goods');
        $gid = intval($params['goods_id']);
        if(!$gid){
            $this->send(false, null, app::get('b2c')->_('无效商品！可能是商品未上架'));
            exit;
        }else{
            $rs = $objGoods->dump(array('goods_id'=>$gid),'goods_id');
            if(!$rs || empty($rs)){
                $this->send(false, null, app::get('b2c')->_('无效商品！可能是商品未上架'));
                exit;
            }
        }
        $picSize = in_array(strtolower($params['pic_size']), array('cl', 'cs'))?strtolower($params['pic_size']):'cl';
        $aGoods = array();
        $siteMember = $this->get_current_member();
        $siteMember['member_lv'] = isset($siteMember['member_lv'])?$siteMember['member_lv']:-1;
        $cols = "store_id,bn,goods_id,cat_id,name,price,mktprice,marketable,act_type,buy_m_count,fav_count,comments_count
,avg_point,freight_bear,store,store_freeze,notify_num,score,score_setting,weight,unit,min_buy,package_scale,package_unit,package_use,nostore_sell,disabled,spec_desc,brief,adjunct,freight_bear";
        $aGoods_list = $objGoods->getList($cols ,array('goods_id'=>$gid, 'store_id|than'=>0, 'disabled'=>'false'));
        $sql = "select * from sdb_b2c_goods_keywords where goods_id ={$gid} and res_type='goods'";
        $keywords = $objGoods->db->select($sql);
        $sql = "select cat_id,cat_name,p_order,type_id,hidden,disabled from sdb_b2c_goods_cat where cat_id =".$aGoods_list[0]['cat_id'];
        $cat_info = $objGoods->db->selectrow($sql);
        $aGoods['store_id'] = $aGoods_list[0]['store_id']?intval($aGoods_list[0]['store_id']):0;
        $aGoods['goods_id'] = $aGoods_list[0]['goods_id'];
        $aGoods['cat'] = $cat_info;
        $aGoods['bn'] = $aGoods_list[0]['bn'];
        $aGoods['marketable'] = $aGoods_list[0]['marketable'];
        $aGoods['name'] = $aGoods_list[0]['name'];
        $aGoods['price'] = $aGoods_list[0]['price'];
        $aGoods['brief'] = $aGoods_list[0]['brief'];
        $aGoods['freight_bear'] = $aGoods_list[0]['freight_bear'];
        $aGoods['keywords'] = $keywords;

        $objProduct = &app::get('b2c')->model('products');
        if( $aGoods_list[0]['mktprice'] == '' || $aGoods_list[0]['mktprice'] == null )
            $aGoods['mktprice'] = $products->getRealMkt($aGoods['price']);
        else
            $aGoods['mktprice'] = $aGoods_list[0]['mktprice'];
        $aGoods['act_type'] = $aGoods_list[0]['act_type'];
        $aGoods['buy_m_count'] = $aGoods_list[0]['buy_m_count'];
        $aGoods['fav_count'] = $aGoods_list[0]['fav_count'];
        $aGoods['comments_count'] = $aGoods_list[0]['comments_count'];
        $aGoods['avg_point'] = $aGoods_list[0]['avg_point'];
        $aGoods['freight_bear'] = $aGoods_list[0]['freight_bear'];
        $aGoods['weight'] = $aGoods_list[0]['weight'];
        $aGoods['unit'] = $aGoods_list[0]['unit'];
        $aGoods['store'] = $aGoods_list[0]['store'];
        $aGoods['freez'] = 0;
        $aGoods['store_freeze'] = $aGoods_list[0]['store_freeze'];
        $aGoods['unit'] = $aGoods_list[0]['unit'];
        if($aGoods_list[0]['score_setting'] == 'percent'){
            $point_money_value = $this->app->getConf('site.point_money_value');
            if($point_money_value == ''){
                $point_money_value = 1;
            }
            $aGoods['gain_score'] = intval($aGoods_list[0]['price'] * ($aGoods_list[0]['score']/100) * $point_money_value);
        }else{
            $aGoods['gain_score'] = $aGoods_list[0]['score'];
        }

        $objMemberLv = &app::get('b2c')->model("member_lv");
        $memLvaData = array();
        foreach((array)$objMemberLv->getList('member_lv_id,name,dis_count') as $row){
            $memLvaData[$row['member_lv_id']] = $row;
        }
        $sql = "SELECT p.product_id,p.price,p.mktprice,p.store,p.freez,p.spec_desc,l.level_id,l.price as lv_price
FROM  `sdb_b2c_products` AS p
LEFT JOIN  `sdb_b2c_goods_lv_price` AS l ON p.product_id = l.product_id
WHERE p.goods_id ={$gid}";
        $productAmp = array();

        foreach((array)$objMemberLv->db->select($sql) as $key=>$pro_v){
            $productAmp[$pro_v['product_id']]['spec_desc'] = unserialize($pro_v['spec_desc']);
            $productAmp[$pro_v['product_id']]['store'] = $pro_v['store'];
            $productAmp[$pro_v['product_id']]['freez'] = $pro_v['freez'];
            $productAmp[$pro_v['product_id']]['product_id'] = $pro_v['product_id'];
            if(array_key_exists($siteMember['member_lv'], $memLvaData)){
                $memLv_v = $memLvaData[$siteMember['member_lv']];
                $productAmp[$pro_v['product_id']]['price'] = $pro_v['level_id']?$pro_v['lv_price']:(($memLv_v['dis_count']>0?$memLv_v['dis_count'] * $pro_v['price']:$pro_v['price']));
            }else{
                $productAmp[$pro_v['product_id']]['price'] = $pro_v['price'];
            }
            $productAmp[$pro_v['product_id']]['mktprice'] = $pro_v['mktprice'];
            $aGoods['freez'] +=  intval($pro_v['freez']);
        }
        $aGoods['product'] = array_values($productAmp);

        $objImage = &app::get("image")->model("image_attach");
        $image_data = $objImage->getList("attach_id,image_id",array("target_id"=>$gid,'target_type'=>'goods'),0,-1,"attach_id asc");
        $images = array();
        if(!empty($image_data)){
            foreach($image_data as $row){
                $images[] = $this->get_img_url($row['image_id'],$picSize);
            }
        }else{
            $images[0] = $this->get_img_url('',$picSize);
        }
        $aGoods['images'] = $images;
        $objSpec = &app::get('b2c')->model('specification');
        if( $aGoods_list[0]['spec_desc'] && is_array( $aGoods_list[0]['spec_desc'] ) ){
            foreach( $aGoods_list[0]['spec_desc'] as $specId => $spec ){
                $aRow = $objSpec->getList("*",array('spec_id'=>$specId));
                $aGoods['spec'][$specId] = $aRow[0];
                foreach( $spec as $pSpecId => $specValue ){
                    $specValue['spec_image'] = $this->get_img_url($specValue['spec_image'],$picSize);
                    $aGoods['spec'][$specId]['option'][$pSpecId] = array_merge( array('private_spec_value_id'=>$pSpecId), $specValue );
                }
            }
            $aGoods['spec'] = array_values($aGoods['spec']);
        }
        if( $aGoods['product'] ){
            $aProduct = current( $aGoods['product']);
            if( isset( $aProduct['price'] ) )
                $aGoods['current_price'] = $aProduct['price'];
        }elseif( $aGoods['price'] ){
            $aGoods['current_price'] = $aGoods['price'];
        }
        
        if(!$aGoods || $aGoods === false || !$aGoods['product']){
            $this->send(false, null, app::get('b2c')->_('无效商品！可能是商品未上架'));
            exit;
        }
        //$aInfo = app::get('business')->model('storemanger')->getList('area,account_id',array('store_id'=>$aGoods['store_id']));
        //if($aInfo[0]['area'])
        //list($a,$area_from,$b) = explode(':', $aInfo[0]['area']);
        //else $area_from ='';
        //if($area_from) $area_from = explode('/', $area_from);
        //if(is_array($area_from)) $area_from = (($area_from[0] == '北京' || $area_from[0] == '天津' || $area_from[0] == '上海' || $area_from[0] == '重庆')?'':$area_from[0]).$area_from[1];
        //else $area_from = '';
        //$aGoods['area_from'] = $area_from;
        //$aGoods['dlytype_info'] = array();
        //if($aGoods['freight_bear'] == 'member'){
            //$aGoods['dlytype_info'] = $this->freight_detial($gid, $aGoods['store_id'], $aGoods['weight']);
        //}
        //if(!$siteMember['member_id']){
            //$aGoods['login_status'] = 'nologin';
        //}else{
            //$obj_store = app::get('business')->model('storemanger');
            //$obj_smb = app::get('business')->model('storemember');
            //$is_business = $obj_store->count(array('account_id'=>$siteMember['member_id']));
            //if(!$is_business) $is_business = $obj_smb->count(array('member_id'=>$siteMember['member_id']));
            //if($is_business > 0) $aGoods['login_status'] = 'business';
            //else $aGoods['login_status'] = 'member';
        //}

        //$cur = app::get('ectools')->model('currency');
        //$cur_info = $_COOKIE["S"]["CUR"]?$cur->getcur($_COOKIE["S"]["CUR"]):$cur->getFormat();
        //if($cur_info['cur_sign']) {
            //$cur_info['sign'] = $cur_info['cur_sign'];
        //}
        //$ret =array(
            //'decimals'=>$this->app->getConf('system.money.decimals'),
            //'dec_point'=>$this->app->getConf('system.money.dec_point'),
            //'thousands_sep'=>$this->app->getConf('system.money.thousands_sep'),
            //'fonttend_decimal_type'=>$this->app->getConf('system.money.operation.carryset'),
            //'fonttend_decimal_remain'=>$this->app->getConf('system.money.decimals'),
            //'sign' => $cur_info['sign']
        //);
        //if(isset($cur_info['cur_default']) && $cur_info['cur_default'] === "false") {
            //$ret['cur_rate'] = $cur_info['cur_rate'];
        //}
        //$aGoods['money_format'] = json_encode($ret);
        //$aGoods['discuss_info'] = $this->discuss_detial(array('goods_id'=>$gid,'pageLimit'=>'2'));
        //$objComment = app::get('business')->model('comment_stores_point');
        //$store_info = $objComment->getStoreInfo($aGoods['store_id']);
        //foreach((array)$store_info['store_point'] as $row){
            //$aGoods['store_info'][] = array(
                //'name' => $row['name'],
                //'avg_point' => $row['avg_point'],
                //'avg_percent' => $row['avg_percent'],
            //);
        //}
        
        $activity_cat = kernel::service('business_activity_cat');
        $activityTab = array();
        if($activity_cat){
            foreach((array)$activity_cat->loadActivityCat() as $row){
                if($row['app']=='groupbuy') $row['app'] = 'group';
                if($row['app']=='scorebuy') $row['app'] = 'score';
                $activityTab[] = $row['app'];
            }
        }
        if($aGoods['act_type'] && $activityTab && in_array($aGoods['act_type'], $activityTab)){
            $aGoods['act_detial'] = array();
            if($aGoods['act_type'] == 'timedbuy'){
                $objapply = app::get('timedbuy')->model('businessactivity');
                $act_info = $objapply->getList('aid,price,remainnums,presonlimit',array('gid'=>$gid,'disabled'=>'false','status'=>'2'));
                if(isset($act_info[0]['aid'])){
                    $objact = app::get('timedbuy')->model('activity');
                    $info = $objact->getList('start_time,end_time',array('act_id'=>$act_info[0]['aid'],'act_open'=>'true'));
                    $aGoods['act_detial']['price'] = $act_info[0]['price'];
                    $aGoods['act_detial']['limit'] = $act_info[0]['presonlimit'];
                    $aGoods['act_detial']['stime'] = $info[0]['start_time'];
                    $aGoods['act_detial']['etime'] = $info[0]['end_time'];
                    if(count($aGoods['product'])){
                        foreach($aGoods['product'] as $pdk=>$pdv){
                            //处理货品库存与商品真实总库 取小的值
                            $aGoods['product'][$pdk]['store'] = min($pdv['store'],$act_info[0]['remainnums']);
                        }
                    }
                    $aGoods['current_price'] = $act_info[0]['price'];
                    $aGoods['store'] = min($act_info[0]['remainnums'],($aGoods['store']-$aGoods['freez']));
                }
            }elseif($aGoods['act_type'] == 'score'){
                $objapply = app::get('scorebuy')->model('scoreapply');
                $objact = app::get('scorebuy')->model('activity');
                $act_info = $objapply->getList('aid,last_price,score,isMemLv,personlimit,remainnums', array('gid'=>$gid,'status'=>'2'));
                if(isset($act_info[0]['aid']) && $objapply && $objact){
                    $activity = $objact->getList('start_time,end_time',array('act_id'=>$act_info[0]['aid'],'act_open'=>'true'));
                    $aGoods['act_detial']['price'] = $act_info[0]['last_price'];
                    $aGoods['act_detial']['limit'] = $act_info[0]['personlimit'];
                    $aGoods['act_detial']['stime'] = $info[0]['start_time'];
                    $aGoods['act_detial']['etime'] = $info[0]['end_time'];
                    if(count($aGoods['product'])){
                        foreach($aGoods['product'] as $pdk=>$pdv){
                            //处理货品库存与商品真实总库 取小的值
                            $aGoods['product'][$pdk]['store'] = min($pdv['store'],$act_info[0]['remainnums']);
                        }
                    }
                    $aGoods['current_price'] = $act_info[0]['last_price'];
                    $aGoods['store'] = min($act_info[0]['remainnums'],($aGoods['store']-$aGoods['freez']));
                    if($siteMember['member_lv']>0 && $act_info['isMemLv'] == '1'){
                        $memLvScoreObj = app::get('scorebuy')->model('memberlvscore');
                        $memlvs = $memLvScoreObj->getMemLvScoreByIds($act_info[0]['aid'],$gid);
                        $aGoods['act_detial']['price'] = $memlvs[$siteMember['member_lv']]['last_price'];
                        $aGoods['act_detial']['score'] = $memlvs[$siteMember['member_lv']]['score'];
                    }
                }
            }else{
                switch($aGoods['act_type']){
                    case 'group':
                        $objapply = app::get('groupbuy')->model('groupapply');
                        $objact = app::get('groupbuy')->model('activity');
                        break;
                    case 'spike':
                        $objapply = app::get('spike')->model('spikeapply');
                        $objact = app::get('spike')->model('activity');
                        break;
                }
                $act_info = $objapply->getList('aid,last_price,personlimit,remainnums', array('gid'=>$gid,'status'=>'2'));
                if(isset($act_info[0]['aid']) && $objapply && $objact){
                    $activity = $objact->getList('start_time,end_time',array('act_id'=>$act_info[0]['aid'],'act_open'=>'true'));
                    $aGoods['act_detial']['price'] = $act_info[0]['last_price'];
                    $aGoods['act_detial']['limit'] = $act_info[0]['personlimit'];
                    $aGoods['act_detial']['stime'] = $info[0]['start_time'];
                    $aGoods['act_detial']['etime'] = $info[0]['end_time'];
                    if(count($aGoods['product'])){
                        foreach($aGoods['product'] as $pdk=>$pdv){
                            //处理货品库存与商品真实总库 取小的值
                            $aGoods['product'][$pdk]['store'] = min($pdv['store'],$act_info[0]['remainnums']);
                        }
                    }
                    $aGoods['current_price'] = $act_info[0]['last_price'];
                    $aGoods['store'] = min($act_info[0]['remainnums'],($aGoods['store']-$aGoods['freez']));
                }
            }
            
        }
        $this->send(true, $aGoods, app::get('b2c')->_('success'));
    }

    /**
     * store_update 修改宝贝库存接口方法
     * @author qianlei
     **/
    public function store_update(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'goods_id'=>'商品ID',
            'product_id'=>'货品ID',
            'num'=>'库存数'
        );
        $this->check_params($must_params);
        $objGoods = &app::get('b2c')->model('goods');
        $objProduct = &app::get('b2c')->model('products');
        $gid = intval($params['goods_id']);
        $pid = intval($params['product_id']);
        $num = intval($params['num']);
        if(!$gid){
            $this->send(false, null, app::get('b2c')->_('无效商品！'));
            exit;
        }else{
            $cols = "store_id,goods_id,store,store_freeze,disabled";
            $filter = array(
                        'goods_id'=>$gid,
                        'store_id'=>$this->store_id,
                        'disabled'=>'false'
            );
            $aGoods_list = $objGoods->getList($cols ,$filter);
            if(!$aGoods_list || empty($aGoods_list)){
                $this->send(false, null, app::get('b2c')->_('无效商品！'));
                exit;
            }
        }

        if(!$pid){
            $this->send(false, null, app::get('b2c')->_('无效货品！'));
            exit;
        }else{
            $cols = "product_id,goods_id,store,freez,disabled";
            $product_list = $objProduct->getList($cols ,array('goods_id'=>$gid, 'product_id'=>$pid, 'disabled'=>'false'));
            if(!$product_list || empty($product_list)){
                $this->send(false, null, app::get('b2c')->_('无效货品！'));
                exit;
            }
        }

        if($num<0){
            $this->send(false, null, app::get('b2c')->_('库存数必须是正整数！'));
            exit;
        }

        $freez = $product_list[0]['store'] - $num;

        if($freez > 0){
            if(!$objGoods->check_freez($gid, $pid, $freez)){
                $this->send(false, null, app::get('b2c')->_('该货品含有冻结库存！库存数不能小于冻结库存'));
                exit;
            }
        }

        if(!$objProduct->update(array('store'=>$num),array('product_id'=>$pid))){
            $this->send(false, null, app::get('b2c')->_('更新失败'));
            exit;
        }
        
        $cols = "sum(store) as gstore";
        $product_list = $objProduct->getList($cols ,array('goods_id'=>$gid,'disabled'=>'false'));

        if(!$objGoods->update(array('store'=>$product_list[0]['gstore']),array('goods_id'=>$gid))){
            $this->send(false, null, app::get('b2c')->_('更新失败'));
            exit;
        }

        $this->send(true, null, app::get('b2c')->_('success'));
    }

    /**
     * price_update 修改宝贝各种价格接口方法
     * @author qianlei
     **/
    public function price_update(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'goods_id'=>'商品ID',
            'product_id'=>'货品ID',
            'updateParams'=>'需要更新的内容'
        );
        $allowParam = array('price','mktprice');
        $this->check_params($must_params);
        $gid = intval($params['goods_id']);
        $pid = intval($params['product_id']);
        $updateParams = $params['updateParams'];
        $objGoods = &app::get('b2c')->model('goods');
        $objProduct = &app::get('b2c')->model('products');
        $gfilter = array(
                'goods_id'=>$gid,
                'store_id'=>$this->store_id,
                'disabled'=>'false'
        );
        $gcount = $objGoods->count($gfilter);
        if($gcount == 0){
            $this->send(false, null, app::get('b2c')->_('无效商品！可能是商品不属于该店铺'));
            exit;
        }

        $pfilter = array(
                'goods_id'=>$gid,
                'product_id'=>$pid ,
                'disabled'=>'false'
        );
        $pcount = $objProduct->count($pfilter);
        if($pcount == 0){
            $this->send(false, null, app::get('b2c')->_('无效商品！可能是商品不属于该店铺'));
            exit;
        }
        $Data = array('last_modify'=>time());

        $updateParams_arr = json_decode($updateParams,true);

        if(!empty($updateParams_arr)){
            foreach($updateParams_arr as $k=>$v){
                if(!in_array($k,$allowParam)){//筛选允许修改的字段
                    unset($updateParams_arr[$k]);
                }
            }
            $Data = array_merge($updateParams_arr,$Data);
        }

        if(!$objProduct->update($Data,$pfilter)){
            $this->send(false, null, app::get('b2c')->_('更新失败'));
            exit;
        }

        $cols = 'max(mktprice) as mktprice,min(price) as price';
        $filter = array('goods_id'=>$gid,'disabled'=>'false');

        $goodsPrice = $objProduct->getList($cols,$filter);

        if(!$objGoods->update($goodsPrice[0],$filter)){
            $this->send(false, null, app::get('b2c')->_('更新失败'));
            exit;
        }else{
            $this->send(true, null, app::get('b2c')->_('success'));
            exit;
        }

    }

    /**
     * marketable_update 宝贝上架、下架操作接口方法
     * @author qianlei
     **/
    public function marketable_update(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'goods_id'=>'商品ID',
            'marketable'=>'上下架',
        );
        $this->check_params($must_params);
        $gids = $params['goods_id'];
        $gid = explode(',',$gids);

        $marketable = $params['marketable'];
        $marketable_arr = array('true','false');
        $objGoods = &app::get('b2c')->model('goods');
        if(!$gid){
            $this->send(false, null, app::get('b2c')->_('无效商品！可能是商品未上架'));
            exit;
        }else{
            $rs = $objGoods->getList('goods_id',array('goods_id'=>$gid,'disabled'=>'false'));
            if(!$rs || empty($rs)){
                $this->send(false, null, app::get('b2c')->_('无效商品！'));
                exit;
            }
        }

        if(!in_array($marketable,$marketable_arr)){
            $this->send(false,null,app::get('b2c')->_('marketable类型无效'));
            exit;
        }

        if(!$objGoods->update(array('marketable'=>$marketable),array('goods_id'=>$gid))){
            $this->send(false, null, app::get('b2c')->_('更新失败'));
        }

        $this->send(true, null, app::get('b2c')->_('success'));
    }

    /**
     * cat_list_get 获取店铺内宝贝分类，带分页接口方法
     * @author qianlei
     **/
    public function cat_list_get(){
        $params = $this->params;

        $page = isset($params['page'])?intval($params['page']):1;
        $num = isset($params['num'])?intval($params['num']):20;
        $parent_id = isset($params['parent_id'])?intval($params['parent_id']):NULL;

        $orderBy = 'p_order ASC';
        $filter = array('disabled'=>'false','store_id'=>$this->store_id);

        if($parent_id !== NULL){
            $filter['parent_id'] = $parent_id;
        }

        $objGcat = app::get('business')->model('goods_cat');
         $cols = "custom_cat_id,store_id,parent_id,cat_path,is_leaf,type_id,cat_name,gallery_setting,disabled,p_order,goods_count,tabs,finder,addon,child_count";
        $Cats = $objGcat->getList($cols, $filter, $page-1, $num,$orderBy );

        $this->send(true, $Cats, app::get('b2c')->_('success'));

    }

    /**
     * cat_detail_get 获取宝贝分类基本信息接口方法
     * @author qianlei
     **/
    public function cat_detail_get(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'custom_cat_id'=>'宝贝分类ID'
        );
        $this->check_params($must_params);
        $custom_cat_id = intval($params['custom_cat_id']);
        $objGcat = app::get('business')->model('goods_cat');
        $filter = array('custom_cat_id'=>$custom_cat_id,'store_id'=>$this->store_id);

        $cols = "custom_cat_id,store_id,parent_id,cat_path,is_leaf,cat_name,disabled,p_order";
        $Data = $objGcat->dump( $filter, $cols );
        $this->send(true, $Data, app::get('b2c')->_('success'));


    }

    /**
     * cat_detail_update 修改宝贝分类基本信息接口方法
     * @author qianlei
     * @updateParams 字段名=值（用，相隔）
     **/
    public function cat_detail_update(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'custom_cat_id'=>'宝贝分类ID',
            'updateParams'=>'需要更新的内容'
        );
        $allowParam = array('parent_id','p_order','cat_name');//允许修改的字段
        $this->check_params($must_params);
        $custom_cat_id = intval($params['custom_cat_id']);
        $Data['custom_cat_id'] = $custom_cat_id;
        $updateParams = $params['updateParams'];

        $updateParams_arr = json_decode($updateParams,true);

        if(!empty($updateParams_arr)){
            foreach($updateParams_arr as $k=>$v){
                if(!in_array($k,$allowParam)){//筛选允许修改的字段
                    unset($updateParams_arr[$k]);
                }
            }
            $Data = array_merge($updateParams_arr,$Data);
        }

        $objGcat = app::get('business')->model('goods_cat');
        if($objGcat->save($Data)){
            $this->send(true, NULL, app::get('b2c')->_('success'));
        }else{
            $this->send(false, NULL, app::get('b2c')->_('更新失败'));
        }

    }

    /*-------------------------------------私有方法--------------------------------------------------*/
    /**
     * goods_info 宝贝列表获取私有方法
     * @author qianlei
     * switch 'onsell'：获取出售中的宝贝
     *        'instock'：获取仓库中的宝贝
     *        'alert'：获取预警中的宝贝
     **/
    private function goods_info($filter, $switch, $orderBy = 0, $page = 1,$pagelimit = 10){
        $objGoods = app::get('business')->model('goods');
        if(empty($filter['name'])) unset($filter['name']);
        if(empty($filter['bn'])) unset($filter['bn']);
        if(empty($filter['cat_id'])) unset($filter['cat_id']);
        if(!empty($filter['price_from'])){
            $filter['price|bthan'] = intval($filter['price_from']);
        }
        unset($filter['price_from']);
        if(!empty($filter['price_to'])){
            $filter['price|lthan'] = intval($filter['price_to']);
        }
        unset($filter['price_to']);
        if(!empty($filter['bcount_from'])){
            $filter['buy_count|bthan'] = intval($filter['bcount_from']);
        }
        unset($filter['bcount_from']);
        if(!empty($filter['bcount_to'])){
            $filter['buy_count|lthan'] = intval($filter['bcount_to']);
        }
        unset($filter['bcount_to']);
        $filter['disabled'] = 'false';
        $filter['goods_type'] = 'normal';
        if(!empty($filter['custom_cat_id'])){
            $objBGoodsCat = &app::get('business')->model('goods_cat_conn');
            $gid_list = array();
            foreach($objBGoodsCat->getList('goods_id', array('cat_id'=>intval($filter['custom_cat_id']))) as $rows){
                $gid_list[] = $rows['goods_id'];
            }
            $filter['goods_id|in'] = $gid_list;
        }
        $return = array();
        $orderBy = $orderBy?$orderBy:'buy_count desc,uptime desc';
        foreach((array)explode(',' , $orderBy) as $item){
            preg_match("/^(\S+)\s+(\S+)/i",$item, $matches);
            $matches[2] = strtolower($matches[2]);
            $matches[2] = ($matches[2] == 'desc' || $matches[2] == 'asc')?$matches[2]:'desc';
            if($matches[1] && $matches[1]=='buy_count'){
                $return['buy_count_orderby'] = $matches[2];
            }else if($matches[1] && $matches[1]=='uptime'){
                $return['uptime_orderby'] = $matches[2];
            }
        }
        switch($switch){
            case 'onsell':
            $filter['marketable'] = 'true';
            break;
            case 'instock':
            $filter['marketable'] = 'false';
            break;
            case 'alert':
            $alert_num = app::get('b2c')->getConf('system.product.alert.num');
            $obj_strman = app::get('business')->model('storemanger');
            $alert_num = $obj_strman->getList('alert_num', array('store_id'=>$this->store_id));
            $alert_num = intval($alert_num[0]['alert_num']);
            $filter['marketable'] = 'true';
            $filter['store|sthan'] = $alert_num;
            break;
        }
        $return['switch'] = $switch;
        //$pageLimit = app::get('b2c')->getConf('gallery.display.listnum');
        //$pageLimit = ($pageLimit ? $pageLimit : 20);
        $aGoods = $objGoods->getList('is_tui,is_new,goods_id,name,bn,store,price,buy_count,uptime,image_default_id,store_id,freight_bear', $filter, $pagelimit*($page-1), $pagelimit, $orderBy);
        $params = $this->params;
        $picSize = in_array(strtolower($params['picSize']), array('cl', 'cs'))?strtolower($params['picSize']):'cl';

        $objImage = &app::get("image")->model("image_attach");
        $goods_ids = array();
        foreach((array)$aGoods as $items){
            $goods_ids[] = $items['goods_id'];
        }
        if(!empty($goods_ids)){
            $goods_store = array();
            $sql = "select goods_id,sum(store) as store,sum(freez) as freez from sdb_b2c_products where goods_id in (".implode(',',$goods_ids).") group by goods_id";
            foreach((array)$objGoods->db->select($sql) as $items){
                $goods_store[$items['goods_id']] = $items;
            }
            //$keywords = array();
            //$sql = "select * from sdb_b2c_goods_keywords where goods_id in (".implode(',',$goods_ids).") and res_type='goods'";
            //foreach((array)$objGoods->db->select($sql) as $items){
                //$keywords[$items['goods_id']][] = $items;
            //}

            foreach((array)$aGoods as $key => $value){
                $aGoods[$key]['store'] = 0;
                $aGoods[$key]['freez'] = 0;
                $aGoods[$key]['keywords'] = array();
                if(isset($goods_store[$value['goods_id']])){
                    $aGoods[$key]['store'] = $goods_store[$value['goods_id']]['store'];
                    $aGoods[$key]['freez'] = $goods_store[$value['goods_id']]['freez'];
                }
                if(isset($keywords[$value['goods_id']])){
                    $aGoods[$key]['keywords'] = $keywords[$value['goods_id']];
                }

                $aGoods[$key]['image_default'] = $this->get_img_url($value['image_default_id'],$picSize);
                unset($aGoods[$key]['image_default_id']);
            }
        }
        $return['goods'] = $aGoods;
        $return['shop'] = $filter['store_id'];

        $total = $objGoods->count($filter);
        $return['paper']['total'] = ceil($total/$pagelimit);
        $return['paper']['current'] = $page;

        return $return;
    }

    /**
     * freight_detial 配送方式获取私有方法
     * @author qianlei
     **/
     private function freight_detial($goods_id, $store_id, $weight, $area_id=0){
        $objGoods = app::get('b2c')->model('goods');
        $aData = array(
            'goods_id' => intval($goods_id),
            'store_id' => intval($store_id),
            'weight' => floatval($weight),
        );
        $aDt = $objGoods->getDlytype($aData, $area_id);
        $aReturn = array();
        $aReturn['area'] = $aDt['area'];
        $aReturn['localname'] = $aDt['localname'];
        $aReturn['parent'] = $aDt['parent'];
        foreach((array)$aDt['dlytype'] as $row){
            $aReturn['dlytype'][] = array(
                'name' => $row['dt_name'],
                'money' => $row['money'],
            );
        }
        return $aReturn;
    }

    /**
     * discuss_detial 商品评论获取私有方法
     * @author qianlei
     **/
    private function discuss_detial($params = array()){
        if(!$params['goods_id']) return array();
        $limit = $params['pageLimit']?intval($params['pageLimit']):10;
        $offset = $params['nPage']?(intval($params['nPage'])-1)*$limit:0;
        $aData = array();
        $siteMember = $this->get_current_member();
        if(!$siteMember['member_id']){
            $aData['login_status'] = 'nologin';
        }
        $objComment = app::get('b2c')->model('member_comments');
        $filter = array('type_id'=>$params['goods_id'],'object_type'=>'discuss','for_comment_id'=>0,'display'=>'true','comments_type'=>'1');
        $aData['total'] = $objComment->count($filter);
        $sql = "  select c.comment_id,c.author,c.time,c.comment,c.addon,c.author_id,m.name as author_alias 
from sdb_b2c_member_comments as c 
left join sdb_b2c_members as m on c.author_id=m.member_id 
left join sdb_b2c_order_items as i on c.order_id=i.order_id and c.type_id=i.goods_id 
where c.object_type='discuss' and c.for_comment_id=0 and c.type_id=".intval($params['goods_id'])." and c.display='true' and c.comments_type='1' 
group by  comment_id,i.goods_id order by time desc limit {$offset},{$limit}";
        $aData['data'] = array();
        $aId = array();
        foreach((array)$objComment->db->select($sql) as $val){
            $val['addon'] = unserialize($val['addon']);
            if(!empty($val['author_alias'])) $val['author'] = $val['author_alias'];
            if(isset($val['addon']['hidden_name']) && $val['addon']['hidden_name'] == 'YES' && ($val['author_id'] !=0 || $val['author_id'] !=1)){
                $val['author'] = mb_substr($val['author'], 0, 1, 'UTF-8').'****'.mb_substr($val['author'], mb_strlen($string, 'UTF-8')-1, 1, 'UTF-8');
            }
            $aId[] = $val['comment_id'];
            unset($val['author_alias'],$val['author_id'],$val['addon']);
            $aData['data'][] = $val;
            
        }
        if(!empty($aId)){
            $addition = array();
            $temp = $objComment->getList('comment_id,for_comment_id',array('for_comment_id' => $aId,'comments_type'=>'3','display' => 'true'));
            foreach((array)$temp as $rows){
                $aId[] = $rows['comment_id'];
                $addition[$rows['for_comment_id']][] = $rows['comment_id'];
            }
            $aReply = (array)kernel::single('business_message_disask')->getCommentsReply($aId, true);
            foreach((array)$aData['data'] as $key => $rows){
                foreach($aReply as $rkey => $rrows){
                    $condition1 = $rows['comment_id'] == $rrows['for_comment_id'];
                    $condition2 = !empty($addition) && isset($addition[$rows['comment_id']]) && in_array($rrows['for_comment_id'],$addition[$rows['comment_id']]);
                    if($condition1 || $condition2){
                        $aData['data'][$key]['items'][] = array(
                            'author' => ($aReply[$rkey]['comments_type']==0&&$aReply[$rkey]['author']!='管理员')?'掌柜':$aReply[$rkey]['author'],
                            'time' => $aReply[$rkey]['time'],
                            'comment' => $aReply[$rkey]['comment'],
                        );
                    }
                }
                unset($aData['data'][$key]['comment_id']);
            }
        }
        return $aData;
    }

    
}