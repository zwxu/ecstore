<?php

 
class b2c_order_splittotal
{
    /**
     * 生成订单总计详细页面
     * @params object 控制器
     * @params object cart objects
     * @params array sdf array
     */
    public function order_total_method(&$controller,$cart,$sdf_order,$need_pmt_info='false')
    {
        $payment_detail = $this->payment_detail($controller,$cart,$sdf_order);
        $controller->pagedata['order_detail'] = &$payment_detail;
		$controller->pagedata['need_pmt_info'] = $need_pmt_info;
        $controller->pagedata['trigger_tax'] = $controller->app->getConf("site.trigger_tax");
        $controller->pagedata['tax_ratio'] = $controller->app->getConf("site.tax_ratio");
        // 发票显示埋点
        foreach( kernel::servicelist('b2c.order_tax_flag') as $services ) {
        	if ( is_object($services) ) {
        		if ( method_exists($services, 'showFlag') ) {
        			$services->showFlag($controller);
        		}
        	}
        }
        $str_html = $controller->fetch("site/cart/checkout_total.html");
		$obj_ajax_view_help = kernel::single('b2c_view_ajax');
		return $obj_ajax_view_help->get_html($str_html, 'b2c_order_total','order_total_method');
    }
    
    /** 
     * 生成订单总计详细
     * @params object 控制器
     * @params object cart objects
     * @params array sdf array
     */
    public function payment_detail(&$controller,$cart,$sdf_order)
    {
        $objMath = kernel::single('ectools_math');
        $split_order = unserialize($sdf_order['split_order']);
        $cart_info = $cart;
        $cost_item = $cart_info['subtotal'];//购物车里商品总费用
        //$cost_item = $objMath->number_minus(array($cost_item, $cart_info['discount_amount_prefilter']));
        $items_weight = $cart_info['subtotal_weight'];//购物车里商品总重量
        /*if (!isset($cart_info['is_free_shipping']) || !$cart_info['is_free_shipping'])
        {
            if ($cart_info['object']['goods'])
            {
                foreach ($cart_info['object']['goods'] as $item_obj)
                {
                    if (isset($item_obj['is_free_shipping']) && $item_obj['is_free_shipping'])
                    {
                        if (isset($item_obj['obj_items']['products'][0]) && $item_obj['obj_items']['products'][0])
                        {
                            $product_item = $item_obj['obj_items']['products'][0];
                            if ($product_item['package_use'])
                            {
                                $items_weight_added = $objMath->number_multiple(array($product_item['package_unit'], $item_obj['quantity'], $product_item['weight']));
                            }
                            else
                            {
                                $items_weight_added = $objMath->number_multiple(array($product_item['weight'], $item_obj['quantity']));
                            }
                            
                            $items_weight = $objMath->number_minus(array($items_weight, $items_weight_added));
                        }
                        $cart_info['is_free_shipping'] = true;
                        break;
                    }
                }
            }
        }*/
        
        $objCurrency = app::get('ectools')->model('currency');
        $arrDefCurrency = $objCurrency->getDefault();
        $strDefCurrency = $arrDefCurrency['cur_code'];
        $aCur = $objCurrency->getcur($sdf_order['cur']);
        
        /*if($sdf_order['shipping_id'])
        {
            $dlytype = $controller->app->model('dlytype');//配送方式
            $dlytype_info = $dlytype->dump($sdf_order['shipping_id'],'*');
            
            if($sdf_order['is_protect'] === 'true' || $sdf_order['is_protect'] === '1' || $sdf_order['is_protect'] === true){//配送设置了保价
                if ($dlytype_info['protect'] == 'true')
                {
					//** 保价费界定为商品的最原始价格 
                    $cost_protect = $objMath->number_multiple(array($cost_item, $dlytype_info['protect_rate']));
                    $cost_protect = $cost_protect>$dlytype_info['minprice']?$cost_protect:$dlytype_info['minprice'];//保价费
                }
            }
            
			// 阶梯费用只能根据优惠后的金额来处理 - 除去商品优惠和订单优惠后的最终价格  
			$subtotal_order_after = $objMath->number_minus(array($cost_item, $cart_info['discount_amount_prefilter'], $cart_info['discount_amount_order']));
            if ($dlytype_info['is_threshold'])
            {
                if ($dlytype_info['threshold'])
                {
                    $dlytype_info['threshold'] = unserialize(stripslashes($dlytype_info['threshold']));
                    if (isset($dlytype_info['threshold']) && $dlytype_info['threshold'])
                    {
                        foreach ($dlytype_info['threshold'] as $res)
                        {
                            if ($res['area'][1] > 0)
                            {
                                if ($subtotal_order_after >= $res['area'][0] && $subtotal_order_after < $res['area'][1])
                                {
                                    $dlytype_info['firstprice'] = $res['first_price'];
                                    $dlytype_info['continueprice'] = $res['continue_price'];
                                }
                            }
                            else
                            {
                                if ($subtotal_order_after >= $res['area'][0])
                                {
                                    $dlytype_info['firstprice'] = $res['first_price'];
                                    $dlytype_info['continueprice'] = $res['continue_price'];
                                }
                            }
                        }
                    }
                }
            }
            
            if (!$dlytype_info['setting'])
            {            
                $arrArea = explode(':', $sdf_order['area']);
                $area_id = $arrArea[2];
                if (isset($dlytype_info['area_fee_conf']) && $dlytype_info['area_fee_conf'])
                {
                    $area_fee_conf = unserialize($dlytype_info['area_fee_conf']);
                    foreach($area_fee_conf as $k=>$v)
                    {
                        $areas = explode(',',$v['areaGroupId']);
                        
                        // 再次解析字符
                        foreach ($areas as &$strArea)
                        {
                            if (strpos($strArea, '|') !== false)
                            {
                                $strArea = substr($strArea, 0, strpos($strArea, '|'));
                                // 取当前area id对应的最上级的区域id
                                $objRegions = app::get('ectools')->model('regions');
                                $arrRegion = $objRegions->dump($area_id);
                                while ($row = $objRegions->getRegionByParentId($arrRegion['p_region_id']))
                                {
                                    $arrRegion = $row;
                                    $tmp_area_id = $row['region_id'];
                                    if ($tmp_area_id == $strArea)
                                    {
                                        $area_id = $tmp_area_id;
                                        break;
                                    }
                                }
                            }
                        }
                        
                        if(in_array($area_id,$areas))
                        {
                            //如果地区在其中，优先使用地区设置的配送费用，及公式
                            if ($dlytype_info['firstprice'])
                            $dlytype_info['firstprice'] = $v['firstprice'];
                            $dlytype_info['continueprice'] = $v['continueprice'];
                            $dlytype_info['dt_expressions'] = $v['dt_expressions'];
                            
                            break;
                        }
                    }
                }
            }
            
            $cost_freight = @utils::cal_fee($dlytype_info['dt_expressions'],$items_weight,$cost_item,$dlytype_info['firstprice'],$dlytype_info['continueprice']);//配送费
        }
        */
        
        
        
        
       
        //echo 'freight=',$cost_freight,'protect=',$cost_protect;
        
		/**
		 * 订单免运费
		 */
        if (isset($cart_info['is_free_shipping']) && $cart_info['is_free_shipping'])
        {
			//$cart_info['discount_amount_order'] = $objMath->number_plus(array($cart_info['discount_amount_order'], $cost_freight, $cost_protect));
			//$cart_info['discount_amount'] = $objMath->number_plus(array($cart_info['discount_amount'], $cost_freight, $cost_protect));
        }
		/** end **/
            
        if($sdf_order['payment'] && $sdf_order['payment'] != -1)
        {
            $payment_info = app::get('ectools')->model('payment_cfgs')->getPaymentInfo($sdf_order['payment']);
            $pay_fee = $payment_info['pay_fee'];//支付费率
        }
        else
        {
            $pay_fee = 0;
        }
        
        if ($sdf_order['is_tax'] == 'true')
        {
        	$tax_ratio = $controller->app->getConf("site.tax_ratio");
        	// 税率计算埋点
        	foreach( kernel::servicelist('b2c.order_ratio_calulate') as $services ) {
        		if ( is_object($services) ) {
        			if ( method_exists($services, 'ratioCalculate') ) {
        				$services->ratioCalculate($controller,$sdf_order['tax_type'],$tax_ratio);
        			}
        		}
        	}
            $cost_tax = $objMath->number_multiple(array($tax_ratio, $objMath->number_minus(array($cost_item, $cart_info['discount_amount_prefilter']))));
        }

        //$total_amount = $objMath->number_plus(array($cost_item, $cost_protect, $cost_freight));
		//$total_amount = $objMath->number_minus(array($total_amount, $cart_info['discount_amount']));
		$total_amount = $objMath->number_minus(array($cost_item, $cart_info['discount_amount']));
		$total_amount = $objMath->number_plus(array($total_amount, $cost_tax));
        
        // 积分兑换
        $total_point = '0';
        $subtotal_consume_score = 0;
        $obj_point_dis = kernel::service('b2c_cart_point_discount');
        $site_point_usage = app::get('b2c')->getConf('site.point_usage');
        $over_real_point = 'false';
        if ($obj_point_dis && $site_point_usage == '2')
        {
            $over_real_point = $obj_point_dis->generate_total($sdf_order, $total_amount, $subtotal_consume_score, $total_point);
            $total_discount_consume_score = $subtotal_consume_score;
        }
        
        
        //积分不抵扣配送费用，所以放在下面
        //根据分单信息取得配送费用和保价。
        $cost_freight_arr=array();
        $cost_protect_arr=array();
        foreach($split_order as $store_id=>$store_slip){
            foreach($store_slip['slips'] as $key=>$slip){
               foreach($slip['shipping'] as $skey=>$ship){
                   if(in_array($skey,$sdf_order['is_protect'])){                   
                        $cost_protect_arr[$store_id]+=$ship['protect_money'];
                   }
                   if(in_array($skey,$sdf_order['shipping_id'])){
                        $cost_freight_arr[$store_id]+=$ship['money'];
                   }
               }
            }
            if($cart_info['is_free_shipping'][$store_id]){
                $cart_info['discount_amount_order'] = $objMath->number_plus(array($cart_info['discount_amount_order'], $cost_freight_arr[$store_id], $cost_protect_arr[$store_id]));
                $cart_info['discount_amount'] = $objMath->number_plus(array($cart_info['discount_amount'], $cost_freight_arr[$store_id], $cost_protect_arr[$store_id]));
                $cost_freight_arr[$store_id]=0;
                $cost_protect_arr[$store_id]=0;
            }
        }
        $cost_freight=array_sum($cost_freight_arr);
        $cost_protect=array_sum($cost_protect_arr);
        
        
        //运费和保价
        $total_amount = $objMath->number_plus(array($total_amount, $cost_protect, $cost_freight));
        
		$cost_payment = $objMath->number_multiple(array($total_amount, $pay_fee));
        $total_amount = $objCurrency->amount_nocur($objMath->number_plus(array($total_amount, $cost_payment)), $sdf_order['cur'], false, false);
        
		$demical = $controller->app->getConf('system.money.operation.decimals');
        $odr_decimals = $controller->app->getConf('system.money.decimals');
        $system_money_operation_carryset = $controller->app->getConf('system.money.operation.carryset');
        $total_amount_odr = $objMath->get($total_amount, $odr_decimals);
        $order_discount = $objMath->number_minus(array($total_amount, $total_amount_odr));
        if ($total_amount < 0)
            $total_amount = 0;
        if( $total_amount_odr<0 )$total_amount_odr = 0;

        // 取到商店积分规则
        $policy_method = $controller->app->getConf("site.get_policy.method");
        switch ($policy_method)
        {
            case '1':
                $subtotal_gain_score = 0;
                $totalScore = 0;
                break;
            case '2':
                $subtotal_consume_score += round($cart_info['subtotal_consume_score']);
                $policy_rate = $controller->app->getConf('site.get_rate.method');
                $subtotal_gain_score = round($cart_info['subtotal_gain_score']);
                $totalScore = round($objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));                
                break;
            case '3':
                $subtotal_consume_score += round($cart_info['subtotal_consume_score']);
                $subtotal_gain_score = round($cart_info['subtotal_gain_score']);
                $totalScore = round($objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));
                break;
            default:
                $subtotal_gain_score = 0;
                $totalScore = 0;
                break;
        }        
        
        if ($sdf_order['member_id'])
        {
            // 得到当前会员的积分
            $obj_members = $controller->app->model('members');
            $arr_member = $obj_members->dump($sdf_order['member_id'], 'point');
            $member_point = $arr_member['score']['total'];
            $obj_extend_point = kernel::service('b2c.member_extend_point_info');
            if ($obj_extend_point)
            {
                // 当前会员实际可以使用的积分
                $obj_extend_point->get_usage_point($sdf_order['member_id'], $member_point);
            }
			if ($member_point < 0)
				$totalScore = 0;
			else
				$totalScore = $member_point;
        }
        else
        {
            $totalScore = 0;
            $subtotal_gain_score = 0;    //如果是非会员购买获得积分为0，@lujy
        }
        foreach((array)$cart_info['promotion']['order'] as $k=>$v) {
			$pmt_order_info .= $v['desc']."<br>";
		}
		$cost_item = $objMath->number_minus(array($cost_item, $cart_info['discount_amount_prefilter']));
        $payment_detail = array('cost_item'=>$objCurrency->amount_nocur($cost_item, $sdf_order['cur'], false, false),
                                'cost_protect'=>$objCurrency->amount_nocur($cost_protect, $sdf_order['cur'], false, false),
                                'cost_freight'=>$objCurrency->amount_nocur($cost_freight, $sdf_order['cur'], false, false),
                                'cost_payment'=>$objCurrency->amount_nocur($cost_payment, $sdf_order['cur'], false, false),
                                'total_amount'=>$total_amount_odr,
                                'currency' => $sdf_order['cur'],
                                'pmt_order' => $cart_info['discount_amount_order'],
                                'pmt_amount' => $cart_info['discount_amount'],
                                'cost_tax' => $cost_tax,
                                'trigger_tax' => $sdf_order['is_tax'],
                                'discount' => $order_discount,
                                'cur_code' => $strDefCurrency,
                                'cur_display' => $sdf_order['cur'],
                                'cur_rate' => $aCur['cur_rate'],
                                'final_amount' => $objCurrency->changer_odr($total_amount, $sdf_order['cur'], true, false, $odr_decimals, $system_money_operation_carryset),
                                'tax_company' => $sdf_order['tax_company'],
                                'totalConsumeScore' => $subtotal_consume_score,
                                'totalGainScore' => $subtotal_gain_score,
                                'totalScore' => $totalScore,
								'pmt_order_info' => $pmt_order_info,
                            );
        
        if ($obj_point_dis && $site_point_usage == '2')
        {
            $point_extend_html = $obj_point_dis->gen_payment_detail($payment_detail);
            $payment_detail['point_extend_html'] = $point_extend_html;
        }
        
        return $payment_detail;
    }
    /** 
     * 生成订单总计详细
     * @params object 控制器
     * @params object cart objects
     * @params array sdf array
     */
    public function payment_split_detail(&$controller,$cart,$sdf_order)
    {
        $objMath = kernel::single('ectools_math');
        $split_order = unserialize($sdf_order['split_order']);
        $cart_info = $cart;
        $cost_item = $cart_info['subtotal'];//购物车里商品总费用
        //$cost_item = $objMath->number_minus(array($cost_item, $cart_info['discount_amount_prefilter']));
        $items_weight = $cart_info['subtotal_weight'];//购物车里商品总重量
                
        $objCurrency = app::get('ectools')->model('currency');
        $arrDefCurrency = $objCurrency->getDefault();
        $strDefCurrency = $arrDefCurrency['cur_code'];
        $aCur = $objCurrency->getcur($sdf_order['cur']);
        
        
        //根据分单信息取得配送费用和保价。
        $cost_freight=0;
        $cost_protect=0;
        foreach($split_order as $store_id=>$store_slip){
            foreach($store_slip['slips'] as $key=>$slip){
               foreach($slip['shipping'] as $skey=>$ship){                  
                   if($skey==$sdf_order['shipping_id']){
                        $cost_freight += $ship['money'];
                        if($sdf_order['is_protect']){
                            $cost_protect+=$ship['protect_money'];
                        }
                   }
               }
            }
        }
       
        /*if($cart_info['is_free_shipping']){
            $cart_info['discount_amount_order'] = $objMath->number_plus(array($cart_info['discount_amount_order'], $cost_freight_arr, $cost_protect_arr));
            $cart_info['discount_amount'] = $objMath->number_plus(array($cart_info['discount_amount'], $cost_freight_arr, $cost_protect_arr));
        }*/
        //echo 'freight=',$cost_freight,'protect=',$cost_protect;
        
		/**
		 * 订单免运费
		 */
        if (isset($cart_info['is_free_shipping']) && $cart_info['is_free_shipping'])
        {
			$cart_info['discount_amount_order'] = $objMath->number_plus(array($cart_info['discount_amount_order'], $cost_freight, $cost_protect));
			$cart_info['discount_amount'] = $objMath->number_plus(array($cart_info['discount_amount'], $cost_freight, $cost_protect));
        }
		/** end **/
            
        if($sdf_order['payment'] && $sdf_order['payment'] != -1)
        {
            $payment_info = app::get('ectools')->model('payment_cfgs')->getPaymentInfo($sdf_order['payment']);
            $pay_fee = $payment_info['pay_fee'];//支付费率
        }
        else
        {
            $pay_fee = 0;
        }
        
        if ($sdf_order['is_tax'] == 'true')
        {
        	$tax_ratio = $controller->app->getConf("site.tax_ratio");
        	// 税率计算埋点
        	foreach( kernel::servicelist('b2c.order_ratio_calulate') as $services ) {
        		if ( is_object($services) ) {
        			if ( method_exists($services, 'ratioCalculate') ) {
        				$services->ratioCalculate($controller,$sdf_order['tax_type'],$tax_ratio);
        			}
        		}
        	}
            $cost_tax = $objMath->number_multiple(array($tax_ratio, $objMath->number_minus(array($cost_item, $cart_info['discount_amount_prefilter']))));
        }

        $total_amount = $objMath->number_plus(array($cost_item, $cost_protect, $cost_freight));
		$total_amount = $objMath->number_minus(array($total_amount, $cart_info['discount_amount']));
		$total_amount = $objMath->number_plus(array($total_amount, $cost_tax));
        
        // 积分兑换
        $total_point = '0';
        $subtotal_consume_score = 0;
        $obj_point_dis = kernel::service('b2c_cart_point_discount');
        $site_point_usage = app::get('b2c')->getConf('site.point_usage');
        $over_real_point = 'false';
        if ($obj_point_dis && $site_point_usage == '2')
        {
            $over_real_point = $obj_point_dis->generate_total($sdf_order, $total_amount, $subtotal_consume_score, $total_point);
            $total_discount_consume_score = $subtotal_consume_score;
        }
        
		$cost_payment = $objMath->number_multiple(array($total_amount, $pay_fee));
        $total_amount = $objCurrency->amount_nocur($objMath->number_plus(array($total_amount, $cost_payment)), $sdf_order['cur'], false, false);
        
		$demical = $controller->app->getConf('system.money.operation.decimals');
        $odr_decimals = $controller->app->getConf('system.money.decimals');
        $system_money_operation_carryset = $controller->app->getConf('system.money.operation.carryset');
        $total_amount_odr = $objMath->get($total_amount, $odr_decimals);
        $order_discount = $objMath->number_minus(array($total_amount, $total_amount_odr));
        if ($total_amount < 0)
            $total_amount = 0;
        if( $total_amount_odr<0 )$total_amount_odr = 0;

        // 取到商店积分规则
        $policy_method = $controller->app->getConf("site.get_policy.method");
        switch ($policy_method)
        {
            case '1':
                $subtotal_gain_score = 0;
                $totalScore = 0;
                break;
            case '2':
                $subtotal_consume_score += round($cart_info['subtotal_consume_score']);
                $policy_rate = $controller->app->getConf('site.get_rate.method');
                $subtotal_gain_score = round($cart_info['subtotal_gain_score']);
                $totalScore = round($objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));                
                break;
            case '3':
                $subtotal_consume_score += round($cart_info['subtotal_consume_score']);
                $subtotal_gain_score = round($cart_info['subtotal_gain_score']);
                $totalScore = round($objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));
                break;
            default:
                $subtotal_gain_score = 0;
                $totalScore = 0;
                break;
        }        
        
        if ($sdf_order['member_id'])
        {
            // 得到当前会员的积分
            $obj_members = $controller->app->model('members');
            $arr_member = $obj_members->dump($sdf_order['member_id'], 'point');
            $member_point = $arr_member['score']['total'];
            $obj_extend_point = kernel::service('b2c.member_extend_point_info');
            if ($obj_extend_point)
            {
                // 当前会员实际可以使用的积分
                $obj_extend_point->get_usage_point($sdf_order['member_id'], $member_point);
            }
			if ($member_point < 0)
				$totalScore = 0;
			else
				$totalScore = $member_point;
        }
        else
        {
            $totalScore = 0;
            $subtotal_gain_score = 0;    //如果是非会员购买获得积分为0，@lujy
        }
        foreach((array)$cart_info['promotion']['order'] as $k=>$v) {
			$pmt_order_info .= $v['desc']."<br>";
		}
		$cost_item = $objMath->number_minus(array($cost_item, $cart_info['discount_amount_prefilter']));
        $payment_detail = array('cost_item'=>$objCurrency->amount_nocur($cost_item, $sdf_order['cur'], false, false),
                                'cost_protect'=>$objCurrency->amount_nocur($cost_protect, $sdf_order['cur'], false, false),
                                'cost_freight'=>$objCurrency->amount_nocur($cost_freight, $sdf_order['cur'], false, false),
                                'cost_payment'=>$objCurrency->amount_nocur($cost_payment, $sdf_order['cur'], false, false),
                                'total_amount'=>$total_amount_odr,
                                'currency' => $sdf_order['cur'],
                                'pmt_order' => $cart_info['discount_amount_order'],
                                'pmt_amount' => $cart_info['discount_amount'],
                                'cost_tax' => $cost_tax,
                                'trigger_tax' => $sdf_order['is_tax'],
                                'discount' => $order_discount,
                                'cur_code' => $strDefCurrency,
                                'cur_display' => $sdf_order['cur'],
                                'cur_rate' => $aCur['cur_rate'],
                                'final_amount' => $objCurrency->changer_odr($total_amount, $sdf_order['cur'], true, false, $odr_decimals, $system_money_operation_carryset),
                                'tax_company' => $sdf_order['tax_company'],
                                'totalConsumeScore' => $subtotal_consume_score,
                                'totalGainScore' => $subtotal_gain_score,
                                'totalScore' => $totalScore,
								'pmt_order_info' => $pmt_order_info,
                            );
        
        if ($obj_point_dis && $site_point_usage == '2')
        {
            $point_extend_html = $obj_point_dis->gen_payment_detail($payment_detail);
            $payment_detail['point_extend_html'] = $point_extend_html;
        }
        
        return $payment_detail;
    }
}
