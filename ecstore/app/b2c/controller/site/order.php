<?php
 

class b2c_ctl_site_order extends b2c_frontpage{

    var $noCache = true;

    public function __construct(&$app){
        parent::__construct($app);
        $shopname = $app->getConf('system.shopname');
        if(isset($sysconfig)){
            $this->title = app::get('b2c')->_('订单').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('订单_').'_'.$shopname;
            $this->description = app::get('b2c')->_('订单_').'_'.$shopname;
        }
        $this->header .= '<meta name="robots" content="noindex,noarchive,nofollow" />';
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->title=app::get('b2c')->_('订单中心');
        $this->objMath = kernel::single("ectools_math");
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
        $db = kernel::database();
        $transaction_status = $db->beginTransaction();

        $this->mCart = $this->app->model('cart');
        $aCart = $this->mCart->get_objects(true);

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
        if (!$_POST['delivery']['ship_area'] || !$_POST['delivery']['ship_addr_area'] || !$_POST['delivery']['ship_addr'] || !$_POST['delivery']['ship_name'] || (!$_POST['delivery']['ship_email'] && !$arrMember['member_id']) || (!$_POST['delivery']['ship_mobile'] && !$_POST['delivery']['ship_tel']) || !$_POST['delivery']['shipping_id'] || !$_POST['payment']['pay_app_id'])
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

            if (!$_POST['delivery']['shipping_id'])
            {
                $msg .= app::get('b2c')->_("配送方式不能为空！")."<br />";
            }

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
        $obj_order_create = kernel::single("b2c_order_create");
        // 加入订单能否生成的判断
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if ($obj_checkorder)
        {
            if (!$obj_checkorder->check_create($aCart, $_POST['delivery']['ship_area'], $message))
                $this->end(false, $message);
        }
        $order_data = $obj_order_create->generate($_POST,'',$msg,$aCart);
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
            $this->cart_model = &$this->app->model('cart_objects');
            $this->cart_model->remove_object();

            // 生成cookie有效性的验证信息
            setcookie('ST_ShopEx-Order-Buy', md5($this->app->getConf('certificate.token').$order_id));
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

            $this->end(true, $this->app->_("订单生成成功！"), $this->gen_url(array('app'=>'b2c','ctl'=>'site_order','act'=>'index','arg0'=>$order_id)),'',true);
        }
        else
            $this->end(false, $msg, $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')),true,true);
    }

