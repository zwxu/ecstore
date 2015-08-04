<?php

class cellphone_base_misc_cart extends cellphone_cellphone
{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;

        header("cache-control: no-store, no-cache, must-revalidate");
        $this->objMath = kernel::single("ectools_math");
        $this->mCart = app::get('b2c')->model('cart');
        $this->mCart->unset_data();
        $this->member_status = kernel::single('b2c_frontpage')->check_login();
    }

    public function enum_type(){
        return array('product','package','adjunct','gift');
    }
    
    public function cart_object(){
        /*$objects = array();
        $objects[] = new cellphone_cart_goods();
        $objects[] = new cellphone_cart_package();
        $objects[] = new cellphone_cart_coupon();
        return $objects;*/
        return kernel::servicelist('cellphone_cart_object');
    }

    // 加入购物车
    public function set_cart(){
        $params = $this->params;
        $must_params = array(
            'session'=>'登陆标识',
            'type'=>'对象标识', // 值可以为 goods, coupon, gift, package, spike, group, score
            'goods_id'=>'捆绑商品标识',
            'products'=>'货品信息', // [{'goods_id':'125','product_id':'959','num':1}]
        );
        $this->check_params($must_params);
        if(!$this->member_status){
            $this->send(false, null, app::get('b2c')->_('加入购物车前请先登录'));exit;
        }
        $params['type'] = $params['type']=='normal'?'goods':$params['type'];
        $type_temp = $params['type'];
        $type = ($type_temp=='spike'||$type_temp=='group'||$type_temp=='score')?'goods':$params['type'];
        $params['num'] = ($params['num']&&intval($params['num']))?intval($params['num']):1;

        foreach( $this->cart_object() as $object ) {
            if( !is_object($object) ) continue;
            if( method_exists($object,'params_to_add') && is_callable(array($object,'params_to_add')) ) {
                $object->params_to_add($params,$data);
            }
        }
        if(isset($params['status']) && $params['status'] == 'fastbuy'){
            $must_params = array(
                //'area_id'=>'收货地址',
            );
            $this->check_params($must_params);
            $data[] = 'fastbuy';
        }
        if($params['type'] == 'coupon'){
            if(isset($params['status']) && $params['status'] == 'fastbuy'){
                $params['products'] = json_decode($params['products'],1);
                if(isset($params['products']['type']))$type_temp = $params['products']['type'];
                $must_params = array(
                    'area_id'=>'收货地址',
                );
            }else{
                $must_params = array(
                    'area_id'=>'收货地址',
                    'md5_cart_info'=>'订单标志',
                );
            }
            $this->check_params($must_params);
        }

        /**
         * 处理信息和验证过程
         */
        $arr_objects = array();
        switch($type_temp){
            case 'spike':
            $objs = kernel::servicelist('spike_cart_object_apps');
            break;
            case 'group':
            $objs = kernel::servicelist('group_cart_object_apps');
            break;
            case 'score':
            $objs = kernel::servicelist('score_cart_object_apps');
            break;
            default:
            $objs = kernel::servicelist('b2c_cart_object_apps');
            break;
        }

        if ($objs)
        {
            foreach ($objs as $obj)
            {
                if ($obj->need_validate_store()){
                    $arr_objects[$obj->get_type()] = $obj;
                }
            }
        }

        /**
         * 处理校验各自的数据是否可以加入购物车
         */
        if (!$arr_objects[$type])
        {
            $this->send(false, null, app::get('b2c')->_('加入购物车类型错误！'));exit;
        }
        if (method_exists($arr_objects[$type], 'get_data')){
            if (!$aData = $arr_objects[$type]->get_data($data,$msg))
            {
                $this->send(false, null, $msg);exit;
            }
        }else $aData = $data;
        // 进行各自的特殊校验
        if (method_exists($arr_objects[$type], 'check_object'))
        {
            if (!$arr_objects[$type]->check_object($aData,$msg))
            {
                $this->send(false, null, $msg);exit;
            }
        }
        $obj_cart_object = kernel::single('b2c_cart_objects');
        if (!$obj_cart_object->check_store($arr_objects[$type], $aData, $msg))
        {
            $this->send(false, null, $msg);exit;
        }

        $arrMember = $this->get_current_member();
        if($params['type'] != 'coupon')
        switch($type_temp){
            case 'spike':
            $applyObj = app::get('spike')->model('spikeapply');
            $aid = $applyObj->getOnActIdByGoodsId($aData['goods']['goods_id']);
            if($aid){
                $obj_business_activity = kernel::single('spike_business_activity');
                if (!$obj_business_activity->checkPersonLimit($aData['goods']['num'],$arrMember['member_id'],$aid,&$msg)){
                    $this->send(false, null, $msg);exit;
                }
            }else{
                $this->send(false, null, '不存在此活动，或活动已经结束');exit;
            }
            break;
            case 'group':
            $applyObj = app::get('groupbuy')->model('groupapply');
            $aid = $applyObj->getOnActIdByGoodsId($aData['goods']['goods_id']);
            if($aid){
                $obj_business_activity = kernel::single('groupbuy_business_activity');
                if (!$obj_business_activity->checkPersonLimit($aData['goods']['num'],$arrMember['member_id'],$aid,&$msg)){
                    $this->send(false, null, $msg);exit;
                }
            }else{
                $this->send(false, null, '不存在此活动，或活动已经结束');exit;
            }
            break;
            case 'score':
            $applyObj = app::get('scorebuy')->model('scoreapply');
            $aid = $applyObj->getOnActIdByGoodsId($aData['goods']['goods_id']);
            if($aid){
                $obj_business_activity = kernel::single('scorebuy_business_activity');
                if (!$obj_business_activity->checkPersonLimit($aData['goods']['num'],$arrMember['member_id'],$aid,&$msg)){
                    $this->send(false, null, $msg);exit;
                }
            }else{
                $this->send(false, null, '不存在此活动，或活动已经结束');exit;
            }

            $obj_isScoreEnough = kernel::single('scorebuy_order_score');
            if($obj_isScoreEnough){
                if(!$obj_isScoreEnough->isScoreEnough($aData['goods']['goods_id'],$aData['goods']['num'],$arrMember['member_id'])){
                    $this->send(false, null, '您的积分不足！');exit;
                }
            }
            break;
        }

		//begin  获取商品是否是限时抢购的商品  限时抢购的商品不能加入购物车
		$checkTimedbuy = kernel::service('timedbuy_check_goods_isTimedbuy');
		if($params['status'] != 'fastbuy' && $checkTimedbuy){
			if(!$checkTimedbuy->check_isTimedbuy($aData,$msg)){
				$this->send(false, null, $msg);exit;
			}
		}
		//end
		//检查卖家是否是店家
		$checkSeller = kernel::service('business_check_goods_isMy');
		if($checkSeller){
			if(!$checkSeller->check_isSeller($msg)){
				$this->send(false, null, $msg);exit;
			}
		}

		//验证是否是自己的商品
		$sign = true;
		$check_objects = kernel::servicelist('business_check_goods_isMy');
		if($aData[0] == 'goods' && $check_objects){
			foreach($check_objects as $check_object){
				$check_object->check_goods_isMy($aData['goods']['goods_id'],$msg,$sign);
			}
			if(!$sign){
				$this->send(false, null, $msg);exit;
			}
		}
		//end by lijun 2013/5/27

        //购物车中是否有虚拟物品
        //echo "<pre>";print_r($this->mCart->get_objects());exit;
        $now_object = $this->mCart->get_objects();
        if($aData[0] == 'goods' && isset($now_object['object']['goods']['0'])){
            $goods_id = $now_object['object']['goods']['0']['obj_items']['products']['0']['goods_id'];
            $sign = true;
            $check_objects = kernel::servicelist('business_check_goods_isMy');
            if($check_objects){
                foreach($check_objects as $check_object){
                    $check_object->check_goods_entity($aData['goods']['goods_id'],$goods_id,$msg,$sign);
                }
                if(!$sign){
                    $this->send(false, null, $msg);exit;
                }
            }
        }
		//end by lufeng 2013/8/16
        /** end **/

        //快速购买
        if(isset($aData[1]) && $aData[1] == 'fastbuy'){
            switch($type_temp){
                case 'spike':
                $oCartGoods=kernel::single('spike_cart_object_goods');
                $oCartCoupon=kernel::single('spike_cart_object_coupon');
                $isfast = 2;
                break;
                case 'group':
                $oCartGoods=kernel::single('groupbuy_cart_object_goods');
                $oCartCoupon=kernel::single('groupbuy_cart_object_coupon');
                $isfast = 3;
                break;
                case 'score':
                $oCartGoods=kernel::single('scorebuy_cart_object_goods');
                $oCartCoupon=kernel::single('scorebuy_cart_object_coupon');
                $isfast = 4;
                break;
                default:
                $oCartGoods=kernel::single('fastbuy_cart_object_goods');
                $oCartCoupon=kernel::single('fastbuy_cart_object_coupon');
                $isfast = 1;
                break;
            }
            $oCartGoods = $type=='goods'?$oCartGoods:$oCartCoupon;
            if(!$obj_ident = $oCartGoods->add_object($aData,$msg)){
                $this->send(false, null, $msg);exit;
            }else{
                $_SESSION['S[Cart_Fastbuy]'][$type]=$aData;
            }
            $picSize = in_array(strtolower($params['picSize']), array('cl', 'cs'))?strtolower($params['picSize']):'cl';
            if($type == 'coupon'){
                $store_id = $aData['store_id'];
                $this->update_total($store_id);
            }
            $this->checkout($isfast);exit;
        }
        if($type == 'coupon' && $params['md5_cart_info'] != kernel::single("b2c_cart_objects")->md5_cart_objects()){
            $this->send(false, null, app::get('b2c')->_('购物车内容发生变化，请重新结算！'));exit;
        }
        if(!$obj_ident = $obj_cart_object->add_object($arr_objects[$type], $aData, $msg)){
            $this->send(false, null, $msg);exit;
        }
        if($type=='package' && is_string($obj_ident)){
            $this->send(false, null, $obj_ident);exit;
        }
        if($type == 'coupon'){
            $store_id = $aData['store_id'];
            $this->update_total($store_id);
        }
        $this->send(true, null, '加入购物车成功');exit;
    }

    public function update_item(){
        $params = $this->params;
        $must_params = array(
            'session'=>'登陆标识',
            'type'=>'对象标识', // 值可以为 goods, coupon, gift, package, adjunct, all
            'ident_id'=>'购物车标识',
            'object'=>'货品标识', // array(array('group_id','product_id','quantity'))
        );
        $this->check_params($must_params);
        $params['type'] = $params['type']=='normal'?'goods':$params['type'];
        $params['object'] = json_decode($params['object'],1);
        $mCartObject = app::get('b2c')->model('cart_objects');
        $aCart = $this->mCart->get_basic_objects();
        foreach($aCart as $row){
            if($row['obj_ident'] == $params['ident_id']){
                $update_row = $row;
                break;
            }
        }
        $arr_object = array();
        if($params['type'] == 'gift' || $params['type'] == 'adjunct'){
            $params['object'] = is_array($params['object'])?$params['object']:array($params['object']);
            $arr_quantity = array();
            foreach($params['object'] as $row){
                if(intval($row['quantity'])>0){
                    $arr_quantity[$row['group_id']][$row['product_id']] = array(
                        'quantity'=>$row['quantity']+(int)$arr_quantity[$row['group_id']][$row['product_id']]['quantity'],
                    );
                }else{
                    $this->send(false, null, '请输入正整数！');exit;
                }
            }
            $arr_object[$params['type']] = $arr_quantity;
            $params['type'] = 'goods';
        }else{
            if(intval($params['object']['quantity'])>0){
                $arr_object['quantity'] = (float)$params['object']['quantity'];
            }else{
                $this->send(false, null, '请输入正整数！');exit;
            }
        }

        if(isset($update_row['params']['adjunct']) && is_array($update_row['params']['adjunct'])){
            foreach($update_row['params']['adjunct'] as $adjunct){
                if(!isset($adjunct['adjunct']) || !is_array($adjunct['adjunct'])) continue;
                foreach($adjunct['adjunct'] as $p_id => $p_quantity){
                    if(!isset($arr_object['adjunct'][$adjunct['group_id']][$p_id]))continue;
                    $this->update_obj_ident['index'] = array('adjunct');
                    $this->update_obj_ident['id'] = $p_id;
                    #break 2;
                }
            }
        }

        $_flag = $mCartObject->update_object($params['type'],$params['ident_id'],$arr_object);
        if(is_array($_flag) && isset($_flag['status']) && isset($_flag['msg'])){
            if($_flag['status']){
                $this->ajax_update = true;
                $this->update_obj_ident['ident'] = $params['ident_id']; //值不同。修改 失败直接推出循环
                $msg = $_flag['msg'];
            }else{
                $this->send(false, null, $_flag['msg']);exit;
            }
        }elseif(!$_flag){
            $this->send(false, array('allowednum'=>$update_row['quantity']), app::get('b2c')->_('更新数量失败！库存不足或超出您可以购买的数量'));exit;
        }else{
            $this->ajax_update = true;
            $this->update_obj_ident['ident'] = $params['ident_id']; //值不同。修改 失败直接推出循环
            $msg = app::get('b2c')->_('购物车修改成功！');
        }
        $picSize = in_array(strtolower($params['picSize']), array('cl', 'cs'))?strtolower($params['picSize']):'cl';
        $aData = $this->_common(1,$picSize);

        $aData['subtotal_prefilter'] = $this->objMath->number_minus(array($aData['subtotal'], $aData['discount_amount_prefilter']));
        $aData['promotion_subtotal'] = $this->objMath->number_minus(array($aData['subtotal'], $aData['subtotal_discount']));
        $this->send(true, array(
            'subtotal_prefilter'=>$aData['subtotal_prefilter'],
            'promotion_subtotal'=>$aData['promotion_subtotal'],
            'subtotal_consume_score'=>$aData['subtotal_consume_score'],
            'subtotal_gain_score'=>$aData['subtotal_gain_score'],
            'update_obj'=>$aData['update_obj'],
        ), $msg);exit;
    }

    public function delete_item(){
        $params = $this->params;
        $must_params = array(
            'session'=>'登陆标识',
            'type'=>'对象标识', // 值可以为 goods, coupon, gift, package, adjunct, all
            'ident_id'=>'购物车标识',
            'object'=>'货品标识', // array(array('group_id','product_id','quantity'))
        );
        $this->check_params($must_params);
        $params['type'] = $params['type']=='normal'?'goods':$params['type'];
        $mCartObject = app::get('b2c')->model('cart_objects');
        $params['object'] = json_decode($params['object'],1);
        if($params['type'] == 'coupon'){
            if(isset($params['status']) && $params['status'] == 'fastbuy'){
                $_SESSION['S[Cart_Fastbuy]']['coupon']=array();
            }else{
                $mCartObject->delete_store_coupon((int)$params['object']['store_id']);
            }
            $this->update_total((int)$params['object']['store_id'],1);
        }elseif($params['type'] == 'all' || empty($params['ident_id'])){
            if(!$mCartObject->remove_object('', null, $msg)){
                $this->send(false, null, $msg);exit;
            }
        }elseif($params['type'] == 'gift' || $params['type'] == 'adjunct'){
            $params['object'] = is_array($params['object'])?$params['object']:array($params['object']);
            $arr_quantity = array();
            foreach($params['object'] as $row){
                $arr_quantity[$row['group_id']][$row['product_id']] = array(
                    'quantity'=>$row['quantity']+(int)$arr_quantity[$row['group_id']][$row['product_id']]['quantity'],
                );
            }

            if(!$mCartObject->remove_object_part($params['type'], $params['ident_id'], $arr_quantity, $msg)){
                $this->send(false, null, $msg);exit;
            }
        }else{
            if(!$mCartObject->remove_object($params['type'], $params['ident_id'], $msg)){
                $this->send(false, null, $msg);exit;
            }
        }
        $picSize = in_array(strtolower($params['picSize']), array('cl', 'cs'))?strtolower($params['picSize']):'cl';
        $aData = $this->_common(1,$picSize);

        $aData['subtotal_prefilter'] = $this->objMath->number_minus(array($aData['subtotal'], $aData['discount_amount_prefilter']));
        $aData['promotion_subtotal'] = $this->objMath->number_minus(array($aData['subtotal'], $aData['subtotal_discount']));

        $this->send(true, array(
            'subtotal_prefilter'=>$aData['subtotal_prefilter'],
            'promotion_subtotal'=>$aData['promotion_subtotal'],
            'subtotal_consume_score'=>$aData['subtotal_consume_score'],
            'subtotal_gain_score'=>$aData['subtotal_gain_score'],
        ), '购物车列表页项目删除成功');exit;
    }

    public function get_cart(){
        $params = $this->params;
        $picSize = in_array(strtolower($params['picSize']), array('cl', 'cs'))?strtolower($params['picSize']):'cl';
        //检查买家是否是店家
        $checkSeller = kernel::service('business_check_goods_isMy');
        if($checkSeller){
            if(!$checkSeller->check_isSeller($msg)){
                $this->send(false, null, app::get('b2c')->_('店家不能购买商品，请更换账号！'));exit;
            }
        }
        $aInterim = $this->_common(1,$picSize);
        $aData = array();
        if(isset($aInterim['enable_object']) && !empty($aInterim['enable_object'])){
            foreach($aInterim['enable_object'] as $row){
                $store_id = (int)$row['storeinfo']['store_id'];
                $aData['enable_object'][$store_id]['store_id'] = $store_id;
                $aData['enable_object'][$store_id]['store_name'] = $row['storeinfo']['store_name'];
                unset($row['storeinfo']);
                $aData['enable_object'][$store_id]['items'][] = $row;
            }
            $aData['enable_object'] = array_values($aData['enable_object']);
        }

        if(isset($aInterim['disable_object']) && !empty($aInterim['disable_object'])){
            $disable_item = array();
            foreach($this->cart_object() as $object){
                if(!is_object($object)) continue;
                if(method_exists($object,'get_item') && is_callable(array($object,'get_item'))) {
                   $disable_item = array_merge($disable_item, $object->get_item($aInterim['disable_object'],$picSize,$disable_totle));
                }
            }
            $aData['disable_object'] = array_values($disable_item);
            $aData['disable_subtotal'] = $disable_totle;
        }
        $aData['subtotal_prefilter'] = $this->objMath->number_minus(array($aInterim['subtotal'], $aInterim['discount_amount_prefilter']));
        $aData['promotion_subtotal'] = $this->objMath->number_minus(array($aInterim['subtotal'], $aInterim['subtotal_discount']));

        $this->send(true, $aData, '获取购物车列表页成功');exit;
    }

    public function _common($flag=0,$picSize,$isfast=0){
        if(!$isfast){
            $old_item = array();
            foreach($this->cart_object() as $object){
                if(!is_object($object)) continue;
                if(method_exists($object,'get_old_item') && is_callable(array($object,'get_old_item'))) {
                    $old_item = array_merge($old_item, $object->get_old_item());
                }
            }
            // 购物车数据信息
            $aCart = $this->mCart->get_objects();
        }elseif($isfast==2){
            kernel::single('spike_cart_fastbuy_goods')->get_fastbuy_arr(
                $_SESSION['S[Cart_Fastbuy]']['goods'],
                $_SESSION['S[Cart_Fastbuy]']['coupon'],
                $aCart
            );
        }elseif($isfast==3){
            kernel::single('groupbuy_cart_fastbuy_goods')->get_fastbuy_arr(
                $_SESSION['S[Cart_Fastbuy]']['goods'],
                $_SESSION['S[Cart_Fastbuy]']['coupon'],
                $aCart
            );
        }elseif($isfast==4){
            kernel::single('scorebuy_cart_fastbuy_goods')->get_fastbuy_arr(
                $_SESSION['S[Cart_Fastbuy]']['goods'],
                $_SESSION['S[Cart_Fastbuy]']['coupon'],
                $aCart
            );
        }else{
            kernel::single('fastbuy_cart_fastbuy_goods')->get_fastbuy_arr(
                $_SESSION['S[Cart_Fastbuy]']['goods'],
                $_SESSION['S[Cart_Fastbuy]']['coupon'],
                $aCart
            );
        }
        $this->_item_to_disabled( $aCart,$flag ); //处理购物扯删除项
        $this->store_total($aCart);
        $this->store_coupons($aCart);

        $cur = app::get('ectools')->model('currency');
        $cur_info = $cur->getFormat();
        $aData = array();
        if($this->ajax_update === true){
            foreach(kernel::servicelist('b2c_cart_object_apps') as $object) {
                if( !is_object($object) ) continue;
                //应该判断是否实现了接口
                if( !method_exists( $object,'get_update_num' ) ) continue;
                if( !method_exists( $object,'get_type' ) ) continue;
                $aTemp = $object->get_update_num( $aCart['object'][$object->get_type()],$this->update_obj_ident);

                if($aTemp){
                    $aTemp['buy_price'] = $cur_info['sign']?str_replace($cur_info['sign'], '', $aTemp['buy_price']):$aTemp['buy_price'];
                    $aData['update_obj'] = $this->update_obj_ident;
                    $aData['update_obj']['subtotal'] = $aTemp['buy_price'];
                    $aData['update_obj']['consume_score'] = $aTemp['consume_score'];
                    break;
                }
            }
        }

        // 购物车是否为空
        if($this->mCart->is_empty($aCart)){
            $this->send(true, null, app::get('b2c')->_('购物车为空'));exit;
        }

        $real_item = array();
        foreach($this->cart_object() as $object){
            if(!is_object($object)) continue;
            if(method_exists($object,'get_cart_item') && is_callable(array($object,'get_cart_item'))) {
               $real_item = array_merge($real_item, $object->get_cart_item($aCart['object']));
            }
        }
        
        $aData['enable_object'] = array();
        $aData['disable_object'] = array();
        if(!$isfast){
            foreach($old_item as $key => $value){
                if(array_key_exists($key, $real_item)){
                    $aData['enable_object'][$key] = $real_item[$key];
                }else{
                    $aData['disable_object'][] = $value;
                }
            }
        }else{
            $aData['enable_object'] = $real_item;
        }

        $aData['subtotal_consume_score'] = $aCart['subtotal_consume_score']; // 消费的总积分（如：用积分换优惠劵) int
        $aData['subtotal_gain_score'] = $aCart['subtotal_gain_score']; // 获取到的总积分（如：购买某件商品送积分) int
        $aData['subtotal'] = (string)$aCart['subtotal']; // 会员的商品总价格 float
        $aData['subtotal_price'] = $aCart['subtotal_price']; // 原始的商品总价格 float
        $aData['subtotal_discount'] = $aCart['subtotal_discount']; // 总优惠金额 float
        $aData['items_quantity'] = $aCart['items_quantity']; // 购物车中总购买单元数 int
        $aData['items_count'] = $aCart['items_count']; // 统计购物车中的购买项（即有多少种）int
        $aData['subtotal_weight'] = $aCart['subtotal_weight']; // 商品总重量 int
        $aData['discount_amount_prefilter'] = $aCart['discount_amount_prefilter']; // 商品预过滤优惠（商品促销优惠金额） int
        $aData['discount_amount_order'] = $aCart['discount_amount_order']; // 订单促销的优惠金额 float
        $aData['discount_amount'] = $aCart['discount_amount']; // 优惠总额 float
        $aData['subtotal_prefilter_after'] = $aCart['subtotal_prefilter_after']; // 商品促销之后的商品总价 float
        $aData['items_quantity_widgets'] = $aCart['items_quantity_widgets']; // 购物车中总共有多少件商品 int
        $aData['items_count_widgets'] = $aCart['items_count_widgets']; // 购物车中总共有多少种商品（赠品除外） int
        $aData['subtotal_price_store'] = $aCart['subtotal_price_store']; //分店铺含运费商品总金额
        $aData['store_count'] = $aCart['store_count']; // 优惠劵优惠金额
        $aData['coupon'] = $aCart['coupon']; // 优惠劵列表

        $aData['subtotal_weight_store'] = $aCart['subtotal_weight_store'];
        $aData['store_shipping_money'] = $aCart['store_shipping_money'];
        $aData['subtotal_price_store'] = $aCart['subtotal_price_store'];
        $aData['subtotal_store_good_price'] = $aCart['subtotal_store_good_price'];
        $aData['subtotal_goods'] = $aCart['subtotal_goods'];
        $aData['subtotal_goods_price'] = $aCart['subtotal_goods_price'];
        $aData['store_free_shipping'] = $aCart['store_free_shipping'];
        $aData['is_free_shipping'] = $aCart['is_free_shipping'];

        //未享受的订单规则
        $aData['unuse_rule'] = $this->mCart->get_unuse_solution_cart($aCart);
        if(!$this->member_status ){
            $aData['login'] = 'nologin';
        }

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
        $aData['money_format'] = $ret;

        return $aData;
    }

    private function _item_to_disabled( &$aCart,$flag ) {

        foreach( kernel::servicelist('b2c_cart_object_apps') as $object ) {
            if( !is_object($object) ) continue;
            $o[$object->get_type()] = $object;
        }

        $arr_cart_disabled_session = $_SESSION['cart_objects_disabled_item'];
        foreach( (array)$aCart['object'] as $_obj_type => $_arr_by_obj_type ) {
            $tmp = $arr_cart_disabled_session[$_obj_type];
            if( isset($arr_cart_disabled_session[$_obj_type]) ) {
                if( !$o[$_obj_type] ) continue;
                if( !method_exists( $o[$_obj_type],'apply_to_disabled' ) ) continue;
                $aCart['object'][$_obj_type] = $o[$_obj_type]->apply_to_disabled( $_arr_by_obj_type, $tmp, $flag );
                $_SESSION['cart_objects_disabled_item'][$_obj_type] = $tmp;
            } else {
                if( $flag )
                    unset($_SESSION['cart_objects_disabled_item'][$obj_type]);
            }
        }
    }

    /*
    *  购物车每个店铺的商品总金额
    */
    public function store_total(&$aCart){
        foreach((array)$aCart['object']['goods'] as $key=>$value){
            $adjunctTatole = 0;
            if($value['adjunct']){
                foreach($value['adjunct'] as $adv){
                    $adjunctTatole += $adv['subtotal'];
                }
            }
            $total[$value['store_id']][$value['obj_ident']] = $value['subtotal_prefilter_after']+$adjunctTatole;
            if($value['freight_bear']=='business'){
                $store_free_shipping[$value['store_id']][] = 0;
                $total_weight[$value['store_id']][] = '0';
            }else{
                $store_free_shipping[$value['store_id']][] = 1;
                $total_weight[$value['store_id']][] = $value['subtotal_weight'];
            }
        }
        foreach((array)$aCart['object']['package'] as $key=>$value){
            $total[$value['store_id']][$value['obj_ident']] = $value['subtotal_price'];
            $store_free_shipping[$value['store_id']][] = 1;
            $total_weight[$value['store_id']][] = $value['subtotal_weight'];
        }
        $arrMember = $this->get_current_member();
        if($total){
            foreach($total as $key=>$value){
                $total[$key] = array_sum($value);
                $total_weight[$key] = array_sum($total_weight[$key]);
                $store_free_shipping[$key] = array_sum($store_free_shipping[$key]);

                $shipping[$key] = app::get('b2c')->controller('site_cart')->get_store_shipping($key,$total_weight,$total,$store_free_shipping,$aCart);
                $shipmoney[$key] = $shipping[$key][0]['money'];
                $subtotal[$key] = $total[$key] + $shipping[$key][0]['money'];
            }
        }
        $aCart['subtotal_weight_store'] = $total_weight;
        $aCart['store_shipping_money'] = $shipmoney;
        $aCart['subtotal_price_store'] = $subtotal;
        $aCart['subtotal_store_good_price'] = $total;
        $aCart['subtotal_goods'] = $aCart['subtotal'];
        $aCart['subtotal_goods_price'] = $aCart['subtotal_price'];
        $aCart['store_free_shipping'] = $store_free_shipping;
    }

    public function store_coupons(&$aCart){
        if($aCart['promotion_solution']){
            foreach($aCart['promotion_solution'] as $value){
                foreach($value['order'] as $k=>$v){
                    if($v['store_id']){
                        $discount[$v['store_id']] = $v['amount'];
                    }
                }
            }
            foreach($aCart['object']['coupon'] as $value){
                $coupons[$value['store_id']] = $value;
            }
            $aCart['store_count'] = $discount;
            $aCart['coupon'] = $coupons;
        }
    }

    function checkout($isfast=0){
        $params = $this->params;
        $must_params = array(
            'session'=>'登陆标识',
            //'area_id'=>'收货地址',
        );
        $this->check_params($must_params);
        $isfast = (isset($params['status'])&&$params['status'])?
        ((isset($params['type'])&&$params['type']=='spike')?2:
        ((isset($params['type'])&&$params['type']=='group')?3:
        ((isset($params['type'])&&$params['type']=='score')?4:1))):$isfast;

        $picSize = in_array(strtolower($params['picSize']), array('cl', 'cs'))?strtolower($params['picSize']):'cl';

        if(!$this->member_status){
            $this->send(false, null, app::get('b2c')->_('加入购物车前请先登录'));exit;
        }
        // 检查买家是否是店家
        $checkSeller = kernel::service('business_check_goods_isMy');
        if($checkSeller){
            if(!$checkSeller->check_isSeller($msg)){
                $this->send(false, null, app::get('b2c')->_('店家不能购买商品，请更换账号！'));exit;
            }
        }

        // 判断顾客登录方式.
        $login_type = app::get('b2c')->getConf('site.login_type');
        $is_member_buy = app::get('b2c')->getConf('security.guest.enabled');
        $arrMember = $this->get_current_member();

        //todo暂时修改为不管是跳转登录还是弹出框登录都统一为跳转到登陆页@lujy
        //if(!$arrMember['member_id'] && (($_COOKIE['S']['ST_ShopEx-Anonymity-Buy'] != 'true') || $is_member_buy != 'true'))$this->loginBuy(1);
        // 初始化购物车数据
        $aCart = $this->_common(0,$picSize,$isfast);

        $check_objects = kernel::servicelist('business_check_goods_isMy');
        $sign = true;
        foreach($aCart['enable_object'] as $item){
            if($item['product'][0]['object_type'] == 'goods'){
                foreach((array)$check_objects as $check_object){
                    $check_object->check_goods_isMy($item['product'][0]['goods_id'],$msg,$sign);
                }
                if(!$sign){
                    $this->send(false, null, app::get('b2c')->_('购物车中有自己的商品'));exit;
                }
            }
        }
        $this->checkout_result($aCart, $params['area_id']);
        $aData = array();
        $aData['md5_cart_info'] = $aCart['md5_cart_info'];
        $aData['delivery'] = !empty($aCart['delivery'])?$aCart['delivery']:null;
        $aCart['point_dis']['max_discount_value'] = (float)$aCart['point_dis']['max_discount_value'];
        $aCart['point_dis']['discount_rate'] = (float)$aCart['point_dis']['discount_rate'];
        $min_score = $aCart['point_dis']['discount_rate']==0?0:$aCart['point_dis']['max_discount_value']/$aCart['point_dis']['discount_rate'];
        $aData['order_detail'] = array(
            'cost_item' => $aCart['order_detail']['cost_item'], //商品金额
            'total_amount' => $aCart['order_detail']['total_amount'], //实付金额
            'totalConsumeScore' => $aCart['order_detail']['totalConsumeScore'], //消费积分数
            'totalGainScore' => $aCart['order_detail']['totalGainScore'], //可获得积分数
            'discount_rate' => $aCart['point_dis']['discount_rate'], //积分金额转换率
            'real_usage_point' => min((int)$min_score,(int)$aCart['point_dis']['real_usage_point']), //当前可用积分
            'max_discount_value' => $aCart['point_dis']['max_discount_value'], //最大抵现金额
            'trigger_tax' => $aCart['trigger_tax'], //是否开启发票
            'tax_ratio' => $aCart['tax_ratio'], //发票税率
            'no_shipping' => $aCart['order_detail']['no_shipping'],
        );
        $aData['money_format'] = $aCart['money_format'];
        $aData['money_format']['cur_code'] = $aCart['order_detail']['cur_code'];
        $aData['order_items'] = array();
        foreach($aCart['order_split'] as $store_id => $items){
            $aData['order_items'][$store_id]['store_id'] = $items['info']['store_id'];
            $aData['order_items'][$store_id]['store_name'] = $items['info']['store_name'];
            $aData['order_items'][$store_id]['store_count'] = $aCart['store_count'][$store_id]; //优惠劵
            $totle = $this->objMath->number_minus(array($aCart['subtotal_store_good_price'][$store_id],$aCart['store_count'][$store_id]));
            $totle = ($totle>0?$totle:0);
            $aData['order_items'][$store_id]['store_slip_subtotal'] = $totle; //店铺各运费模板合计（含运费）
            $aData['order_items'][$store_id]['goods_count'] = 0; //店铺商品数量
            $aData['order_items'][$store_id]['goods_preferential'] = 0.0; //店铺优惠金额
            $aData['order_items'][$store_id]['coupon'] = $aCart['coupon'][$store_id]['coupon'];
            $aData['order_items'][$store_id]['slips'] = array();
            foreach((array)$items['slips'] as $slip_id => $rows){
                $aData['order_items'][$store_id]['slips'][$slip_id]['slip_id'] = $slip_id;
                $aData['order_items'][$store_id]['slips'][$slip_id]['shipping'] = array_values($rows['shipping']); //店铺所有运费模板
                foreach((array)$rows['object'] as $value){
                    $temp = array();
                    foreach((array)$this->enum_type() as $type){
                        if(isset($aCart['enable_object'][$value][$type])){
                            $temp[$type] = $aCart['enable_object'][$value][$type];
                            if($type=='product' && $aCart['enable_object'][$value]['product'][0]['object_type']!='normal'){
                            }else{
                                $aData['order_items'][$store_id]['goods_count'] += count($aCart['enable_object'][$value][$type]);
                            }
                        }
                    }
                    $aData['order_items'][$store_id]['slips'][$slip_id]['items'][] = $temp;
                    if($aCart['enable_object'][$value]['storeinfo']['store_id'] == $store_id)
                    $aData['order_items'][$store_id]['goods_preferential'] += $aCart['enable_object'][$value]['subtotal_price'];
                }
            }
            $aData['order_items'][$store_id]['slips'] = array_values($aData['order_items'][$store_id]['slips']);
            $aData['order_items'][$store_id]['goods_preferential'] = $this->objMath->number_minus(array($aData['order_items'][$store_id]['goods_preferential'],$totle));
            $aData['order_items'][$store_id]['goods_preferential'] = $aData['order_items'][$store_id]['goods_preferential']>0?$aData['order_items'][$store_id]['goods_preferential']:0.00;
        }
        $aData['order_items'] = array_values($aData['order_items']);

        $this->send(true, $aData, '获取确认订单列表页成功');exit;
    }

    public function checkout_result(&$aCart, $area_id=null){
        $aCart['checkout'] = 1;
        $aCart['md5_cart_info'] = kernel::single("b2c_cart_objects")->md5_cart_objects();
        if(!$this->member_status){
            $this->send(false, null, app::get('b2c')->_('加入购物车前请先登录'));exit;
        }
        $arrMember = $this->get_current_member();
        // 收货地址
        $obj_member_addrs = app::get('b2c')->model('member_addrs');
        if($area_id){
            $address = $obj_member_addrs->getRow('*',array('member_id'=>$arrMember['member_id'],'addr_id'=>"{$area_id}"));
        }
        if(empty($address)){
            $order_info = app::get('b2c')->model('orders')->getList('ship_area',array('member_id'=>$arrMember['member_id']),0,1,'createtime desc');
            $order_info = $order_info[0]['ship_area'];
            $area = explode(':',$order_info);
            if(!empty($area) && isset($area[2])){
                $address = $obj_member_addrs->getRow('*',array('member_id'=>$arrMember['member_id'],'area|foot'=>":{$area[2]}"));
            }
            if(empty($address)){
                $address = $obj_member_addrs->getRow('*',array('member_id'=>$arrMember['member_id'],'def_addr'=>1));
            }
            if(empty($address)){
                $address = $obj_member_addrs->getRow('*',array('member_id'=>$arrMember['member_id']));
            }
        }
        $aCart['delivery'] = array();
        if(!empty($address)){
            $area = explode(':',$address['area']);
            $aCart['delivery'] = array(
                'addr_id'=>$address['addr_id'],
                'ship_area'=>$address['area'],
                'ship_addr'=>$address['addr'],
                'ship_zip'=>$address['zip'],
                'ship_name'=>$address['name'],
                'ship_mobile'=>$address['mobile'],
                'area_id'=>$area[2],
                'member_id'=>$address['member_id'],
            );
            $area_id = $area[2];
            $area = explode('/',$area[1]);
            if(in_array($area[0],array('北京','天津','上海','重庆'))){
                $area[0] = '';
            }
            $aCart['delivery']['ship_addr_area'] = implode('',$area);
        }else $area_id=null;

        $currency = app::get('ectools')->model('currency');
        $str_def_currency = $arrMember['member_cur'] ? $arrMember['member_cur'] : "";
        if(!$str_def_currency){
            $arrDefCurrency = $currency->getDefault();
            $str_def_currency = $arrDefCurrency['cur_code'];
        }else{
            $arrDefCurrency = $currency->getcur($str_def_currency);
        }
        $aCur = $currency->getcur($str_def_currency);

        // 得到税金的信息
        $aCart['trigger_tax'] = app::get('b2c')->getConf("site.trigger_tax");
        $aCart['tax_ratio'] = app::get('b2c')->getConf("site.tax_ratio");

        $total_item = $this->objMath->number_minus(array($aCart["subtotal"], $aCart['discount_amount_prefilter']));
        $policy_method = app::get('b2c')->getConf("site.get_policy.method");

        switch($policy_method){
            case '1':
                $subtotal_consume_score = 0;
                $subtotal_gain_score = 0;
                $totalScore = 0;
                break;
            case '2':
                $subtotal_consume_score = round($aCart['subtotal_consume_score']);
                $policy_rate = app::get('b2c')->getConf('site.get_rate.method');
                $subtotal_gain_score = round($this->objMath->number_plus(array(0, $aCart['subtotal_gain_score'])));
                $totalScore = round($this->objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));
                break;
            case '3':
                $subtotal_consume_score = round($aCart['subtotal_consume_score']);
                $subtotal_gain_score = round($aCart['subtotal_gain_score']);
                $totalScore = round($this->objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));
                break;
            default:
                $subtotal_consume_score = 0;
                $subtotal_gain_score = 0;
                $totalScore = 0;
                break;
        }

        $total_amount = $this->objMath->number_minus(array($aCart["subtotal"], $aCart['discount_amount']));
        $aCart['subtotal_store_old_price'] = $aCart['subtotal_store_good_price'];
        //订单分单
        $aCart['order_split'] = $split_order=kernel::single('cellphone_cart_split')->split_order($area_id,$aCart);
        ksort($split_order);
        $no_shipping = false;
        foreach($split_order as $store_id=>$sgoods){
            foreach($sgoods['slips'] as $order_sp=>$order){
                if(empty($order['shipping'])){
                    $no_shipping = true;
                }
                foreach($order['shipping'] as $dkey=>$ship){
                    if($ship['default_type']=='true'){
                      //由于前台显示已经减去了抵扣。所以此处仍然要加上运费。
                      $aCart['subtotal_store_good_price'][$store_id]+=$ship['money'];

                      $total_amount+=$ship['money'];

                      if($aCart['is_free_shipping'][$store_id]==1){//优惠券免运费
                           $aCart['store_count'][$store_id]+=$ship['money'];
                           $total_amount-=$ship['money'];
                      }
                      if($total_amount<=0){
                          $total_amount=0;
                      }
                    }
                }
            }
        }
        if($total_amount < 0)$total_amount = 0;
        // 是否可以用积分抵扣
        $obj_point_dis = kernel::service('b2c_cart_point_discount');
        if ($obj_point_dis){
            $aCart['point_dis'] = $this->point_dis($total_amount);
        }
        // 得到cart total支付的信息
        $aCart['order_detail'] = array(
            'cost_item' => $total_item,
            'total_amount' => $total_amount,
            'currency' => $this->app->getConf('site.currency.defalt_currency'),
            'pmt_order' => $aCart['discount_amount_order'],
            'pmt_amount' => $aCart['discount_amount'],
            'totalConsumeScore' => $subtotal_consume_score,
            'totalGainScore' => $subtotal_gain_score,
            'totalScore' => $totalScore,
            'cur_code' => $strDefCurrency,
            'cur_display' => $strDefCurrency,
            'cur_rate' => $aCur['cur_rate'],
            'cur_code' => $aCur['cur_code'],
            'final_amount' => $currency->changer($total_amount, $this->app->getConf("site.currency.defalt_currency"), true),
            'no_shipping' => $no_shipping,
        );

        if($arrMember['member_id']){
            $aCart['order_detail']['totalScore'] = $member_point;
        }else{
            $aCart['order_detail']['totalScore'] = 0;
            $aCart['order_detail']['totalGainScore'] = 0;    //如果是非会员购买获得积分为0，@lujy
        }

        $odr_decimals = app::get('b2c')->getConf('system.money.decimals');
        $total_amount = $this->objMath->get($aCart['order_detail']['total_amount'], $odr_decimals);
        $aCart['order_detail']['discount'] = $this->objMath->number_minus(array($aCart['order_detail']['total_amount'], $total_amount));
        $aCart['order_detail']['total_amount'] = $total_amount;
        $aCart['order_detail']['current_currency'] = $strDefCurrency;
    }

    function point_dis($total_amount){
        $aData = array();
        $arrMember = $this->get_current_member();
        if(!$this->member_status)return $aData;
        $site_get_policy_method = app::get('b2c')->getConf('site.get_policy.method');
        $site_point_usage = app::get('b2c')->getConf('site.point_usage');
        if($site_get_policy_method != '1' && $site_point_usage == '2'){
            $site_point_deductible_value = app::get('b2c')->getConf('site.point_deductible_value');
            $obj_extend_point = kernel::service('b2c.member_extend_point_info');
            if($obj_extend_point){
                // 当前会员实际可以使用的积分
                $obj_extend_point->get_usage_point($arrMember['member_id'], $real_usage_point);
            }
            $aData['discount_rate'] = $site_point_deductible_value;
            if ($real_usage_point < 0)
                $real_usage_point = 0;
            $aData['real_usage_point'] = $real_usage_point;
            $site_point_max_deductible_method = app::get('b2c')->getConf('site.point_max_deductible_method');
            $site_point_max_deductible_value = app::get('b2c')->getConf('site.point_max_deductible_value');
            $objMath = kernel::single("ectools_math");
            if ($site_point_max_deductible_method == '1')
            {
                $aData['max_discount_value'] = $site_point_max_deductible_value;
            }
            else
            {
                $aData['max_discount_value'] = $objMath->number_multiple($total_amount, $site_point_max_deductible_value);
            }
            if ($aData['max_discount_value'] < 0)
                $aData['max_discount_value'] = 0;
        }
        return $aData;
    }

    // 取到店铺优惠券的信息
    function get_coupon(){
        $params = $this->params;
        $must_params = array(
            'session'=>'登陆标识',
            'store_id'=>'店铺标志',
        );
        $this->check_params($must_params);

        $couInfo = app::get('b2c')->model('coupons')->getList('cpns_id',array('store_id'=>",{$params['store_id']},"));
        $cId = array();
        foreach($couInfo as $k=>$v){
            $cId[] = $v['cpns_id'];
        }
        $arrMember = $this->get_current_member();
        $aData = array();
        if($arrMember['member_id']){
            $oCoupon = app::get('b2c')->model('member_coupon');
            $obj_store=&app::get('business')->model('storemanger');
            $oStore= $obj_store->getList('store_name',array('store_id'=> $params['store_id']));
            $filter = array('member_id'=>$arrMember['member_id']);
            $filter['disabled'] = 'false';
            $filter['memc_isvalid'] = 'true';
            $filter['cpns_id|in'] = $cId;
            $aData[$params['store_id']] = $oCoupon->_get_list('*', $filter);
            if(is_array($aData[$params['store_id']])){
                $obj_rule_order = &app :: get('b2c')->model('sales_rule_order');
                foreach($aData[$params['store_id']] as $_key => $_val){
                    if($_val['coupons_info']['cpns_status'] != 1){
                        unset($aData[$params['store_id']][$_key]);
                        continue;
                    }
                    $member_lvs = explode(',',$_val['time']['member_lv_ids']);
                    if(!in_array($arrMember['member_lv'],(array)$member_lvs)){
                        unset($aData[$params['store_id']][$_key]);
                        continue;
                    }
                    $curTime = time();
                    if($curTime<$_val['time']['from_time'] || $curTime>=$_val['time']['to_time']){
                        unset($aData[$params['store_id']][$_key]);
                        continue;
                    }
                    if($_val['memc_used_times']>= app::get('b2c')->getConf('coupon.mc.use_times')){
                        unset($aData[$params['store_id']][$_key]);
                        continue;
                    }
                    if(!$_val['coupons_info']['cpns_status']){
                        unset($aData[$params['store_id']][$_key]);
                        continue;
                    }
                    if($_val['memc_isactive']=='false'){
                        unset($aData[$params['store_id']][$_key]);
                        continue;
                    }
                    $aData[$params['store_id']][$_key]['storename']=$oStore[0]['store_name'];
                    $aData[$params['store_id']][$_key]['description'] = $this->getdescbyid($obj_rule_order,$_val['coupons_info']['rule']['rule_id']);
                }
            }
        }
        foreach($aData[$params['store_id']]  as &$item){
            unset($item['cpns_id'],
                  $item['member_id'],
                  $item['memc_gen_orderid'],
                  $item['memc_source'],
                  $item['memc_enabled'],
                  $item['memc_used_times'],
                  $item['memc_gen_time'],
                  $item['disabled'],
                  $item['memc_isvalid'],
                  $item['memc_isactive'],
                  $item['coupons_info'],
                  $item['time']['member_lv_ids']
            );
        }
        $aData[$params['store_id']] = array_values($aData[$params['store_id']]);
        $this->send(true, $aData[$params['store_id']], '获取优惠劵列表成功');exit;
    }

    function update_total($store_id=0,$no_coupon){
        $params = $this->params;
        $must_params = array(
            'session'=>'登陆标识',
            'area_id'=>'收货地址',
            'shipping_id'=>'运费模板',
            //'is_protect'=>'物流保价',
            //'is_tax'=>'税金',
            //'tax_company'=>'税头',
            //'dis_point'=>'抵现积分',
        );
        $this->check_params($must_params);
        $type_temp = $params['type'];
        if($params['type'] == 'coupon' && isset($params['status'])&&$params['status']){
            if(isset($params['products'])){
                $temp = json_decode($params['products'],1);
            }elseif(isset($params['object'])){
                $temp = json_decode($params['object'],1);
            }
            $type_temp = $temp['type'];
        }
        $isfast = (isset($params['status'])&&$params['status'])?
        (($type_temp=='spike')?2:
        (($type_temp=='group')?3:
        (($type_temp=='score')?4:1))):0;
        $picSize = in_array(strtolower($params['picSize']), array('cl', 'cs'))?strtolower($params['picSize']):'cl';
        $params['shipping_id'] = json_decode($params['shipping_id'],1);
        $params['is_protect'] = isset($params['is_protect'])?json_decode($params['is_protect'],1):array();
        $params['is_tax'] = isset($params['is_tax'])?(!!$params['is_tax']):false;
        $params['tax_company'] = isset($params['tax_company'])?($params['is_tax']?$params['tax_company']:''):'';
        $params['dis_point'] = isset($params['dis_point'])?intval($params['dis_point']):0;
        $aCart = $this->_common(0,$picSize,$isfast);
        $aData = array();
        $split_order = kernel::single('cellphone_cart_split')->split_order($params['area_id'],$aCart,(!!$store_id&&!!$no_coupon));
        $no_shipping = false;
        foreach($split_order as $key=>$sgoods){
            foreach($sgoods['slips'] as $order_sp=>$order){
                if(empty($order['shipping'])){
                    $no_shipping = true;
                }
                foreach($order['shipping'] as $dkey=>$ship){
                    if($ship['default_type']=='true'){
                      $total_amount+=$ship['money'];
                      if($aCart['is_free_shipping'][$store_id]==1){//优惠券免运费
                           $total_amount-=$ship['money'];
                      }
                      if($total_amount<=0){
                          $total_amount=0;
                      }
                    }
                }
            }
        }
        if($total_amount < 0)$total_amount = 0;
        // 是否可以用积分抵扣
        $obj_point_dis = kernel::service('b2c_cart_point_discount');
        if ($obj_point_dis){
            $temp = $this->point_dis($total_amount);
            $temp['max_discount_value'] = (float)$temp['max_discount_value'];
            $temp['discount_rate'] = (float)$temp['discount_rate'];
            $min_score = $temp['discount_rate']==0?0:$temp['max_discount_value']/$temp['discount_rate'];
            $min_score = min((int)$min_score,(int)$temp['real_usage_point']);
        }else{
            $min_score = 0;
        }

        if($store_id){
            if(isset($aCart['coupon'][$store_id]) && !$aCart['coupon'][$store_id]['used']){
                $this->send(false, null, '优惠券不适用');exit;
            }
            $totle = $this->objMath->number_minus(array($aCart['subtotal_store_good_price'][$store_id],$aCart['store_count'][$store_id]));
            $totle = ($totle>0?$totle:0);
            $temp = 0.0;
            foreach($split_order[$store_id]['slips'] as $key=>$slip){
                foreach($slip['shipping'] as $skey=>$ship){
                    if(in_array($skey,$params['is_protect'])){
                        if($aCart['is_free_shipping'][$store_id]==1){
                            $aCart['store_count'][$store_id]+=$ship['protect_money'];
                        }
                    }
                    if(in_array($skey,$params['shipping_id'])){
                        if($aCart['is_free_shipping'][$store_id]==1){
                            $aCart['store_count'][$store_id]+=$ship['money'];
                        }
                    }
                }
                foreach((array)$slip['object'] as $value){
                    if($aCart['enable_object'][$value]['storeinfo']['store_id'] == $store_id)
                    $temp += $aCart['enable_object'][$value]['subtotal_price'];
                }
            }
            /*foreach((array)$aCart['store_count'] as $key => $value){
                $aData['store_count'][] = array('store_id'=>$key,'discount'=>$value);
            }*/
            $aData['store_count'] = array(
                'discount'=>$aCart['store_count'][$store_id],
                'coupon'=>$aCart['coupon'][$store_id]['coupon'],
                'economize'=>$this->objMath->number_minus(array($temp,$totle)),
                'md5_cart_info' => kernel::single("b2c_cart_objects")->md5_cart_objects(),
            );
        }

        $sdf_order = $params;
        $arrMember = $this->get_current_member();
        $sdf_order['member_id'] = $arrMember['member_id'];
        $currency = app::get('ectools')->model('currency');
        $str_def_currency = $arrMember['member_cur'] ? $arrMember['member_cur'] : "";
        if(!$str_def_currency){
            $arrDefCurrency = $currency->getDefault();
            $str_def_currency = $arrDefCurrency['cur_code'];
        }else{
            $arrDefCurrency = $currency->getcur($str_def_currency);
        }
        $sdf_order['cur'] = $str_def_currency;
        $obj_total = kernel::single('cellphone_order_splittotal');
        $obj_total->order_total_method($aCart,$sdf_order);

        $aData['order_detail'] = array(
            'tax_ratio' => $aCart['order_detail']['tax_ratio'], //发票税率
            'trigger_tax' => $aCart['order_detail']['trigger_tax'], //是否开启发票
            'tax_company' => $aCart['order_detail']['tax_company'], //发票抬头
            'cost_tax' => $aCart['order_detail']['cost_tax'], //开发票所需税金
            'cost_item' => $aCart['order_detail']['cost_item'], //商品金额
            'cost_freight' => $aCart['order_detail']['cost_freight'], //配送费用
            'cost_protect' => $aCart['order_detail']['cost_protect'], //配送保价费
            'total_amount' => $aCart['order_detail']['total_amount'], //实付金额
            'totalConsumeScore' => $aCart['order_detail']['totalConsumeScore'], //消费积分数
            'totalGainScore' => $aCart['order_detail']['totalGainScore'], //可获得积分数
            'discount_rate' => $aCart['order_detail']['site_point_deductible_value'], //积分金额转换率
            'real_usage_point' => $min_score, //当前可用积分
            'max_discount_value' => $aCart['order_detail']['total_dis_money'], //最大抵现金额
            'total_discount_consume_score' => $aCart['order_detail']['total_discount_consume_score'], //抵扣积分
            'total_dis_consume_money' => $aCart['order_detail']['total_dis_consume_money'], //抵扣金额
            'no_shipping' => $no_shipping,
        );
        $aData['money_format'] = $aCart['money_format'];
        $this->send(true, $aData, '更新确认订单列表页成功');exit;
    }

    //根据规则获取优惠券内容描述
    private function  getdescbyid($obj_rule_order,$rule_id){
        $arr = $obj_rule_order->getList( '*',array('rule_id'=>$rule_id));
        $aData['description'] =$arr[0]['description'];
        if($arr[0]['conditions']['conditions'][0]['attribute'] !='coupon'){
            return  $aData;
        }

        $oSOP = kernel::single('b2c_sales_order_process');
        $condi=  $oSOP->getTemplateList();

        //优惠条件
        $strname=$condi[$arr[0]['c_template']]['name'];

        /*
        //>=
        $aryoperator=array( '<'   =>app::get('b2c')->_('小于'),
               '<='  => app::get('b2c')->_('小于等于'),
               '>'   => app::get('b2c')->_('大于'),
               '>='  => app::get('b2c')->_('大于等于')
        );

        $stroperator=$aryoperator[$arr[0]['conditions']['conditions'][1]['conditions'][0]['operator']];
        */

        //100
        $strvalue=$arr[0]['conditions']['conditions'][1]['conditions'][0]['value'];

        //优惠方案
        $oSSP = kernel::single('b2c_sales_solution_process');
        $arry=$oSSP->getTemplateList();
        $strtype=$arr[0]['action_solution'][$arr[0]['s_template']]['type'];
        $strpercent=$arr[0]['action_solution'][$arr[0]['s_template']]['percent'];
        if($strpercent){
            $strpercent .='%';
        }
        $stramount=$arr[0]['action_solution'][$arr[0]['s_template']]['total_amount'];
        //$strcpns_id=$arr[0]['action_solution'][$arr[0]['s_template']]['cpns_id'];
        $strsolution=$arry[$strtype][$arr[0]['s_template']];

        $aData['conditions']=str_replace('X',$strvalue,$strname);
        $aData['solutions']=$strsolution;
        $aData['solutions_value']=$strpercent.$stramount.$strcpns_id;

        return $aData;

    }
}