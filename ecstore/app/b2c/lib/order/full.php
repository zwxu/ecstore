<?php

 
//订单全部信息，匹配矩阵接口 store.trade.add
class b2c_order_full{

    public function __construct($app)
    {
        $this->app = &$app;
        $this->objMath = kernel::single('ectools_math');
    }

    public function get($order_id)
    {
        if( $order_id == '' )
            return array();

        $obj_orders = $this->app->model('orders');

        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $sdf = $obj_orders->dump($order_id, '*', $subsdf);

        $arr_data = array();






		/**
		 * 支付状态数组
		 */

		$arr_pay_status = array(
			'0'=>'PAY_NO',
			'1'=>'PAY_FINISH',
			'2'=>'PAY_TO_MEDIUM',
			'3'=>'PAY_PART',
			'4'=>'REFUND_PART',
			'5'=>'REFUND_ALL',
            );
        $arr_data['tid'] = $sdf['order_id'];
        $arr_data['title'] = 'Order Create';
        $arr_data['created'] = date('Y-m-d H:i:s', $sdf['createtime']);
        $arr_data['modified'] = date('Y-m-d H:i:s', $sdf['last_modified']);
        $arr_data['lastmodify'] = date('Y-m-d H:i:s', $sdf['last_modified']);
        $arr_data['status'] = ($sdf['status'] == 'active') ? 'TRADE_ACTIVE' : 'TRADE_CLOSED';
        $arr_data['pay_status'] = ($sdf['pay_status'] == '0' || !$sdf['pay_status']) ? 'PAY_NO' : $arr_pay_status[$sdf['pay_status']];
        $arr_data['ship_status'] = ($sdf['ship_status'] == '0' || !$sdf['ship_status']) ? 'SHIP_NO' : 'SHIP_FINISH';        
        $arr_data['has_invoice'] = ($sdf['is_tax'] == 'true' || $sdf['is_tax'] === true) ? true : false;
        $arr_data['invoice_title'] = $sdf['tax_title'] ? $sdf['tax_title'] : '';
        $arr_data['invoice_fee'] = $sdf['cost_tax'];
        $arr_data['total_goods_fee'] = $this->objMath->number_plus(array($sdf['cost_item'], $sdf['pmt_goods']));
        $arr_data['total_trade_fee'] = $sdf['total_amount'];
        $arr_data['discount_fee'] = $sdf['discount'];
        $arr_data['goods_discount_fee'] = $sdf['pmt_goods'];
        $arr_data['orders_discount_fee'] = $sdf['pmt_order'];
		$arr_promotion_details = array();
		if ($sdf['order_pmt'])
		{
			foreach ($sdf['order_pmt'] as $arr_order_pmt)
			{
				$arr_promotion_details[] = array(
					'promotion_name'=>$arr_order_pmt['pmt_describe'],
					'promotion_fee'=>$arr_order_pmt['pmt_amount'],
                    );
			}
		}
		if ($sdf['addon'])
		{
			if ($sdf['addon']['order_chgpointmoney'] > 0)
			{
				$arr_data['goods_discount_fee'] = $this->objMath->number_plus(array($arr_data['goods_discount_fee'], $sdf['addon']['order_chgpointmoney']));
				$arr_promotion_details[] = array(
					'promotion_name'=>app::get('b2c')->_('订单积分折扣优惠'),
					'promotion_fee'=>$sdf['addon']['order_chgpointmoney'],
                    );
			}
		}
		if ($arr_promotion_details)
			$arr_data['promotion_details'] = json_encode($arr_promotion_details);
        $arr_data['payed_fee'] = $sdf['payed'];
        $arr_data['currency'] = $sdf['currency'];
        $arr_data['currency_rate'] = $sdf['cur_rate'];
        $arr_data['total_currency_fee'] = $sdf['cur_amount'];
        $arr_data['buyer_obtain_point_fee'] = $sdf['score_g'];
        $arr_data['point_fee'] = $sdf['score_u'];
        $arr_data['total_weight'] = $sdf['weight'];
        $arr_data['receiver_time'] = $sdf['consignee']['r_time'] ? $sdf['consignee']['r_time'] : '';
        $arr_data['shipping_tid'] = $sdf['shipping']['shipping_id'];
        $arr_data['shipping_type'] = $sdf['shipping']['shipping_name'];
        $arr_data['shipping_fee'] = $sdf['shipping']['cost_shipping'];
        $arr_data['is_protect'] = $sdf['shipping']['is_protect'];
        $arr_data['protect_fee'] = $sdf['shipping']['cost_protect'];
        $opayment = app::get('ectools')->model('payment_cfgs');
        $arr_payment = $opayment->getPaymentInfo($sdf['payinfo']['pay_app_id']);
        $arr_data['payment_tid'] = $arr_payment['app_rpc_id'];
        $arr_data['payment_type'] = $arr_payment['app_display_name'];
        $arr_data['is_cod'] = $sdf['payinfo']['pay_app_id'] == '-1' ? 'true' : 'false';
        $arr_data['receiver_name'] = $sdf['consignee']['name'];
        $arr_data['receiver_email'] = $sdf['consignee']['email'] ? $sdf['consignee']['email'] : '';
        $arr_data['receiver_mobile'] = $sdf['consignee']['mobile'];
        $arr_states = explode(':', $sdf['consignee']['area']);
        $str_states = $arr_states[1];
        $arr_states = explode('/', $str_states);
        $arr_data['receiver_state'] = trim($arr_states[0]);
        $arr_data['receiver_city'] = trim($arr_states[1]);
        $arr_data['receiver_district'] = trim($arr_states[2]);
        $arr_data['receiver_address'] = $sdf['consignee']['addr'];
        $arr_data['receiver_zip'] = $sdf['consignee']['zip'] ? $sdf['consignee']['zip'] : '';
        $arr_data['receiver_phone'] = $sdf['consignee']['telephone'] ? $sdf['consignee']['telephone'] : '';
        $arr_data['pay_cost'] = $sdf['payinfo']['cost_payment'];
        $arr_data['buyer_memo'] = $sdf['memo'] ? $sdf['memo'] : '';
        $arr_data['orders_number'] = 1;
        // 取到用户信息
        if ($sdf['member_id'])
        {
            $objMember = $this->app->model('members');
            $aMember = $objMember->dump($sdf['member_id'], '*', array(':account@pam'=>'*'));
            $arr_data['buyer_uname'] = $aMember['pam_account']['login_name'];
            $arr_data['buyer_name'] = $aMember['contact']['name'] ? $aMember['contact']['name'] : '';
            if ($aMember['contact']['area'] && strpos($aMember['contact']['area'], ':') !== false)
            {
                $arr_area = explode(':', $aMember['contact']['area']);
                $arr_district = explode('/', $arr_area[1]);
                $arr_data['buyer_state'] = $arr_district[0];
                $arr_data['buyer_city'] = $arr_district[1];
                $arr_data['buyer_district'] = $arr_district[2];
            }
            $arr_data['buyer_address'] = $aMember['contact']['addr'];
            $arr_data['buyer_mobile'] = $aMember['contact']['phone']['mobile'] ? $aMember['contact']['phone']['mobile'] : '';
            $arr_data['buyer_phone'] = $aMember['contact']['phone']['telephone'] ? $aMember['contact']['phone']['telephone'] : '';
            $arr_data['buyer_email'] = $aMember['contact']['email'];
            $arr_data['buyer_zip'] = $aMember['contact']['zipcode'];
        }
        else
        {
            $arr_data['buyer_uname'] = '';
            $arr_data['buyer_name'] = '';
            $arr_data['buyer_state'] = '';
            $arr_data['buyer_city'] = '';
            $arr_data['buyer_district'] = '';
            $arr_data['buyer_address'] = '';
            $arr_data['buyer_mobile'] = '';
            $arr_data['buyer_phone'] = '';
            $arr_data['buyer_email'] = '';
            $arr_data['buyer_zip'] = '';
        }


        //子订单信息
        $index = 0;
        foreach ($sdf['order_objects'] as $odr_obj)
        {
            $item_total_price = 0;
            $arr_data['orders']['order'][$index] = array(
                'oid' => $odr_obj['obj_id'],
				'orders_bn'=> $odr_obj['bn'],
                'type' => ($odr_obj['obj_type'] == 'goods') ? 'goods' : $odr_obj['obj_type'],
                'type_alias' => $odr_obj['obj_alias'],
                'iid' => $odr_obj['obj_id'],
                'title' => $odr_obj['name'],
                'items_num' => intval($odr_obj['quantity']),
                'order_status' => 'SHIP_NO',
				'price' => $odr_obj['price'],
                'total_order_fee' => $this->objMath->number_multiple(array($odr_obj['price'], $odr_obj['quantity'])),
                'discount_fee' => 0,
                'consign_time' => '',
                'order_items' => array('item' => array()),
                'weight' => $odr_obj['weight'],
                'discount_fee'=>0,//商品优惠金额
                );
            $total_price = 0;
            foreach ($odr_obj['order_items'] as $odr_item)
            {
                $arr_addon = array();
                $str_property = "";
                $item_total_price+=$odr_item['quantity']*$odr_item['price'];
                if ($odr_item['addon'])
                {
                    $arr_addon = unserialize($odr_item['addon']);
                    
                    if ($arr_addon['product_attr'])
                        foreach ($arr_addon['product_attr'] as $arr_product_attr)
                        {
                            $str_property .= $arr_product_attr['label'] . ":" . $arr_product_attr['value'] . ";";
                        }
                        
                    if ($str_property && strpos($str_property, ";") !== false)
                    {
                        $str_property = substr($str_property, 0, strrpos($str_property, ";"));
                    }
                }
                
                $arr_data['orders']['order'][$index]['order_items']['item'][] = array(
                    'sku_id' => $odr_item['products']['product_id'],
                    'iid' => $odr_item['goods_id'],
                    'bn' => $odr_item['bn'],
                    'name' => $odr_item['name'],
                    'sku_properties' => $str_property,
                    'weight' => $odr_item['weight'],
                    'score' => $odr_item['score'],
                    'price' => $odr_item['g_price'],
                    'num' => $odr_item['quantity'],
                    'sendnum' => $odr_item['sendnum'],
                    'total_item_fee' => $odr_item['g_price']*$odr_item['quantity'],
                    'sale_price'=> $odr_item['amount'],
                    'item_type' => $odr_item['item_type'],
					'item_status'=>'normal',
                    'discount_fee'=>($odr_item['g_price']-$odr_item['price'])*$odr_item['quantity'],//商品优惠金额
                    );
                $total_price+= $this->objMath->number_multiple(array($odr_item['g_price'], $odr_item['quantity']));
            }
            if($odr_obj['obj_type']=='giftpackage'){
                $arr_data['orders']['order'][$index]['total_order_fee']= $odr_obj['price'];//test giftpackage
            }else{
                $arr_data['orders']['order'][$index]['total_order_fee']= $total_price;//test giftpackage
            }
            $index++;
        }


        //支付单信息
        $payments_status = array(
            'succ' => 'SUCC',
            'failed' => 'FAILED',
            'cancel' => 'CANCEL',
            'error' => 'ERROR',
            'invalid' => 'INVALID',
            'progress' => 'PROGRESS',
            'timeout' => 'TIMEOUT',
            'ready' => 'READY',
            );


        //关联支付单
        $arr_data['payment_list'] = array();
        $obj_order_bill = app::get('ectools')->model('order_bills');
        $obj_payments = app::get('ectools')->model('payments');
        $bill_list = $obj_order_bill->getList('*', array('rel_id'=>$sdf['order_id'], 'bill_type'=>'payments', 'pay_object'=>'order'));
        if( $bill_list )
        {
            $index = 0;
            foreach( $bill_list as $row )
            {
                $payment_info = $obj_payments->getList('*', array('payment_id' => $row['bill_id']));
                if( $payment_info[0] )
                {
                    $payment_info = $payment_info[0];
                    $arr_data['payment_list'][$index]['tid'] = $sdf['order_id'];
                    $arr_data['payment_list'][$index]['payment_id'] = $payment_info['payment_id'];
                    $arr_data['payment_list'][$index]['seller_bank'] = $payment_info['bank'];
                    $arr_data['payment_list'][$index]['seller_account'] = $payment_info['account'];
                    $arr_data['payment_list'][$index]['buyer_account'] = $payment_info['pay_account'];
                    $arr_data['payment_list'][$index]['currency'] = $payment_info['currency'];
                    $arr_data['payment_list'][$index]['pay_fee'] = $payment_info['money'];
                    $arr_data['payment_list'][$index]['paycost'] = $payment_info['paycost'];
                    $arr_data['payment_list'][$index]['currency_fee'] = $payment_info['cur_money'];
                    $arr_data['payment_list'][$index]['pay_type'] = ($payment_info['pay_app_id'] == 'deposit') ? 'deposit' : $payment_info['pay_type'];
                    $arr_data['payment_list'][$index]['payment_name'] = $payment_info['pay_name'];
                    $arr_data['payment_list'][$index]['payment_code'] = $payment_info['pay_app_id'];
                    $arr_data['payment_list'][$index]['t_begin'] = date('Y-m-d H:i:s', $payment_info['t_begin']);
                    $arr_data['payment_list'][$index]['t_end'] = date('Y-m-d H:i:s', $payment_info['t_payed']);
                    $arr_data['payment_list'][$index]['status'] = $payments_status[$payment_info['status']];
                    $arr_data['payment_list'][$index]['memo'] = $payment_info['memo'];
                    $arr_data['payment_list'][$index]['outer_no'] = $payment_info['trade_no'];
                    
                    $index++;
                }
            }
        }

        $arr_data['orders'] = json_encode($arr_data['orders']);
        //$arr_data['payment_list'] = json_encode($arr_data['payment_list']);
        $arr_data['payment_lists'] = json_encode( array( 'payment_list' => $arr_data['payment_list'] ) );
        unset($arr_data['payment_list']);
    
        return $arr_data;
    }

}