    public function index($orderStr, $selecttype=false)
    {
        $objOrder = &$this->app->model('orders');
        $aOrders = $objOrder->getList('*', array('order_id'=>explode('|', base64_decode($orderStr))));

        if(empty($aOrders)){
            $this->begin();
            $this->end(false,  app::get('b2c')->_('订单无效！'), $this->gen_url(array('app'=>'site','ctl'=>'default','act'=>'index')));
        }
        foreach ($aOrders as $key => $sdf) {
            // 校验订单的会员有效性.
            $is_verified = ($this->_check_verify_member($sdf['member_id'])) ? $this->_check_verify_member($sdf['member_id']) : false;

            // 校验订单的有效性.
            if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token').$orderStr))
            {
                $this->begin();
                $this->end(false,  app::get('b2c')->_('订单无效！'), $this->gen_url(array('app'=>'site','ctl'=>'default','act'=>'index')));

            }

            $sdf['cur_money'] = $this->objMath->number_minus(array($sdf['cur_amount'], $sdf['payed']));
        }

        $this->pagedata['order'] = $aOrders;

        // if($selecttype){
        //     $selecttype = 1;
        // }else{
        //     $selecttype = 0;
        // }
        // $this->pagedata['order']['selecttype'] = $selecttype;

        // $opayment = app::get('ectools')->model('payment_cfgs');
        // $this->pagedata['payments'] = $opayment->getListByCode($aOrders[0]['currency']);
        // $system_money_decimals = $this->app->getConf('system.money.decimals');
        // $system_money_operation_carryset = $this->app->getConf('system.money.operation.carryset');
        // foreach ($this->pagedata['payments'] as $key=>&$arrPayments)
        // {
        //     if (!$sdf['member_id'])
        //     {
        //         if (trim($arrPayments['app_id']) == 'deposit')
        //         {
        //             unset($this->pagedata['payments'][$key]);
        //             continue;
        //         }
        //     }

        //     if ($arrPayments['app_id'] == $this->pagedata['order']['payinfo']['pay_app_id'])
        //     {
        //         $this->pagedata['order']['payinfo']['pay_name'] = $arrPayments['app_name'];
        //         $this->pagedata['order']['payinfo']['pay_des'] = $arrPayments['app_des'];
        //         $arrPayments['cur_money'] = $this->objMath->formatNumber($this->pagedata['order']['cur_money'], $system_money_decimals, $system_money_operation_carryset);
        //         $arrPayments['total_amount'] = $this->objMath->formatNumber($this->pagedata['order']['total_amount'], $system_money_decimals, $system_money_operation_carryset);
        //     }
        //     else
        //     {
        //         $arrPayments['cur_money'] = $this->pagedata['order']['cur_money'];
        //         $cur_discount = $this->objMath->number_multiple(array($sdf['discount'], $this->pagedata['order']['cur_rate']));
        //         if ($this->pagedata['order']['payinfo']['cost_payment'] > 0)
        //         {
        //             if ($this->pagedata['order']['cur_money'] > 0)
        //                 $cost_payments_rate = $this->objMath->number_div(array($arrPayments['cur_money'], $this->objMath->number_plus(array($this->pagedata['order']['cur_money'], $this->pagedata['order']['payed']))));
        //             else
        //                 $cost_payments_rate = 0;
        //             $cost_payment = $this->objMath->number_multiple(array($this->objMath->number_multiple(array($this->pagedata['order']['payinfo']['cost_payment'], $this->pagedata['order']['cur_rate'])), $cost_payments_rate));

        //             $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
        //             $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cost_payment));
        //             $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $this->objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']))));
        //             $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
        //         }
        //         else
        //         {
        //             $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
        //             $cost_payment = $this->objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']));
        //             $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cost_payment));
        //             $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
        //         }

        //         $arrPayments['total_amount'] = $this->objMath->formatNumber($this->objMath->number_div(array($arrPayments['cur_money'], $this->pagedata['order']['cur_rate'])), $system_money_decimals, $system_money_operation_carryset);
        //         $arrPayments['cur_money'] = $this->objMath->formatNumber($arrPayments['cur_money'], $system_money_decimals, $system_money_operation_carryset);;
        //     }
        // }

        // if ($this->pagedata['order']['payinfo']['pay_app_id'] == '-1')
        // {
        //     $this->pagedata['order']['payinfo']['pay_name'] = app::get('b2c')->_('货到付款');
        // }

        // $objCur = app::get('ectools')->model('currency');
        // $aCur = $objCur->getFormat($this->pagedata['order']['currency']);
        // $this->pagedata['order']['cur_def'] = $aCur['sign'];

        // $this->pagedata['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'result'));
        // $this->pagedata['res_url'] = $this->app->res_url;
        // $this->pagedata['form_action'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'dopayment','arg0'=>'order'));
        // $obj_order_payment_html = kernel::servicelist('b2c.order.pay_html');
        // $app_id = 'b2c';
        // if ($obj_order_payment_html)
        // {
        //     foreach ($obj_order_payment_html as $obj)
        //     {
        //         $obj->gen_data($this, $app_id);
        //     }
        // }
        $this->set_tmpl('order_index');
        $this->page('site/order/index.html', false, $app_id);
    }

