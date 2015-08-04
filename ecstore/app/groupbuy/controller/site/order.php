<?php


class groupbuy_ctl_site_order extends b2c_ctl_site_order{
    var $noCache = true;
    public function __construct(&$app){
        parent::__construct(app::get('b2c'));
    }

     public function create()
    {
        // 判断顾客登录方式.
        $login_type = $this->app->getConf('site.login_type');
        $arrMember = $this->get_current_member();

        if ($login_type == 'href' && !$arrMember['member_id'] && $_COOKIE['S']['ST_ShopEx-Anonymity-Buy'] != 'true')
            $this->redirect(array('app'=>'b2c','ctl'=>'site_cart','act'=>'loginbuy','arg0'=>'1'));

        // checkout url
        $this->begin(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout'));

        //验证码验证
        if(isset($_POST['userVcode'])&&!base_vcode::verify('b2c',intval($_POST['verifycode']))){
            $this->end(false, '验证码输入错误！', 'javascript:changeimg("membervocde");',true,true);
        }else{
            unset($_POST['userVcode']);
            unset($_POST['verifycode']);
        }

        //检查买家是否是店家
        $checkSeller = kernel::service('business_check_goods_isMy');
        if($checkSeller){
            if(!$checkSeller->check_isSeller($msg)){
                $this->end(false, $msg, '',true,true);
            }
        }

        $db = kernel::database();
        $transaction_status = $db->beginTransaction();

        $this->mCart = $this->app->model('cart');
        kernel::single('groupbuy_cart_fastbuy_goods')->get_fastbuy_arr(
                $_SESSION['S[Cart_Fastbuy]']['goods'],
                $_SESSION['S[Cart_Fastbuy]']['coupon'],
                $aCart);//立即购买  
        $this->store_total($aCart);

        //检查每人限购 
        $member_id = app::get('b2c')->member_id;
        $applyObj = app::get('groupbuy')->model('groupapply');
        $gid = $aCart['object']['goods'][0]['obj_items']['products'][0]['goods_id'];
        $num = $aCart['object']['goods'][0]['quantity'];
        $aid = $applyObj->getOnActIdByGoodsId($gid);
        if($aid){
            $obj_business_activity = kernel::single('groupbuy_business_activity');
            if (!$obj_business_activity->checkPersonLimit($num,$member_id,$aid,&$msg)){
               $this->end(false, $msg, '',true,true);
            }
        }else{
            $this->end(false, '不存在此活动，或活动已经结束', '',true,true);
        }

        //当有活动时，在生成订单前做一个当前购买数量与实际库存的判断
        if( isset($aCart['cart_status'] )){

            $this->end(false,app::get('b2c')->_($aCart['cart_error_html']),$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index')),true,true);

        }

        // 校验购物车是否为空
        if ($this->mCart->is_empty($aCart))
        {
            $this->end(false,app::get('b2c')->_('操作失败，购物车为空！'),$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index')),true,true);
        }
        // 校验购物车有没有发生变化
        $md5_cart_info = $_POST['md5_cart_info'];
        if ($md5_cart_info != kernel::single("b2c_cart_objects")->md5_cart_objects())
            $this->end(false,app::get('b2c')->_('购物车内容发生变化，请重新结算！'),$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')),true,true);

        $msg = "";
		if(empty($_POST['shipping'])){
            $msg = app::get('b2c')->_("请选择店铺配送方式！");
            $this->end(false, $msg, '',true,true);
        }else{
            foreach ($_POST['shipping'] as $skey => $sval) {
                if(!$sval['shipping_id']){
                    $msg = app::get('b2c')->_("请选择店铺配送方式！");
                    $this->end(false, $msg, '',true,true);
                }
            }
        }
        if (!$_POST['delivery']['ship_area'] || !$_POST['delivery']['ship_addr_area'] || !$_POST['delivery']['ship_addr'] || !$_POST['delivery']['ship_name'] || (!$_POST['delivery']['ship_email'] && !$arrMember['member_id']) || (!$_POST['delivery']['ship_mobile'] && !$_POST['delivery']['ship_tel']) || !$_POST['payment']['pay_app_id'])
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

            /*if (!$_POST['delivery']['shipping_id'])
            {
                $msg .= app::get('b2c')->_("配送方式不能为空！")."<br />";
            }*/

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

        $obj_dlytype = $this->app->model('dlytype');
        if ($_POST['payment']['pay_app_id'] == '-1')
        {
            $arr_dlytype = $obj_dlytype->dump($_POST['delivery']['shipping_id'], '*');
            if ($arr_dlytype['has_cod'] == 'false')
            {
                $this->end(false, $this->app->_("ship_method_consistent_error"),'',true,true);
            }
        }

        $obj_filter = kernel::single('b2c_site_filter');
        $_POST = $obj_filter->check_input($_POST);

        $order = &$this->app->model('orders');
        $_POST['order_id'] = $order_id = $order->gen_id();
        $_POST['member_id'] = $arrMember['member_id'] ? $arrMember['member_id'] : 0;
        $order_data = array();
        $obj_order_create = kernel::single("groupbuy_order_create");
        // 加入订单能否生成的判断
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        
        if ($obj_checkorder)
        {
            if (!$obj_checkorder->check_create($aCart, $_POST['delivery']['ship_area'], $message))
                $this->end(false, $message);
        }
        
        $shipping = array_shift($_POST['shipping']);
        $_POST['delivery']['shipping_id'] = $shipping['shipping_id'];
        $_POST['delivery']['is_protect'][$shipping['shipping_id']] = $shipping['is_protect'];
        $_POST['delivery']['memo'] = $shipping['memo']; 
        $business_goods = json_decode($_POST['business_goods'], true);
        $store_id = array_shift(array_keys($business_goods));
		$order_data = $obj_order_create->generate($_POST,'',$msg,$aCart,$store_id);
		$order_data['store_id'] = $store_id;
        
        $obj_checkproducts = kernel::servicelist('b2c_order_check_products');
        if ($obj_checkproducts)
        {
            foreach($obj_checkproducts as $obj_check){
                if (!$obj_check->check_products($order_data, $messages))
                    $this->end(false, $messages);
            }
        }
        if (!$order_data || !$order_data['order_objects'])
        {
            $db->rollback();
            $this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')),true,true);
        }

        //增加团购字段
        $order_data['order_type'] = 'group';
        $applyObj = app::get('groupbuy')->model('groupapply');
        $goods_id = $order_data['order_objects'][0]['order_items'][0]['goods_id'];
        $act_id = $applyObj->getOnActIdByGoodsId($goods_id);
        if($act_id){
            $order_data['act_id'] = $act_id;
        }

        if($order_data['shipping']['shipping_id'] == null || $order_data['shipping']['shipping_id'] == ''){
            $msg = app::get('b2c')->_("请选择店铺配送方式！");
            $this->end(false, $msg, '',true,true);
        }
        $result = $obj_order_create->save($order_data, $msg);
        if ($result)
        {
            // 发票高级配置埋点
            foreach( kernel::servicelist('invoice_setting') as $services ) {
                if ( is_object($services) ) {
                    if ( method_exists($services, 'saveInvoiceData') ) {
                        $services->saveInvoiceData($_POST['order_id'],$_POST['payment']);
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
            'mark_text' => $_POST['memo'],
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

            // 设定优惠券不可以使用
            if (isset($aCart['object']['coupon']) && $aCart['object']['coupon'])
            {
                $obj_coupon = kernel::single("b2c_coupon_mem");
                foreach ($aCart['object']['coupon'] as $coupons)
                {
                    if($coupons['used'])
                        $obj_coupon->use_c($coupons['coupon'], $arrMember['member_id']);
                }
            }

            // 订单成功后清除购物车的的信息

            unset($_SESSION['S[Cart_Fastbuy]']);//立即购买后清空session,普通购买清空购物车

            //$this->cart_model = &$this->app->model('cart_objects');
            //$this->cart_model->remove_object();

            // 生成cookie有效性的验证信息
            //setcookie('ST_ShopEx-Order-Buy', md5($this->app->getConf('certificate.token').$order_id));
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
            $odr_create_service = kernel::servicelist('group_order.create');
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
            }

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
              /*
                $obj_rpc_request_service = kernel::service('b2c.rpc.send.request');
                if ($obj_rpc_request_service && method_exists($obj_rpc_request_service, 'rpc_caller_request'))
                {
                    if ($obj_rpc_request_service instanceof b2c_api_rpc_request_interface)
                        $obj_rpc_request_service->rpc_caller_request($order_data,'create');
                }
                else
                {
                    $obj_order_create->rpc_caller_request($order_data);
                    }*/
              //新的版本控制api
              $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
              $obj_apiv->rpc_caller_request($order_data, 'ordercreate');
            }
            
            $orderStr = base64_encode($order_id);
            setcookie('ST_ShopEx-Order-Buy', md5($this->app->getConf('certificate.token').$orderStr));
            
            $this->end(true, $this->app->_("订单生成成功！"), $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'orderPayments','args'=>array($order_id,false,true))),'',true);
            //$this->end(true, $this->app->_("订单生成成功！"), $this->gen_url(array('app'=>'b2c','ctl'=>'site_order','act'=>'index','arg0'=>$orderStr)),'',true);
        }
        else
            $this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')),true,true);
    }

    function store_total(&$aCart){
        foreach($aCart['object']['goods'] as $key=>$value){
            $total[$value['store_id']][$value['obj_ident']] = $value['subtotal_price'];
            if($value['freight_bear']=='business'){
                $store_free_shipping[$value['store_id']][] = 0;
                $total_weight[$value['store_id']][] = '0';
            }else{
                $store_free_shipping[$value['store_id']][] = 1;
                $total_weight[$value['store_id']][] = $value['subtotal_weight'];
            }
        }
        if($total){
            foreach($total as $key=>$value){
                $total[$key] = array_sum($value);
                $total_weight[$key] = array_sum($total_weight[$key]);
                $store_free_shipping[$key] = array_sum($store_free_shipping[$key]);
            }
        }
        $aCart['subtotal_store_good_price'] = $total;
        $aCart['store_free_shipping'] = $store_free_shipping;
        $aCart['subtotal_weight_store'] = $total_weight;
    }

}