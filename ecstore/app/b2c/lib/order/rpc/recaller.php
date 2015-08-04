<?php


class b2c_order_rpc_recaller extends b2c_api_rpc_request implements b2c_api_rpc_request_interface{
    
    public function rpc_caller_request(&$sdf,$method='pay')
    {
        if ($sdf)
            $this->request($sdf);
            
        return true;
    }
    
    /**
     * 订单创建
     * @param array sdf
     * @return boolean success or failure
     */
    protected function request(&$sdf)
    {
		$obj_math = kernel::single('ectools_math');
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
        $arr_data['status'] = ($sdf['status'] == 'active') ? 'TRADE_ACTIVE' : 'TRADE_CLOSED';
        $arr_data['pay_status'] = ($sdf['pay_status'] == '0' || !$sdf['pay_status']) ? 'PAY_NO' : $arr_pay_status[$sdf['pay_status']];
        $arr_data['ship_status'] = ($sdf['ship_status'] == '0' || !$sdf['ship_status']) ? 'SHIP_NO' : 'SHIP_FINISH';        
        $arr_data['has_invoice'] = ($sdf['is_tax'] == 'true' || $sdf['is_tax'] === true) ? true : false;
        $arr_data['invoice_title'] = $sdf['tax_title'] ? $sdf['tax_title'] : '';
        $arr_data['invoice_fee'] = $sdf['cost_tax'];
        $arr_data['total_goods_fee'] = $obj_math->number_plus(array($sdf['cost_item'], $sdf['pmt_goods']));
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
				$arr_data['goods_discount_fee'] = $obj_math->number_plus(array($arr_data['goods_discount_fee'], $sdf['addon']['order_chgpointmoney']));
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
        
        $index = 0;
        foreach ($sdf['order_objects'] as $odr_obj)
        {
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
                'total_order_fee' => $obj_math->number_multiple(array($odr_obj['price'], $odr_obj['quantity'])),
                'discount_fee' => 0,
                'consign_time' => '',
                'order_items' => array('item' => array()),
                'weight' => $odr_obj['weight'],
            );
            
            foreach ($odr_obj['order_items'] as $odr_item)
            {
                $arr_addon = array();
                $str_property = "";
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
                    'price' => $odr_item['price'],
                    'num' => $odr_item['quantity'],
                    'sendnum' => $odr_item['sendnum'],
                    'total_item_fee' => $odr_item['amount'],
					'sale_price'=> $odr_item['g_price'],
                    'item_type' => $odr_item['item_type'],
					'item_status'=>'normal',
                );
            }
            
            $index++;
        }
        
        if ($arr_data['orders'])
            $arr_data['orders'] = json_encode($arr_data['orders']);
        
        $arr_callback = array(
            'class' => 'b2c_api_callback_app', 
            'method' => 'callback',
            'params' => array(
                'method' => 'store.trade.add',
                'tid' => $sdf['order_id'],
            ),
        );
        
        // 回朔和请求
        parent::request('store.trade.add', $arr_data, $arr_callback, 'Order Create', 1);
        
        return true;
    }
}