    public function detail($order_id, $selecttype=false)
    {
        $objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $sdf = $objOrder->dump($order_id, '*', $subsdf);
        $this->objMath = kernel::single("ectools_math");

        // 校验订单的会员有效性.
        $is_verified = ($this->_check_verify_member($sdf['member_id'])) ? $this->_check_verify_member($sdf['member_id']) : false;

        // 校验订单的有效性.
        // if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token').$order_id))
        // {
        //     $this->begin();
        //     $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        // }

       $order_items = array();

        if ($sdf['payinfo']['pay_app_id'] == '-1')
        {
            $sdf['payinfo']['pay_key'] = '-1';
        }
        else if ($sdf['payinfo']['pay_app_id'] == 'offline')
        {
            $sdf['payinfo']['pay_key'] = 'OFFLINE';
        }
        else if ($sdf['payinfo']['pay_app_id'] == 'deposit')
        {
            $sdf['payinfo']['pay_key'] = 'DEPOSIT';
        }
        else
        {
            $sdf['payinfo']['pay_key'] = $sdf['payinfo']['pay_app_id'];
        }

        if(!$sdf){
            $this->_response->set_http_response_code(404);
            $this->_response->set_body(app::get('b2c')->_('订单号：') . $order_id . app::get('b2c')->_('不存在！'));
            return;
        }

        $sdf['cur_money'] = $this->objMath->number_minus(array($sdf['cur_amount'], $sdf['payed']));

        $arrMember = $this->get_current_member();

        if (!$sdf['consignee']['email'] && $arrMember['member_id'])
            $sdf['consignee']['email'] = $arrMember['email'];


        // 订单的相关信息的修改
        $obj_other_info = kernel::servicelist('b2c.order_other_infomation');
        if ($obj_other_info)
        {
            foreach ($obj_other_info as $obj)
            {
                $this->pagedata['discount_html'] = $obj->gen_point_discount($sdf);
            }
        }
        $this->pagedata['order'] = $sdf;
        /** 去掉商品优惠 **/
        if ($this->pagedata['order']['order_pmt'])
        {
            foreach ($this->pagedata['order']['order_pmt'] as $key=>$arr_pmt)
            {
                if ($arr_pmt['pmt_type'] == 'goods')
                {
                    unset($this->pagedata['order']['order_pmt'][$key]);
                }
            }
        }
        /** end **/

        if($selecttype){
            $selecttype = 1;
        }else{
            $selecttype = 0;
        }
        $this->pagedata['order']['selecttype'] = $selecttype;

        $system_money_decimals = $this->app->getConf('system.money.decimals');
        $system_money_operation_carryset = $this->app->getConf('system.money.operation.carryset');
        if ($this->pagedata['order']['payinfo']['pay_key'] != '-1')
        {
            $opayment = app::get('ectools')->model('payment_cfgs');
            $this->pagedata['payments'] = $opayment->getListByCode($sdf['currency']);
            foreach ($this->pagedata['payments'] as &$arrPayments)
            {
                if (!$arrMember['member_id'])
                {
                    if (trim($arrPayments['app_id']) == 'deposit')
                    {
                        unset($arrPayments);
                        continue;
                    }
                }

                if ($arrPayments['app_id'] == $this->pagedata['order']['payinfo']['pay_app_id'])
                    $this->pagedata['order']['payinfo']['pay_name'] = $arrPayments['app_name'];

                if ($arrPayments['app_id'] == $this->pagedata['order']['payinfo']['pay_app_id'])
                {
                    $arrPayments['cur_money'] = $this->objMath->formatNumber($this->pagedata['order']['cur_money'], $system_money_decimals, $system_money_operation_carryset);
                    $arrPayments['total_amount'] = $this->objMath->formatNumber($this->pagedata['order']['total_amount'], $system_money_decimals, $system_money_operation_carryset);
                }
                else
                {
                    $arrPayments['cur_money'] = $this->pagedata['order']['cur_money'];
                    $cur_discount = $this->objMath->number_multiple(array($sdf['discount'], $this->pagedata['order']['cur_rate']));
                    if ($this->pagedata['order']['payinfo']['cost_payment'] > 0)
                    {
                        if ($this->pagedata['order']['cur_money'] > 0)
                            $cost_payments_rate = $this->objMath->number_div(array($arrPayments['cur_money'], $this->objMath->number_plus(array($this->pagedata['order']['cur_money'], $this->pagedata['order']['payed']))));
                        else
                            $cost_payments_rate = 0;
                        $cost_payment = $this->objMath->number_multiple(array($this->objMath->number_multiple(array($this->pagedata['order']['payinfo']['cost_payment'], $this->pagedata['order']['cur_rate'])), $cost_payments_rate));
                        $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
                        $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cost_payment));
                        $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $this->objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']))));
                        $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
                    }
                    else
                    {
                        $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
                        $cost_payment = $this->objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']));
                        $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cost_payment));
                        $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
                    }

