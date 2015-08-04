<?php


class business_ctl_site_cart extends b2c_ctl_site_cart{

    var $noCache = true;

    public function __construct(&$app){
        parent::__construct(app::get('b2c'));
    }

    public function create()
    {
        //echo '<pre>';
        
        // 判断顾客登录方式.
        $login_type = $this->app->getConf('site.login_type');
        $arrMember = $this->get_current_member();

        if ($login_type == 'href' && !$arrMember['member_id'] && $_COOKIE['S']['ST_ShopEx-Anonymity-Buy'] != 'true')
            $this->redirect(array('app'=>'b2c','ctl'=>'site_cart','act'=>'loginbuy','arg0'=>'1'));

        // checkout url
        $this->begin(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout'));
        
        $db = kernel::database();
        $transaction_status = $db->beginTransaction();
        
        $this->mCart = $this->app->model('cart');
		
		if($_POST['fastbuy']){
			kernel::single('fastbuy_cart_fastbuy_goods')->get_fastbuy_arr(
				$_SESSION['S[Cart_Fastbuy]']['goods'],
				$_SESSION['S[Cart_Fastbuy]']['coupon'],
				$allCart);
		}else{
			$allCart = $this->mCart->get_objects();
		}
        // 校验购物车是否为空，如果为空，其他的不需要检查。
        if ($this->mCart->is_empty($allCart))
        {
            $this->end(false,app::get('b2c')->_('操作失败，购物车为空！'),$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index')),true,true);
        }
        
        
        $defaule_area_id = explode(':',$_POST['delivery']['ship_area']);
		$this->pagedata['area_id'] = $defaule_area_id[2];
        //如果商品一致，则分单信息也应该一致。
        $temp_split_order=kernel::single('b2c_cart_object_split')->split_order($this,$this->pagedata['area_id'],$allCart);
        
        if($allCart['json_order_split']!=$_POST['split_order']){
            $this->end(false,app::get('b2c')->_('购物车内容发生变化，请重新结算！'),$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')),true,true);
        }
        
        //自提
        if ($_POST['shipping']['pickup']) {
            $pickup_address = 0;
            foreach($temp_split_order as $store_id=>$sgoods){
                foreach($sgoods['slips'] as $order_sp=>$order){
                    $_POST['shipping'][$store_id][$order_sp]['shipping_id'] = '0';
                    $pickup_address = $_POST['shipping'][$store_id][$order_sp]['pickup'];
                    unset($_POST['shipping'][$store_id][$order_sp]['pickup']);
                }
            }
            $oAddress = app::get('business')->model('dlyaddress');
            $address = $oAddress->dump($pickup_address);
            $region = explode(':', $address['region']);
            $region = explode('/', $region[1]);
            if(in_array($region[0],array('北京','天津','上海','重庆'))){
                $region[0] = '';
            }
            $_POST['delivery'] = array(
                'addr_id' => $address['da_id'],
                'ship_area' => $address['region'],
                'ship_addr_area' => implode('', $region),
                'ship_addr' => $address['address'],
                'ship_zip' => $address['zip'],
                'ship_name' => $address['uname'],
                'ship_mobile' => $address['mobile'],
                'ship_tel' => $address['phone'],
            );
        }
        
        if ($allCart['isNeedDelivery']) {
            foreach($temp_split_order as $store_id=>$sgoods){
                foreach($sgoods['slips'] as $order_sp=>$order){
                    if(empty($order['shipping'])){                    
                        $this->end(false,app::get('b2c')->_('存在不支持所选地区配送的商品，请从购物车删除后重新结算！'),$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')),true,true);
                    }
                }
            }
        }
        //取得分单信息。
        $split_order=unserialize($_POST['split_order']);
        
        
		//检查买家是否是店家
		$checkSeller = kernel::service('business_check_goods_isMy');
		if($checkSeller){
			if(!$checkSeller->check_isSeller($msg)){
				$this->end(false, $msg, '',true,true);
			}
		}
		//begin 验证码
		if(isset($_POST['userVcode'])&&!base_vcode::verify('b2c',intval($_POST['verifycode']))){
			$this->end(false, '验证码不正确', 'javascript:changeimg("membervocde");',true,true);
        }else{
			unset($_POST['userVcode']);
			unset($_POST['verifycode']);
		}
		//$this->end(false, '验证码正确', '',true,true);
		
        
        $msg = "";

        /*if(empty($_POST['shipping'])){
            $msg = app::get('b2c')->_("请选择店铺配送方式！");
            $this->end(false, $msg, '',true,true);
        }else{
            foreach ($_POST['shipping'] as $skey => $sval) {
                if(!$sval['shipping_id']){
                    $msg = app::get('b2c')->_("请选择店铺配送方式！");
                    $this->end(false, $msg, '',true,true);
                }
            }
        }*/

        
        
        /*if($allCart['business_goods'] != $_POST['business_goods']){
            error_log(var_export($allCart['business_goods'].'======'.$_POST['business_goods'],1),3,DATA_DIR.'/logs/1errorlog.log');
            $msg = app::get('b2c')->_("订单生成失败，数据异常！");
            $this->end(false, $msg, '',true,true);
        }*/

        if (($allCart['isNeedAddress'] && (!$_POST['delivery']['ship_area'] || !$_POST['delivery']['ship_addr_area'] || !$_POST['delivery']['ship_addr'] || !$_POST['delivery']['ship_name'] || (!$_POST['delivery']['ship_email'] && !$arrMember['member_id']) || (!$_POST['delivery']['ship_mobile'] && !$_POST['delivery']['ship_tel']) || $_POST['delivery']['shipping_id'])) || !$_POST['payment']['pay_app_id'])
        {
            if (!$_POST['delivery']['ship_area'] || !$_POST['delivery']['ship_addr_area'])
            {
                $msg .= app::get('b2c')->_("收货地区不能为空！")."<br />";
            }

            if (!$_POST['delivery']['ship_addr'])
            {
                $msg .= app::get('b2c')->_("收货地址不能为空！")."<br />";
            }

            if (!$_POST['delivery']['ship_name'])
            {
                $msg .= app::get('b2c')->_("收货人姓名不能为空！")."<br />";
            }

            if (!$_POST['delivery']['ship_email'] && !$arrMember['member_id'])
            {
                $msg .= app::get('b2c')->_("Email不能为空！")."<br />";
            }

            if (!$_POST['delivery']['ship_mobile'] && !$_POST['delivery']['ship_tel'])
            {
                $msg .= app::get('b2c')->_("手机或电话必填其一！")."<br />";
            }

            // if (!$_POST['delivery']['shipping_id'])
            // {
            //     $msg .= app::get('b2c')->_("配送方式不能为空！")."<br />";
            // }

            if (!$_POST['payment']['pay_app_id'])
            {
                $msg .= app::get('b2c')->_("支付方式不能为空！")."<br />";
            }

            if (strpos($msg, '<br />') !== false)
            {
                $msg = substr($msg, 0, strlen($msg) - 6);
            }
            eval("\$msg = \"$msg\";");

            $this->end(false, $msg, '',true,true);
        }
        $this->dis_point=0;
        $this->dis_point_money=0;
        //积分抵扣处理 
        $this->point_usage=app::get('b2c')->getConf('site.point_usage');//积分用途：2：用于抵扣
        $this->get_policy_method=app::get('b2c')->getConf('site.get_policy.method');//是否启用积分：1：不启用。
        $this->site_point_deductible_value = app::get('b2c')->getConf('site.point_deductible_value');
        if($this->point_usage==2&& $this->get_policy_method!=1){
            $this->dis_point=$_POST['payment']['dis_point']?$_POST['payment']['dis_point']:0;
            $this->dis_point_money=$this->dis_point*$this->site_point_deductible_value;//抵扣的总金额
            /*$discount_value = $_POST['payment']['dis_point']*$site_point_deductible_value;//抵扣的总金额
            $total_amout_arr = array();//每个订单的商品总金额
            foreach (json_decode($allCart['business_goods']) as $store_id => $Store_goods) {
                $aCart = $this->mCart->get_objects($Store_goods);
                $total_amout_arr[$store_id] = $aCart['subtotal'] - $aCart['subtotal_discount'];
            }
            $dis_point_money = array();//每个订单抵扣金额
            $num_total_amout_arr = count($total_amout_arr);
            for($i=0;$i<$num_total_amout_arr;$i++){
                $max_total_amount = max($total_amout_arr);
                foreach($total_amout_arr as $key=>$v){
                    if($v == $max_total_amount){
                        if($discount_value > $max_total_amount){//抵扣金额大于订单金额
                            $dis_point_money[$key] = $max_total_amount;
                            $discount_value -= $max_total_amount;
                        }else{
                            $dis_point_money[$key] = $discount_value;
                            $discount_value = 0;
                        }
                        unset($total_amout_arr[$key]);
                    }
                }
            }*/
        }
        //积分抵扣处理 
        
        // 根据购物车中的商家进行拆单 weifeng
       /* foreach ($allCart['object']['goods'] as $agkey => $agval) {
            $aGids[] = $agval['params']['goods_id'];
        }

        if(!$aGids['0']){
            error_log(var_export($allCart['business_goods'].'======'.$_POST['business_goods'],1),3,DATA_DIR.'/logs/2errorlog.log');
            $msg = app::get('b2c')->_("订单生成失败，数据异常！");
            $this->end(false, $msg, '',true,true);
        }

        $mdl_goods = app::get('b2c')->model('goods');
        $gStoreID = $mdl_goods->getList('goods_id,store_id',array('goods_id'=>$aGids));
        if(!isset($gStoreID['0'])){
            error_log(var_export($aGids,1),3,DATA_DIR.'/logs/3errorlog.log');
            $msg = app::get('b2c')->_("订单生成失败，数据异常！");
            $this->end(false, $msg, '',true,true);
        }
        foreach ($gStoreID as $gskey => $gsval) {
            $aStoreID[$gsval['goods_id']] = $gsval['store_id'];
        }*/
        //print_r($temp_split_order);
       //print_r($allCart);
        /*foreach($temp_split_order as $store_id=>$sgoods){
            foreach($sgoods['slips'] as $order_sp=>$sorder){
                //post数据
                $order = &$this->app->model('orders');
                //分单后重新构建购物车数据.
                $aCart=$this->get_split_cart($allCart,$sorder,$_POST,$_POST['shipping'][$store_id][$order_sp],$store_id);
                
                $postData=$this->get_post_cart($aCart,$sorder,$_POST,$_POST['shipping'][$store_id][$order_sp]);
              //取得购物车数据对应的订单数据。
                $order_data=$this->get_order_data($aCart,$postData,$store_id,$msg,$arrMember);
               //print_r($aCart);
               print_r($order_data);
            }
        }
            exit;*/
        foreach($temp_split_order as $store_id=>$sgoods){
            foreach($sgoods['slips'] as $order_sp=>$sorder){
                //post数据
                $order = &$this->app->model('orders');
                //分单后重新构建购物车数据.
                $aCart=$this->get_split_cart($allCart,$sorder,$_POST,$_POST['shipping'][$store_id][$order_sp],$store_id);
                
                $postData=$this->get_post_cart($aCart,$sorder,$_POST,$_POST['shipping'][$store_id][$order_sp]);
                
                //取得购物车数据对应的订单数据。
                $order_data=$this->get_order_data($aCart,$postData,$store_id,$msg,$arrMember);
               
               // print_r($aCart);
              //  print_r($order_data);
           // }
            //}
            //print_r($_POST);
           // echo '</pre>';
           // exit;
           // foreach (json_decode($allCart['business_goods'],true) as $store_id => $Store_goods) {

                //判断商品的store_id与$allCart中的store_id是否一致，防止出现下单时订单跑到别的店铺中
                /*foreach ($Store_goods as $sgkey => $sgval) {
                    list($type,$gid,$pid) = explode('_', $sgval);
                    if($aStoreID[$gid] != $store_id){
                        error_log(var_export($allCart['business_goods'].'======'.$_POST['business_goods'],1),3,DATA_DIR.'/logs/4errorlog.log');
                        $msg = app::get('b2c')->_("订单生成失败，数据异常！");
                        $this->end(false, $msg, '',true,true);
                    }
                }*/

                /*$aCart = $this->mCart->get_objects($Store_goods);
                $this->store_total($aCart);
                
                $aCart['store_free_shipping'] = $aCart['store_free_shipping'][$store_id];
                $aCart['subtotal_weight_store'] = $aCart['subtotal_weight_store'][$store_id];
                $postData = $_POST;
                if($site_point_deductible_value>0){
                    $postData['payment']['dis_point'] = $dis_point_money[$store_id]/$site_point_deductible_value;//订单抵扣积分
                }else{
                    $postData['payment']['dis_point'] = 0;
                }
                $shipping_id = $_POST['shipping'][$store_id]['shipping_id'];

                $postData['delivery']['shipping_id'] = $shipping_id;
                $postData['delivery']['is_protect'][$shipping_id] = $_POST['shipping'][$store_id]['is_protect'];
                $postData['memo'] = $_POST['shipping'][$store_id]['memo'];
                unset($postData['shipping']);*/

                //当有活动时，在生成订单前做一个当前购买数量与实际库存的判断
                /*if( isset($aCart['cart_status'] )){

                    $this->end(false,app::get('b2c')->_($aCart['cart_error_html']),$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index')),true,true);

                }*/

                // 校验购物车是否为空
                /*if ($this->mCart->is_empty($aCart))
                {
                    $this->end(false,app::get('b2c')->_('操作失败，购物车为空！'),$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index')),true,true);
                }

                

                $obj_dlytype = $this->app->model('dlytype');
                if ($postData['payment']['pay_app_id'] == '-1')
                {
                    $arr_dlytype = $obj_dlytype->dump($postData['delivery']['shipping_id'], '*');
                    if ($arr_dlytype['has_cod'] == 'false')
                    {
                        $this->end(false, $this->app->_("ship_method_consistent_error"),'',true,true);
                    }
                }

                $obj_filter = kernel::single('b2c_site_filter');
                $postData = $obj_filter->check_input($postData);

                $order = &$this->app->model('orders');
                $postData['order_id'] = $order_id = $order->gen_id();
                $postData['member_id'] = $arrMember['member_id'] ? $arrMember['member_id'] : 0;
                $order_data = array();
                $obj_order_create = kernel::single("b2c_order_create");
                // 加入订单能否生成的判断
                $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
                if ($obj_checkorder)
                {
                    if (!$obj_checkorder->check_create($aCart, $postData['delivery']['ship_area'], $message))
                        $this->end(false, $message);
                }

                $order_data = $obj_order_create->generate($postData,'',$msg,$aCart,$store_id);
                $order_data['store_id'] = $store_id;
                $obj_checkproducts = kernel::servicelist('b2c_order_check_products');
                if ($obj_checkproducts)
                {
                    foreach($obj_checkproducts as $obj_check){
                        if (!$obj_check->check_products($order_data, $messages)){
                            
                             $this->end(false, $messages,'',true,true);
                            }
                    }
                }
                if (!$order_data || !$order_data['order_objects'])
                {
                    $db->rollback();
                    $this->end(false, $msg, '',true,true);
                }

                if($order_data['shipping']['shipping_id'] == null || $order_data['shipping']['shipping_id'] == ''){
                    $msg = app::get('b2c')->_("请选择店铺配送方式！");
                    $this->end(false, $msg, '',true,true);
                }
    */          $obj_order_create = kernel::single("b2c_order_create");
                $order_id=$order_data['order_id'];
                $result = $obj_order_create->save($order_data, $msg);
                
                if ($result)
                {
                    // 发票高级配置埋点
                    foreach( kernel::servicelist('invoice_setting') as $services ) {
                        if ( is_object($services) ) {
                            if ( method_exists($services, 'saveInvoiceData') ) {
                                $services->saveInvoiceData($postData['order_id'],$postData['payment']);
                            }
                        }
                    }
                }

                // 取到日志模块
                if ($arrMember['member_id'])
                {
                    $obj_members = $this->app->model('members');
                    $arrPams = $obj_members->dump($arrMember['member_id'], '*', array(':account@pam' => array('*')));
                }

                // remark create
                $obj_order_create = kernel::single("b2c_order_remark");
                $arr_remark = array(
                    'order_bn' => $order_id,
                    'mark_text' => $postData['memo'],
                    'op_name' => (!$arrMember['member_id']) ? app::get('b2c')->_('顾客') : $arrPams['pam_account']['login_name'],
                    'mark_type' => 'b0',
                );

                $log_text = "";
                if ($result)
                {
                    $log_text[] = array(
                        'txt_key'=>'订单创建成功！',
                        'data'=>array(
                        ),
                    );
                    $log_text = serialize($log_text);
                }
                else
                {
                    $log_text[] = array(
                        'txt_key'=>'订单创建失败！',
                        'data'=>array(
                        ),
                    );
                    $log_text = serialize($log_text);
                }
                $orderLog = $this->app->model("order_log");
                $sdf_order_log = array(
                    'rel_id' => $order_id,
                    'op_id' => $arrMember['member_id'],
                    'op_name' => (!$arrMember['member_id']) ? app::get('b2c')->_('顾客') : $arrPams['pam_account']['login_name'],
                    'alttime' => time(),
                    'bill_type' => 'order',
                    'behavior' => 'creates',
                    'result' => 'SUCCESS',
                    'log_text' => $log_text,
                );

                $log_id = $orderLog->save($sdf_order_log);

                if ($result)
                {
                    foreach(kernel::servicelist('b2c_save_post_om') as $object)
                    {
                        $object->set_arr($order_id, 'order');
                    }

                    $this->cart_model = &$this->app->model('cart_objects');
                    // 设定优惠券不可以使用
                    if (isset($aCart['object']['coupon']) && $aCart['object']['coupon'])
                    {
                        $obj_coupon = kernel::single("b2c_coupon_mem");
                        foreach ($aCart['object']['coupon'] as $coupons)
                        {
                            if($coupons['used'])
                                $obj_coupon->use_c($coupons['coupon'], $arrMember['member_id']);
                        }
                        foreach ($aCart['object']['coupon'] as $ckey => $cval) {
                            if($cval['store_id'] == $store_id){
                                $this->cart_model->remove_object('coupon', $cval['obj_ident']);
                            }
                        }
                    }

                    // 订单成功后清除购物车的的信息
					if($_POST['fastbuy']){
						unset($_SESSION['S[Cart_Fastbuy]']);//立即购买后清空session,普通购买清空购物车
					}else{
						$this->cart_model->remove_object('goods', $Store_goods);
					}
					
                    

                    // 生成cookie有效性的验证信息
                    setcookie("S[ST_ShopEx-Anonymity-Buy]", "false", time() - 3600, kernel::base_url().'/');
                    setcookie("purchase[addr][usable]", "", time() - 3600, kernel::base_url().'/');
                    setcookie("purchase[shipping]", "", time() - 3600, kernel::base_url().'/');
                    setcookie("purchase[payment]", "", time() - 3600, kernel::base_url().'/');
                    setcookie("checkout_b2c_goods_buy_info", "", time() - 3600, kernel::base_url().'/');

                    // 得到物流公司名称
                    if ($order_data['order_objects'])
                    {
                        $itemNum = 0;
                        $good_id = "";
                        $goods_name = "";
                        foreach ($order_data['order_objects'] as $arr_objects)
                        {
                            if ($arr_objects['order_items'])
                            {
                                if ($arr_objects['obj_type'] == 'goods')
                                {
                                    $obj_goods = $this->app->model('goods');
                                    $good_id = $arr_objects['order_items'][0]['goods_id'];
                                    $obj_goods->updateRank($good_id, 'buy_count',$arr_objects['order_items'][0]['quantity']);
                                    $arr_goods = $obj_goods->dump($good_id);
                                }

                                foreach ($arr_objects['order_items'] as $arr_items)
                                {
                                    $itemNum = $this->objMath->number_plus(array($itemNum, $arr_items['quantity']));
                                    if ($arr_objects['obj_type'] == 'goods')
                                    {
                                        if ($arr_items['item_type'] == 'product')
                                            $goods_name .= $arr_items['name'] . ($arr_items['products']['spec_info'] ? '(' . $arr_items['products']['spec_info'] . ')' : '') . '(' . $arr_items['quantity'] . ')';
                                    }
                                }
                            }
                        }
                        $obj_dlytype = $this->app->model('dlytype');
                        $arr_dlytype = $obj_dlytype->dump($order_data['shipping']['shipping_id'], 'dt_name');
                        $arr_updates = array(
                            'order_id' => $order_id,
                            'total_amount' => $order_data['total_amount'],
                            'shipping_id' => $arr_dlytype['dt_name'],
                            'ship_mobile' => $order_data['consignee']['mobile'],
                            'ship_tel' => $order_data['consignee']['telephone'],
                            'ship_addr' => $order_data['consignee']['addr'],
                            'ship_email' => $order_data['consignee']['email'] ? $order_data['consignee']['email'] : '',
                            'ship_zip' => $order_data['consignee']['zip'],
                            'ship_name' => $order_data['consignee']['name'],
                            'member_id' => $order_data['member_id'] ? $order_data['member_id'] : 0,
                            'uname' => (!$order_data['member_id']) ? app::get('b2c')->_('顾客') : $arrPams['pam_account']['login_name'],
                            'itemnum' => count($order_data['order_objects']),
                            'goods_id' => $good_id,
                            'goods_url' => kernel::base_url(1).kernel::url_prefix().$this->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg0'=>$good_id)),
                            'thumbnail_pic' => base_storager::image_path($arr_goods['image_default_id']),
                            'goods_name' => $goods_name,
                            'ship_status' => '',
                            'pay_status' => 'Nopay',
                            'is_frontend' => true,
                        );
                        $order->fireEvent('create', $arr_updates, $order_data['member_id']);
                    }

                    $db->commit($transaction_status);

                    /** 订单创建结束后执行的方法 **/
                    $odr_create_service = kernel::servicelist('b2c_order.create');
                    $arr_order_create_after = array();
                    if ($odr_create_service)
                    {
                        foreach ($odr_create_service as $odr_ser)
                        {
                            if(!is_object($odr_ser)) continue;

                            if( method_exists($odr_ser,'get_order') )
                                $index = $odr_ser->get_order();
                            else $index = 10;

                            while(true) {
                                if( !isset($arr_order_create_after[$index]) )break;
                                $index++;
                            }
                            $arr_order_create_after[$index] = $odr_ser;
                        }
                    }
                    ksort($arr_order_create_after);
                    if ($arr_order_create_after)
                    {
                        foreach ($arr_order_create_after as $obj)
                        {
                            $obj->generate($order_data);
                        }
                    }
                    /** end **/
                }
                else
                {
                    $db->rollback();
                }

                if ($result)
                {
                    $order_num = $order->count(array('member_id' => $order_data['member_id']));
                    $obj_mem = $this->app->model('members');
                    $obj_mem->update(array('order_num'=>$order_num), array('member_id'=>$order_data['member_id']));

                    /** 订单金额为0 **/ 
                    /* 0元订单有支付特殊处理。
                    if ($order_data['cur_amount'] == '0')
                    {
                        // 模拟支付流程
                        $objPay = kernel::single("ectools_pay");
                        $sdf = array(
                            'payment_id' => $objPay->get_payment_id(),
                            'order_id' => $order_data['order_id'],
                            'rel_id' => $order_data['order_id'],
                            'op_id' => $order_data['member_id'],
                            'pay_app_id' => $order_data['payinfo']['pay_app_id'],
                            'currency' => $order_data['currency'],
                            'payinfo' => array(
                                'cost_payment' => $order_data['payinfo']['cost_payment'],
                            ),
                            'pay_object' => 'order',
                            'member_id' => $order_data['member_id'],
                            'op_name' => $this->user->user_data['account']['login_name'],
                            'status' => 'ready',
                            'cur_money' => $order_data['cur_amount'],
                            'money' => $order_data['total_amount'],
                        );
                        $is_payed = $objPay->gopay($sdf, $msg);
                        if (!$is_payed){
                            $msg = app::get('b2c')->_('订单自动支付失败！');
                            $this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')));
                        }

                        $obj_pay_lists = kernel::servicelist("order.pay_finish");
                        $is_payed = false;
                        foreach ($obj_pay_lists as $order_pay_service_object)
                        {
                            $is_payed = $order_pay_service_object->order_pay_finish($sdf, 'succ', 'font',$msg);
                        }
                    }*/

                    // 与中心交互
                    $is_need_rpc = false;
                    $obj_rpc_obj_rpc_request_service = kernel::servicelist('b2c.rpc_notify_request');
                    foreach ($obj_rpc_obj_rpc_request_service as $obj)
                    {
                        if ($obj && method_exists($obj, 'rpc_judge_send'))
                        {
                            if ($obj instanceof b2c_api_rpc_notify_interface)
                                $is_need_rpc = $obj->rpc_judge_send($order_data);
                        }

                        if ($is_need_rpc) break;
                    }

                    if ($is_need_rpc)
                    {
                      //新的版本控制api
                      $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                      $obj_apiv->rpc_caller_request($order_data, 'ordercreate');
                    }
                    
                    $flag = true;
                    $aOrders[] = $order_id;
                    
                }else{
                    $flag = false;
                }
            }
        }
            
        if($flag){
            $orderStr = base64_encode(implode('|', $aOrders));
            setcookie('ST_ShopEx-Order-Buy', md5($this->app->getConf('certificate.token').$orderStr));
            if(count($aOrders)>1){
                $this->end(true, $this->app->_("订单生成成功！"), $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'all_orderPayments','args'=>array(false,'',$orderStr))),'',true);
            }else{
                $this->end(true, $this->app->_("订单生成成功！"), $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'orderPayments','args'=>array($aOrders[0],false,true))),'',true);
            }
        }else{
            $this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')),true,true);
        }
    }
    //取得订单数据。
    function get_order_data($aCart,$postData,$store_id,&$msg,$arrMember){
        $obj_filter = kernel::single('b2c_site_filter');
        $postData = $obj_filter->check_input($postData);

        $order = &$this->app->model('orders');
        $postData['order_id'] = $order_id = $order->gen_id();
        $postData['member_id'] = $arrMember['member_id'] ? $arrMember['member_id'] : 0;
        $order_data = array();
        
        $obj_order_create = kernel::single("b2c_order_create");
        // 加入订单能否生成的判断
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if ($obj_checkorder)
        {
            if (!$obj_checkorder->check_create($aCart, $postData['delivery']['ship_area'], $message))
                $this->end(false, $message);
        }

        $order_data = $obj_order_create->generate($postData,'',$msg,$aCart);
        $order_data['store_id'] = $store_id;
        $obj_checkproducts = kernel::servicelist('b2c_order_check_products');
        if ($obj_checkproducts)
        {
            foreach($obj_checkproducts as $obj_check){
                if (!$obj_check->check_products($order_data, $messages)){
                    
                     $this->end(false, $messages,'',true,true);
                    }
            }
        }
        if (!$order_data || !$order_data['order_objects'])
        {
            $db->rollback();
            $this->end(false, $msg, '',true,true);
        }

        if($order_data['shipping']['shipping_id'] == null || $order_data['shipping']['shipping_id'] == ''){
            $isNeedDelivery = true;
            if (isset($order_data['order_kind']) && $order_data['order_kind'] == '3rdparty') {
                foreach(kernel::servicelist('3rdparty_goods_processor') as $processor) {
                    if ($processor->goodsKindDetail() == $order_data['order_kind_detail'] && $processor->isCustom('order_delivery')) {
                        $isNeedDelivery = $processor->isNeedDelivery();
                        break;
                    }
                }
            }
            if ($isNeedDelivery) {
                $msg = app::get('b2c')->_("请选择店铺配送方式！");
                $this->end(false, $msg, '',true,true);
            }
        }
        return $order_data;
    }
    //为分单准备post数据。
    function get_post_cart($aCart,$sOrder,$postData,$oShipping){
        $post=array();
        if(isset($postData['purchase'])){
            $post['purchase']=$postData['purchase'];
        }
        if(isset($postData['split_order'])){
            $post['split_order']=$postData['purchase'];
        }
        if(isset($postData['extends_args'])){
            $post['extends_args']=$postData['extends_args'];
        }
        if(isset($postData['delivery'])){
            $post['delivery']=$postData['delivery'];
        }
        if(isset($postData['payment'])){
            $post['payment']=$postData['payment'];
        }
        if(isset($postData['fromCart'])){
            $post['fromCart']=$postData['fromCart'];
        }
        if(isset($postData['split_order'])){
            $post['split_order']=$postData['split_order'];
        }
        if(isset($postData['minfo'])){
            $post['minfo']=$postData['minfo'];
        }
        $shipping_id = $oShipping['shipping_id'];
        $post['delivery']['shipping_id'] = $shipping_id;
        $post['delivery']['is_protect'][$shipping_id] = $oShipping['is_protect'];
        $post['is_protect'][]= $oShipping['is_protect'];
        $post['shipping_id'][]= $oShipping['shipping_id'];
        $post['memo'] = $oShipping['memo'];
        $post['payment']['dis_point']=0;
        //保价费
        //$cost_protect=0;
        //if($oShipping['is_protect']){
            //$cost_protect+=$sOrder['shipping'][$oShipping['shipping_id']]['protect_money'];                
        //}
        if($this->point_usage==2&& $this->get_policy_method!=1){
            if($this->dis_point>0 && $this->site_point_deductible_value>0 &&$this->dis_point_money>0){
                //订单余额。
                $subtotal=$aCart['subtotal'] - $aCart['subtotal_discount'];//+$cost_protect;
                
                if($subtotal>$this->dis_point_money){//订单余额满足积分抵扣金额。
                    $post['payment']['dis_point']=$this->dis_point;
                    $this->dis_point=0;
                }else{
                    $tdis_point_money=$subtotal;
                    //根据抵扣钱数重新计算抵扣积分。
                    $post['payment']['dis_point']=$tdis_point_money/$this->site_point_deductible_value;
                    $this->dis_point-=$post['payment']['dis_point'];
                    $this->dis_point_money-=$tdis_point_money;
                }
            }
        }
        //print_r($post);
        return $post;
    }
    //根据分单数据重新构建购物车结构，以便直接生成订单结构。
    function get_split_cart(&$allCart,$sOrder,$postData,$oShipping,$store_id){
        $sCart=array();        
        foreach($sOrder['object'] as $obj_type=>$obj){
           foreach($obj['index'] as $index){
                $sCart['object'][$obj_type][]=$allCart['object'][$obj_type][$index];
           }
        }
         
        $this->mCart->count_objects($sCart);
        if($allCart['promotion']){
            foreach($allCart['promotion'] as $pkey=> $promotion){
                foreach($promotion as $ptypekey=> $pcoupon){
                    if($allCart['is_free_shipping'][$store_id]){//免运费
                        if($pcoupon['store_id']==$store_id){
                            $sCart['promotion'][$pkey][$ptypekey]=$pcoupon;
                        }                              
                    }else{//折扣或者送优惠券
                        if($pcoupon['store_id']==$store_id){
                            if($pcoupon['discount_amount']>0){                                   
                               if($sCart['subtotal']>$pcoupon['discount_amount']){
                                    
                                    $sCart['discount_amount']=$pcoupon['discount_amount'];
                                    $sCart['subtotal_discount']=$pcoupon['discount_amount'];
                                    $sCart['discount_amount_order']=$pcoupon['discount_amount'];
                                    $allCart['promotion'][$pkey][$ptypekey]=$pcoupon['discount_amount']-$sCart['subtotal'];
                               }
                               $sCart['promotion'][$pkey][$ptypekey]=$pcoupon;
                            }else if($pcoupon['discount_amount']==0){//送优惠券。
                               $sCart['promotion'][$pkey][$ptypekey]=$pcoupon;
                               unset($allCart['promotion'][$pkey][$ptypekey]);
                            }
                        }
                    }
                }
            }
        }
        if($sCart['promotion']){
            if($allCart['object']['coupon']){
               foreach($allCart['object']['coupon'] as $coupon){
                   if($coupon['store_id']==$store_id){
                        $sCart['object']['coupon'][]=$coupon;
                   }
               }
            }
        }
        if($allCart['is_free_shipping']){
            $sCart['is_free_shipping']=$allCart['is_free_shipping'][$store_id];
        }
        if($allCart['free_shipping_rule_type']){
            $sCart['free_shipping_rule_type']=$allCart['free_shipping_rule_type'][$store_id];
        }
        if($allCart['free_shipping_rule_id']){
            $sCart['free_shipping_rule_id']=$allCart['free_shipping_rule_id'][$store_id];
        }        
        if(isset($allCart['inAct'])){
           $sCart['inAct']=$allCart['inAct'];
        }
        if(isset($allCart['isNeedAddress'])){
           $sCart['isNeedAddress']=$allCart['isNeedAddress'];
        }
        if(isset($allCart['isNeedDelivery'])){
           $sCart['isNeedDelivery']=$allCart['isNeedDelivery'];
        }
        //print_r($sCart);
        return $sCart;
    }
}