                    $arrPayments['total_amount'] = $this->objMath->formatNumber($this->objMath->number_div(array($arrPayments['cur_money'], $this->pagedata['order']['cur_rate'])), $system_money_decimals, $system_money_operation_carryset);
                    $arrPayments['cur_money'] = $this->objMath->formatNumber($arrPayments['cur_money'], $system_money_decimals, $system_money_operation_carryset);
                }
            }
        }
        else
        {
            $this->pagedata['order']['payinfo']['pay_name'] = app::get('b2c')->_('货到付款');
        }

        $objCur = app::get('ectools')->model('currency');
        $aCur = $objCur->getFormat($this->pagedata['order']['currency']);
        $this->pagedata['order']['cur_def'] = $aCur['sign'];
        $aCur = $objCur->getcur($this->pagedata['order']['currency']);
        $this->pagedata['order']['currency'] = $aCur['cur_code'];

        // 生成所有的items
        $this->objMath = kernel::single('ectools_math');

        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

        $obj_specification = $this->app->model('specification');
        $obj_spec_values = $this->app->model('spec_values');

        foreach ($this->pagedata['order']['order_objects'] as $k=>$arrOdr_object)
        {
            $index = 0;
            $index_adj = 0;
            $index_gift = 0;
            $image_set = app::get('image')->getConf('image.set');
            if ($arrOdr_object['obj_type'] == 'goods')
            {
                foreach($arrOdr_object['order_items'] as $key => $item)
                {
                    if (!$item['products'])
                    {
                        $o = $this->app->model('order_items');
                        $tmp = $o->getList('*', array('item_id'=>$item['item_id']));
                        $item['products']['product_id'] = $tmp[0]['product_id'];
                    }

                    $arrGoods = array();
                    if ($item['item_type'] != 'gift')
                    {
                        if ($item['item_type'] == 'product')
                        {
                            if ($arr_service_goods_type_obj['goods'])
                            {
                                $str_service_goods_type_obj = $arr_service_goods_type_obj['goods'];
                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $item['goods_id'],'product_id'=>$item['products']['product_id']), $arrGoods,'order_detail');
                            }

                            $gItems[$k]['addon'] = unserialize($item['addon']);
                            if($item['addon'] && unserialize($item['addon'])){
                                $arr_addon = unserialize($item['addon']);
                                if ($arr_addon['product_attr'])
                                    unset($arr_addon['product_attr']);
                                $gItems[$k]['minfo'] = $arr_addon;
                            }else{
                                $gItems[$k]['minfo'] = array();
                            }

                            if (!$arrGoods['image_default_id'])
                            {
                                $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                            }

                            $order_items[$k]['product'] = $item;
                            $order_items[$k]['product']['score'] = intval($order_items[$k]['product']['score']*$order_items[$k]['product']['quantity']);
                            $order_items[$k]['product']['thumbnail_pic'] = $arrGoods['image_default_id'];
                            $order_items[$k]['product']['is_type'] = $arrOdr_object['obj_type'];
                            $order_items[$k]['product']['item_type'] = $arrGoods['category']['cat_name'];
                            $order_items[$k]['product']['minfo'] = $gItems[$k]['minfo'];
                            $order_items[$k]['product']['link_url'] = $arrGoods['link_url'];

                            $order_items[$k]['product']['name'] = $item['name'];

                            if ($gItems[$k]['addon']['product_attr'])
                            {
								$order_items[$k]['product']['attr'] .= '(';
								foreach ($gItems[$k]['addon']['product_attr'] as $arr_product_attr)
                                {
                                    $order_items[$k]['product']['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                }
								if (strpos($order_items[$k]['product']['attr'], $this->app->_(" ")) !== false)
								{
									$order_items[$k]['product']['attr'] = substr($order_items[$k]['product']['attr'], 0, strrpos($order_items[$k]['product']['attr'], $this->app->_(" ")));
								}
								$order_items[$k]['product']['attr'] .= ')';
                            }
                        }
                        else
                        {
                            if ($arr_service_goods_type_obj['adjunct'])
                            {
                                $str_service_goods_type_obj = $arr_service_goods_type_obj['adjunct'];
                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $item['goods_id'],'product_id'=>$item['products']['product_id']), $arrGoods,'order_detail');
                            }

                            if (!$arrGoods['image_default_id'])
                            {
                                $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                            }

                            $order_items[$k]['adjunct'][$index_adj] = $item;
                            $order_items[$k]['adjunct'][$index_adj]['score'] = intval($order_items[$k]['adjunct'][$index_adj]['score']*$order_items[$k]['adjunct'][$index_adj]['quantity']);
                            $order_items[$k]['adjunct'][$index_adj]['thumbnail_pic'] = $arrGoods['image_default_id'];
                            $order_items[$k]['adjunct'][$index_adj]['is_type'] = $arrOdr_object['obj_type'];
                            $order_items[$k]['adjunct'][$index_adj]['item_type'] = $arrGoods['category']['cat_name'];
                            $order_items[$k]['adjunct'][$index_adj]['link_url'] = $arrGoods['link_url'];

                            $order_items[$k]['adjunct'][$index_adj]['name'] = $item['name'];

                            if ($item['addon'])
                            {
                                $arr_addon = unserialize($item['addon']);

                                if ($arr_addon['product_attr'])
                                {
									$order_items[$k]['adjunct'][$index_adj]['attr'] .= '(';
									foreach ($arr_addon['product_attr'] as $arr_product_attr)
									{
										$order_items[$k]['adjunct'][$index_adj]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
									}
									if (strpos($order_items[$k]['adjunct'][$index_adj]['attr'], $this->app->_(" ")) !== false)
									{
										$order_items[$k]['adjunct'][$index_adj]['attr'] = substr($order_items[$k]['adjunct'][$index_adj]['attr'], 0, strrpos($order_items[$k]['adjunct'][$index_adj]['attr'], $this->app->_(" ")));
									}
									$order_items[$k]['adjunct'][$index_adj]['attr'] .= ')';
                                }
                            }

                            $index_adj++;
                        }
                    }
                    else
                    {
                        if ($arr_service_goods_type_obj['gift'])
                        {
                            $str_service_goods_type_obj = $arr_service_goods_type_obj['gift'];
                            $str_service_goods_type_obj->get_order_object(array('goods_id' => $item['goods_id'],'product_id'=>$item['products']['product_id']), $arrGoods,'order_detail');

                            if (!$arrGoods['image_default_id'])
                            {
                                $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                            }

                            $order_items[$k]['gifts'][$index_gift] = $item;
                            $order_items[$k]['gifts'][$index_gift]['score'] = intval($order_items[$k]['gifts'][$index_gift]['score']*$order_items[$k]['gifts'][$index_gift]['quantity']);
                            $order_items[$k]['gifts'][$index_gift]['thumbnail_pic'] = $arrGoods['image_default_id'];
                            $order_items[$k]['gifts'][$index_gift]['is_type'] = $arrOdr_object['obj_type'];
                            $order_items[$k]['gifts'][$index_gift]['item_type'] = $arrGoods['category']['cat_name'];
                            $order_items[$k]['gifts'][$index_gift]['link_url'] = $arrGoods['link_url'];

                            $order_items[$k]['gifts'][$index_gift]['name'] = $item['name'];
                            if ($item['addon'])
                            {
                                $arr_addon = unserialize($item['addon']);

                                if ($arr_addon['product_attr'])
                                {
                                    $order_items[$k]['gifts'][$index_gift]['attr'] .= '(';

                                    foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                    {
                                        $order_items[$k]['gifts'][$index_gift]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                    }

                                    if (strpos($order_items[$k]['gifts'][$index_gift]['attr'], $this->app->_(" ")) !== false)
                                    {
                                        $order_items[$k]['gifts'][$index_gift]['attr'] = substr($order_items[$k]['gifts'][$index_gift]['attr'], 0, strrpos($order_items[$k]['gifts'][$index_gift]['attr'], $this->app->_(" ")));
                                    }

                                    $order_items[$k]['gifts'][$index_gift]['attr'] .= ')';
                                }
                            }

                            $index_gift++;
                        }
                    }
                }
            }
            else
            {
                if ($arrOdr_object['obj_type'] == 'gift')
                {
                    if ($arr_service_goods_type_obj['gift'])
                    {
                        $str_service_goods_type_obj = $arr_service_goods_type_obj['gift'];
                        foreach ($arrOdr_object['order_items'] as $gift_key => $gift_item)
                        {
                            if (isset($gift_items[$gift_item['goods_id']]) && $gift_items[$gift_item['goods_id']])
                                $gift_items[$gift_item['goods_id']]['nums'] = $this->objMath->number_plus(array($gift_items[$gift_item['goods_id']]['nums'], $gift_item['quantity']));
                            else
                            {
                                if (!$gift_item['products'])
                                {
                                    $o = $this->app->model('order_items');
                                    $tmp = $o->getList('*', array('item_id'=>$gift_item['item_id']));
                                    $gift_item['products']['product_id'] = $tmp[0]['product_id'];
                                }

                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $gift_item['goods_id'], 'product_id'=>$gift_item['products']['product_id']), $arrGoods,'order_detail');

                                if (!isset($gift_item['products']['product_id']) || !$gift_item['products']['product_id'])
                                    $gift_item['products']['product_id'] = $gift_item['goods_id'];

                                if (!$arrGoods['image_default_id'])
                                {
                                    $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                }

                                $gift_name = $gift_item['name'];
                                if ($item['addon'])
                                {
                                    $arr_addon = unserialize($item['addon']);

                                    if ($arr_addon['product_attr'])
                                    {
                                        $gift_attr .= '(';

                                        foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                        {
                                            $gift_attr .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                        }

                                        if (strpos($gift_attr, $this->app->_(" ")) !== false)
                                        {
                                            $gift_attr = substr($gift_attr, 0, strrpos($gift_attr, $this->app->_(" ")));
                                        }

                                        $gift_attr .= ')';
                                    }
                                }

                                $gift_items[$gift_item['products']['product_id']] = array(
                                    'goods_id' => $gift_item['goods_id'],
                                    'bn' => $gift_item['bn'],
                                    'nums' => $gift_item['quantity'],
                                    'name' => $gift_name,
									'attr' => $gift_attr ? $gift_attr : '',
                                    'item_type' => $arrGoods['category']['cat_name'],
                                    'price' => $gift_item['price'],
                                    'quantity' => $gift_item['quantity'],
                                    'sendnum' => $gift_item['sendnum'],
                                    'thumbnail_pic' => $arrGoods['image_default_id'],
                                    'is_type' => $arrOdr_object['obj_type'],
                                    'amount' => $gift_item['amount'],
                                    'score' => intval($gift_item['score']*$gift_item['quantity']),
                                    'link_url' => $arrGoods['link_url'],
                                );
                            }
                        }
                    }
                }
                else
                {
                    if ($arr_service_goods_type_obj[$arrOdr_object['obj_type']])
                    {
                        $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrOdr_object['obj_type']];
                        $extend_items[] = $str_service_goods_type_obj->get_order_object($arrOdr_object, $arr_Goods,'order_detail');
                    }
                }
            }
        }

        if (isset($this->pagedata['order']['order_pmt']) && $this->pagedata['order']['order_pmt'])
        {
            foreach ($this->pagedata['order']['order_pmt'] as &$arr_order_pmt)
            {
                if ($arr_order_pmt['pmt_type'] == 'coupon')
                {
                    $this->pagedata['order']['coupon_p'][] = $arr_order_pmt;
                    unset($arr_order_pmt);
                }
            }
        }

        /** 是否开启配送时间的限制 */
        $this->pagedata['site_checkout_receivermore_open'] = $this->app->getConf('site.checkout.receivermore.open');

        $this->pagedata['order']['order_items'] = $order_items;
        $this->pagedata['order']['gifts'] = $gift_items;
        $this->pagedata['order']['extends'] = $extend_items;

        $this->pagedata['order']['cost_item'] = $this->objMath->number_minus(array($this->pagedata['order']["cost_item"], $this->pagedata['aCart']['discount_amount_prefilter']));

        $this->pagedata['return_url'] = $this->app->router()->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'result'));
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['form_action'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'dopayment','arg0'=>'order'));
        $obj_order_payment_html = kernel::servicelist('b2c.order.pay_html');
        $app_id = 'b2c';
        if ($obj_order_payment_html)
        {
            foreach ($obj_order_payment_html as $obj)
            {
                $obj->gen_data($this, $app_id);
            }
        }
        $this->set_tmpl('order_detail');
        // 订单详细增加html埋点
        foreach( kernel::servicelist('b2c.order_add_html') as $services ) {
        	if ( is_object($services) ) {
        		if ( method_exists($services, 'fetchHtml') ) {
        			$services->fetchHtml($this,$order_id,'site/invoice_detail.html');
        		}
        	}
        }
        $this->page('site/order/detail.html',false,$app_id);
    }

}
