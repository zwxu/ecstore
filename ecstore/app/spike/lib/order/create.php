<?php

 

class spike_order_create extends b2c_api_rpc_request
{
    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        parent::__construct($app);
        $this->objMath = kernel::single('ectools_math');
    }
    
    /**
     * 订单标准数据生成
     * @params mixed - 订单数据
     * @params string - 唯一标识
	 * @params string message
	 * @param array cart object array
     * @return boolean - 成功与否(mixed 订单数据)
     */
    public function generate(&$sdf, $member_indent='', &$msg='',$aCart=array(),$store_id='')
    {
        $order_data = array();
        $cart_objects  = $aCart;
        $order_data['order_id'] = $sdf['order_id'];
        $order_data['member_id'] = ($sdf['member_id']) ? $sdf['member_id'] : 0;
        $is_generate = $this->_chgdata($sdf, $order_data, $member_indent, $msg, $aCart,$store_id);
		if (!$is_generate)
		{
			return false;
		}
       
        // 订单创建前之行的方法
        $odr_create_service = kernel::servicelist('b2c_order.beforecreate');
        if ($odr_create_service)
        {
            foreach ($odr_create_service as $odr_ser)
            {
                $odr_ser->generate($order_data,$sdf,$cart_objects);
            }
        }

        return $order_data;
    }
    
    /**
     * @params array sdf
     * @params array
     * @params string
     * @params string message
	 * @return boolean true or false
     */
    private function _chgdata(&$sdf, &$order_data, $member_indent='', &$msg='',$aCart=array(),$store_id='')
    {	
        $now = time();
        $objCurrency = app::get('ectools')->model("currency")->getcur($sdf['payment']['currency']);
        
        // 得到shipping name
        $objDlytype = app::get('b2c')->model("dlytype");
        $shipping_name = $objDlytype->dump($sdf['delivery']['shipping_id'], 'dt_name');

        if(empty($shipping_name['dt_name'])){
            $msg = app::get('b2c')->_('请选择店铺配送方式！');
            return false;
        }

        $memo = isset($sdf['memo'])?$sdf['memo']:$sdf['delivery']['memo'];//订单备注

        $order_data = array(
            'order_id'=>$order_data['order_id'],
            'member_id'=>$order_data['member_id'],
            'confirm' => 'N',
            'status'=>'active',     //active/dead/cancel/finish
            'pay_status'=>'0', 
            'ship_status'=>'0',     //0/1/2/3/4
            'is_delivery'=>'Y',     //Y/N
            'createtime'=>$now,
            'last_modified'=>$now,
            'memo'=>$memo,
            'ip'=>$_SERVER['REMOTE_ADDR'],
            'title' => app::get('b2c')->_('订单明细介绍'),
            'shipping' => array('shipping_id' => $sdf['delivery']['shipping_id'],
                                'is_protect' => ($sdf['delivery']['is_protect'][$sdf['delivery']['shipping_id']]) ? 'true' : 'false',
                                'shipping_name' => $shipping_name['dt_name'],
                                'cost_shipping' => 10,
                                'cost_protect' => 0,
                                ),
            'payinfo' => array('pay_app_id' => $sdf['payment']['pay_app_id']),
            'currency' => $sdf['payment']['currency'],
            'cur_rate' => $objCurrency['cur_rate'],
            'is_tax' => ($sdf['payment']['is_tax'] ? 'true' : 'false'),
            'tax_title' => $sdf['payment']['tax_company'],
        );
		if($aCart['inAct']=='true'){
			$order_data['order_type'] = 'timedbuy';
		}
        
		$objCarts  = $aCart;
        
        // 购物车是否为空
        $is_empty = app::get('b2c')->model('cart')->is_empty($objCarts);
        if ($is_empty)
        {
            $msg = app::get('b2c')->_('购物车为空！');
            return false;
        }

        //判断订单是否为虚拟商品订单 
        if(isset($objCarts['object']['goods']['0']['obj_items']['products']['0']['goods_id'])){
            $obj_goods = app::get('b2c')->model('goods');
            $info = $obj_goods->dump($objCarts['object']['goods']['0']['obj_items']['products']['0']['goods_id'],'goods_kind,goods_kind_detail');
            $order_data['order_kind'] = $info['goods_kind'];
            $order_data['order_kind_detail'] = $info['goods_kind_detail'];
        }
        
        $order_data['weight'] = $objCarts['subtotal_weight'];
        $order_data['itemnum'] = $objCarts['items_quantity'];
        // 计算cart的总费用
        $obj_total = new b2c_order_total();
        $sdf_order = array('payment'=>$sdf['payment']['pay_app_id'],'shipping_id'=>array($store_id=>$sdf['delivery']['shipping_id']),'is_protect'=>array($store_id=>($sdf['delivery']['is_protect'][$sdf['delivery']['shipping_id']] ? $sdf['delivery']['is_protect'][$sdf['delivery']['shipping_id']] : 0)),'currency'=>$sdf['payment']['currency'],'is_tax'=>($sdf['payment']['is_tax'] ? $sdf['payment']['is_tax'] : 'false'), 'tax_company'=>($sdf['payment']['tax_company'] ? $sdf['payment']['tax_company'] : ''),'area'=>$sdf['delivery']['ship_area']);
		/** 积分抵扣值的转换 **/
		$sdf_order['dis_point'] = $sdf['payment']['dis_point'];
		$sdf_order['member_id'] = $sdf['member_id'];
		// 订单创建过程添加项埋点
		foreach( kernel::servicelist('b2c.order_add_sdf') as $services ) {
			if ( is_object($services) ) {
				if ( method_exists($services, 'addElement') ) {
					$services->addElement($sdf_order,$sdf['payment']['tax_type']);
				}
			}
		}
        $order_detail = $obj_total->payment_detail(app::get('b2c')->controller('site_order'),$objCarts,$sdf_order);
        // 订单显示方式        
        $system_money_decimals = app::get('b2c')->getConf('system.money.decimals');
        $system_money_operation_carryset = app::get('b2c')->getConf('system.money.operation.carryset');
        $order_data['cost_item'] = $this->objMath->formatNumber($order_detail['cost_item'], $system_money_decimals, $system_money_operation_carryset);

        if ($sdf['payment']['is_tax']) {
            $order_data['cost_tax'] = $this->objMath->formatNumber($order_detail['cost_tax'], $system_money_decimals, $system_money_operation_carryset);
        }
        else {
            $order_data['cost_tax'] = 0;
        }
        //$order_data['tax_company'] = $sdf['payment']['tax_company'];     
        $order_data['shipping']['cost_shipping'] = $this->objMath->formatNumber($order_detail['cost_freight'], $system_money_decimals, $system_money_operation_carryset);
        $order_data['shipping']['cost_protect'] = $this->objMath->formatNumber($order_detail['cost_protect'], $system_money_decimals, $system_money_operation_carryset);
        $order_data['payinfo']['cost_payment'] = $this->objMath->formatNumber($order_detail['cost_payment'], $system_money_decimals, $system_money_operation_carryset);
        $order_data['total_amount'] = $this->objMath->formatNumber($order_detail['total_amount'], $system_money_decimals, $system_money_operation_carryset);
        $order_data['cur_amount'] = app::get('ectools')->model("currency")->changer_odr($order_data['total_amount'], $sdf['payment']['currency'], true, false, $system_money_decimals, $system_money_operation_carryset);
        $order_data['pmt_goods'] = $this->objMath->formatNumber($objCarts['discount_amount_prefilter'], $system_money_decimals, $system_money_operation_carryset);
        $order_data['pmt_order'] = $this->objMath->formatNumber($order_detail['pmt_order'], $system_money_decimals, $system_money_operation_carryset);
        $order_data['discount'] = $this->objMath->number_minus(array(0, $order_detail['discount']));
        $order_data['payed'] = "0.00";
        
        $order_data['score_u'] = round($order_detail['totalConsumeScore']);
        $order_data['score_g'] = round($order_detail['totalGainScore']);
        
        $site_point_deductible_value = app::get('b2c')->getConf('site.point_deductible_value');
        $discount_value = $order_data['score_u'] * $site_point_deductible_value;//抵扣的总金额
        $order_data['discount_value'] = $discount_value;//抵扣的总金额
        
        $obj_service_change_point = kernel::servicelist('b2c.order_total_change');
        if ($obj_service_change_point)
        {
            foreach ($obj_service_change_point as $obj_point)
                $obj_point->order_summary_change($sdf, $order_data, $system_money_decimals, $system_money_operation_carryset);
        }
        
        $order_data['consignee'] =  array(
            'name'=>$sdf['delivery']['ship_name'],
            'addr'=>$sdf['delivery']['ship_addr_area'].$sdf['delivery']['ship_addr'],
            'zip'=>$sdf['delivery']['ship_zip'],
            'telephone'=>$sdf['delivery']['ship_tel'],
            'mobile'=>$sdf['delivery']['ship_mobile'],
            'email'=>$sdf['delivery']['ship_email'],
            'area'=>$sdf['delivery']['ship_area'],
            'r_time'=> ($sdf['delivery']['specal_day']?$sdf['delivery']['specal_day']:$sdf['delivery']['day']).$sdf['delivery']['time'],
            'meta'=>array()
        );
		
		$is_generate = true;
        $is_generate = $this->_order_items($sdf, $order_data, $objCarts['object'], $msg);
		if (!$is_generate)
		{
			return false;
		}
        if (isset($objCarts['promotion']) && $objCarts['promotion'])
		{
            if($objCarts['is_free_shipping'][$store_id]==true) {
                if(isset($objCarts['promotion'][$objCarts['free_shipping_rule_type'][$store]][$objCarts['free_shipping_rule_id'][$store_id]])) {
                    $objCarts['promotion'][$objCarts['free_shipping_rule_type']][$objCarts['free_shipping_rule_id']]['discount_amount'] = $order_detail['cost_freight'];
                }

            }
            $is_generate = $this->_order_pmts($order_data, $objCarts['promotion'], $msg,$store_id);
			if (!$is_generate)
			{
				return false;
			}
		}
			
        return true;
    }
    
    /**
     * 取到订单优惠规则
     * @params array 订单详细数组地址
     * @params array 订单规则数组
	 * @params string message
     * @return boolean
     */
    private function _order_pmts(&$order_data, $order_pmts, &$msg='',$store_id='')
    {
        if (isset($order_pmts) && is_array($order_pmts) && $order_pmts)
        {
            foreach ($order_pmts as $type=>$arr_pmt_odrs)
            {
                foreach ($arr_pmt_odrs as $key=>$arr_pmts_items)
                {	
					if(!$store_id||$store_id==$arr_pmts_items['store_id']){
						$order_data['order_pmt'][] = array(
							'pmt_id' => $arr_pmts_items['rule_id'],
							'order_id' => $order_data['order_id'],
							'pmt_type' => $type,
							'pmt_amount' => floatval($arr_pmts_items['discount_amount']),
							'pmt_memo' => $arr_pmts_items['name'],
							'pmt_describe' => $arr_pmts_items['desc'],
						);
					}
                }
            }
			//echo '<pre>';print_r($order_data['order_pmt']);exit; 
        }
		
		return true;
    }
    
    /**
     * 取到购物车的goods信息
     * @params array sdf
     * @params array - 取地址数组
	 * @params string message
     * @return boolean
     */
    private function _order_items(&$sdf, &$order_data, $orderObj, &$msg='')
    {
        if (is_array($orderObj) && $orderObj)
        {
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;                
            }
            $store_mark = app::get('b2c')->getConf('system.goods.freez.time');
			$is_freez = true;
            
            foreach ($orderObj as $obj_type=>$arrObjInfo)
            {
                // Orders - 分成购物券和订单
                if (is_array($arrObjInfo) && $arrObjInfo)
                {
                    $obj_specification = app::get('b2c')->model('specification');
                    $obj_spec_values = app::get('b2c')->model('spec_values');
                    
                    if ($obj_type == "goods")
                    {
                        // Order Objects.
                        $index = count($order_data['order_objects']);
                        foreach ($arrObjInfo as $arrObjItems)
                        {
                            // 订单附加信息
                            $strAddon = "";
                            $arrAddon = array();
                            if ($sdf['minfo'])
                            {
                                if ($sdf['minfo'][$arrObjItems['obj_items']['products'][0]['product_id']])
                                {
                                    $arrAddon  = $sdf['minfo'][$arrObjItems['obj_items']['products'][0]['product_id']];
                                    $strAddon .= serialize($arrAddon);
                                }
                            }
                            
                            if ($arrObjItems['obj_items']['products'][0]['package_use'] == '1')
                            {
                                if (isset($arrObjItems['obj_items']['products'][0]['spec_desc']) && $arrObjItems['obj_items']['products'][0]['spec_desc'] && is_array($arrObjItems['obj_items']['products'][0]['spec_desc']))
                                {                            
                                    if ($strAddon)
                                    {
                                        $arrAddon = unserialize($strAddon);                                        
                                    }
                                    
                                    foreach ($arrObjItems['obj_items']['products'][0]['spec_desc']['spec_value_id'] as $spec_key => $str_spec_value_id)
                                    {
                                        $temp = $arrObjItems['obj_items']['products'][0]['spec_desc']['spec_value'][$spec_key];
                                        $arr_spec_value = $obj_spec_values->dump($str_spec_value_id);
                                        $arr_specification = $obj_specification->dump($arr_spec_value['spec_id']);
                                        $arrAddon['product_attr'][$spec_key] = array(
                                            'label' => $arr_specification['spec_name'],
                                            'value' => $temp?$temp:$arr_spec_value['spec_value'],
                                        );
                                    }
                                    
                                    $strAddon = serialize($arrAddon);
                                }
                                $order_data['order_objects'][$index] = array(
                                    'order_id' => $order_data['order_id'],
                                    'obj_type' => $obj_type,
                                    'obj_alias' => app::get('b2c')->_('商品区块'),
                                    'goods_id' => $arrObjItems['obj_items']['products'][0]['goods_id'],
                                    'bn' => $arrObjItems['obj_items']['products'][0]['bn'],
                                    'name' => $arrObjItems['obj_items']['products'][0]['name'],
                                    'price' => $arrObjItems['obj_items']['products'][0]['price']['price'],
                                    'quantity'=> $arrObjItems['quantity'],
                                    'amount'=> $this->objMath->number_minus(array($arrObjItems['obj_items']['subtotal'], $arrObjItems['obj_items']['discout_amount'])),
                                    'weight'=> $arrObjItems['subtotal_weight'],
                                    'score'=> $arrObjItems['subtotal_gain_score'],
                                    'order_items' => array(
                                        array(
                                            'products' => array('product_id'=>$arrObjItems['obj_items']['products'][0]['product_id']),
                                            'goods_id'=>$arrObjItems['obj_items']['products'][0]['goods_id'],
                                            'order_id' => $order_data['order_id'],
                                            'item_type'=>'product',
                                            'bn'=>$arrObjItems['obj_items']['products'][0]['bn'],
                                            'name'=>$arrObjItems['obj_items']['products'][0]['name'],
                                            'type_id'=>$arrObjItems['obj_items']['products'][0]['type_id'],
                                            'cost'=>$arrObjItems['obj_items']['products'][0]['price']['cost'],
                                            'quantity'=>$this->objMath->number_multiple(array($arrObjItems['obj_items']['products'][0]['quantity'], $arrObjItems['quantity'])),
                                            'sendnum'=>0,
                                            'amount'=>$this->objMath->number_multiple(array($arrObjItems['obj_items']['products'][0]['price']['buy_price'], $arrObjItems['quantity'])),
                                            'score' => $this->objMath->number_minus(array($arrObjItems['obj_items']['products'][0]['gain_score'], $arrObjItems['obj_items']['products'][0]['consume_score'])),
                                            'price'=>$arrObjItems['obj_items']['products'][0]['price']['buy_price'],
											'g_price'=>$arrObjItems['obj_items']['products'][0]['price']['member_lv_price'],
                                            'weight'=>$arrObjItems['obj_items']['products'][0]['weight'],
                                            'addon'=>$strAddon,
                                        ),
                                    ),
                                );
                            }
                            else
                            {
                                if (isset($arrObjItems['obj_items']['products'][0]['spec_desc']) && $arrObjItems['obj_items']['products'][0]['spec_desc'] && is_array($arrObjItems['obj_items']['products'][0]['spec_desc']))
                                {                            
                                    if ($strAddon)
                                    {
                                        $arrAddon = unserialize($strAddon);                                        
                                    }
                                    
                                    foreach ($arrObjItems['obj_items']['products'][0]['spec_desc']['spec_value_id'] as $spec_key=>$str_spec_value_id)
                                    {
                                        $temp = $arrObjItems['obj_items']['products'][0]['spec_desc']['spec_value'][$spec_key];
                                        $arr_spec_value = $obj_spec_values->dump($str_spec_value_id);
                                        $arr_specification = $obj_specification->dump($arr_spec_value['spec_id']);
                                        $arrAddon['product_attr'][$spec_key] = array(
                                            'label' => $arr_specification['spec_name'],
                                            'value' => $temp?$temp:$arr_spec_value['spec_value'],
                                        );
                                    }
                                    
                                    $strAddon = serialize($arrAddon);
                                }
                                
                                $order_data['order_objects'][$index] = array(
                                    'order_id' => $order_data['order_id'],
                                    'obj_type' => $obj_type,
                                    'obj_alias' => ($obj_type == 'goods') ? app::get('b2c')->_('商品区块') : app::get('b2c')->_('捆绑销售'),
                                    'goods_id' => $arrObjItems['obj_items']['products'][0]['goods_id'],
                                    'bn' => $arrObjItems['obj_items']['products'][0]['bn'],
                                    'name' => $arrObjItems['obj_items']['products'][0]['name'],
                                    'price' => $arrObjItems['obj_items']['products'][0]['price']['price'],
                                    'quantity'=> $arrObjItems['quantity'],
                                    'amount'=> $this->objMath->number_minus(array($arrObjItems['obj_items']['subtotal'], $arrObjItems['obj_items']['discout_amount'])),
                                    'weight'=> $arrObjItems['subtotal_weight'],
                                    'score'=> $arrObjItems['subtotal_gain_score'],
                                    'order_items' => array(
                                        array(
                                            'products' => array('product_id'=>$arrObjItems['obj_items']['products'][0]['product_id']),
                                            'goods_id'=>$arrObjItems['obj_items']['products'][0]['goods_id'],
                                            'order_id' => $order_data['order_id'],
                                            'item_type'=>'product',
                                            'bn'=>$arrObjItems['obj_items']['products'][0]['bn'],
                                            'name'=>$arrObjItems['obj_items']['products'][0]['name'],
                                            'type_id'=>$arrObjItems['obj_items']['products'][0]['type_id'],
                                            'cost'=>$arrObjItems['obj_items']['products'][0]['price']['cost'],
                                            'quantity'=>$this->objMath->number_multiple(array($arrObjItems['obj_items']['products'][0]['quantity'], $arrObjItems['quantity'])),
                                            'sendnum'=>0,
                                            'amount'=>$this->objMath->number_multiple(array($arrObjItems['obj_items']['products'][0]['price']['buy_price'], $arrObjItems['quantity'])),
                                            'score' => $this->objMath->number_minus(array($arrObjItems['obj_items']['products'][0]['gain_score'], $arrObjItems['obj_items']['products'][0]['consume_score'])),
                                            'price'=>$arrObjItems['obj_items']['products'][0]['price']['buy_price'],
											'g_price'=>$arrObjItems['obj_items']['products'][0]['price']['member_lv_price'],
                                            'weight'=>$arrObjItems['obj_items']['products'][0]['weight'],
                                            'addon'=>$strAddon,
                                        ),
                                    ),
                                );
                            }
                            
                            // 添加附件和赠品todo...
                            if ($arr_service_goods_type_obj['adjunct'])
                            {
                                $str_service_goods_type_obj = $arr_service_goods_type_obj['adjunct'];
                                $str_service_name = $str_service_goods_type_obj->get_goods_type();
                                
                                if (isset($arrObjItems[$str_service_name]) && $arrObjItems[$str_service_name])
                                {
                                    $str_start = count($order_data['order_objects'][$index]['order_items']);
                                    foreach ($arrObjItems['adjunct'] as $key=>$adjunctItems)
                                    {
                                        $strAddon = "";
                                        $arrAddon = array();
                                        if (isset($adjunctItems['spec_desc']) && $adjunctItems['spec_desc'] && is_array($adjunctItems['spec_desc']))
                                        {
                                            foreach ($adjunctItems['spec_desc']['spec_value_id'] as $spec_key=>$str_spec_value_id)
                                            {
                                                $temp = $arrObjItems['obj_items']['products'][0]['spec_desc']['spec_value'][$spec_key];
                                                $arr_spec_value = $obj_spec_values->dump($str_spec_value_id);
                                                $arr_specification = $obj_specification->dump($arr_spec_value['spec_id']);
                                                $arrAddon['product_attr'][$spec_key] = array(
                                                    'label' => $arr_specification['spec_name'],
                                                    'value' => $temp?temp:$arr_spec_value['spec_value'],
                                                );
                                            }
                                            
                                            $strAddon = serialize($arrAddon);
                                        }
                                    
                                        $order_data['order_objects'][$index]['order_items'][$key + $str_start] = array(
                                            'products' => array('product_id'=>$adjunctItems['product_id']),
                                            'goods_id' => $adjunctItems['goods_id'],
                                            'order_id' => $order_data['order_id'],
                                            'item_type'=>'adjunct',
                                            'bn' => $adjunctItems['bn'],
                                            'name' => $adjunctItems['name'],
                                            'type_id' => $adjunctItems['type_id'],
                                            'cost' => $adjunctItems['price']['cost'],
                                            'quantity' => $adjunctItems['quantity'],
                                            'sendnum' => 0,
                                            'amount' => $this->objMath->number_multiple(array($adjunctItems['price']['buy_price'], $adjunctItems['quantity'])),
                                            'price' => $adjunctItems['price']['buy_price'],
											'g_price'=>$adjunctItems['price']['buy_price'],
                                            'weight' => $adjunctItems['weight'],
                                            'score' => $this->objMath->number_multiple(array($adjunctItems['gain_score'], $adjunctItems['quantity'])),
                                            'addon' => $strAddon,
                                        ); 
                                        
                                        // 处理adjunct库存冻结                                        
                                        if ($arr_service_goods_type_obj['adjunct'])
                                        {
                                            $str_service_goods_type_obj = $arr_service_goods_type_obj['adjunct'];
                                            $arr_params = array(
                                                'goods_id' => $adjunctItems['goods_id'],
                                                'product_id' => $adjunctItems['product_id'],
                                                'quantity' => $adjunctItems['quantity'],
                                            );
                                            if ($store_mark == '1')
											{
                                                $is_freez = $str_service_goods_type_obj->freezeGoods($arr_params);
												if (!$is_freez)
												{
													$msg = app::get('b2c')->_('库存冻结失败！');
													return false;
												}
											}
                                        }
                                        
                                    }
                                }
                            }
                            
                            if (isset($arr_service_goods_type_obj['gift']) && $arr_service_goods_type_obj['gift'])
                            {
                                $str_service_goods_type_obj = $arr_service_goods_type_obj['gift'];
                                $str_service_name = $str_service_goods_type_obj->get_goods_type();
                                
                                // 赠品...
                                if (isset($arrObjItems[$str_service_name]) && $arrObjItems[$str_service_name])
                                {
                                    $str_start = count($order_data['order_objects'][$index]['order_items']);
                                    foreach ($arrObjItems[$str_service_name] as $key=>$adgiftItems)
                                    {
                                        $strAddon = "";
                                        $arrAddon = array();
                                        if (isset($adgiftItems['spec_desc']) && $adgiftItems['spec_desc'] && is_array($adgiftItems['spec_desc']))
                                        {
                                            foreach ($adgiftItems['spec_desc']['spec_value_id'] as $spec_key=>$str_spec_value_id)
                                            {
                                                $temp = $arrObjItems['obj_items']['products'][0]['spec_desc']['spec_value'][$spec_key];
                                                $arr_spec_value = $obj_spec_values->dump($str_spec_value_id);
                                                $arr_specification = $obj_specification->dump($arr_spec_value['spec_id']);
                                                $arrAddon['product_attr'][$spec_key] = array(
                                                    'label' => $arr_specification['spec_name'],
                                                    'value' => $temp?$temp:$arr_spec_value['spec_value'],
                                                );
                                            }
                                            
                                            $strAddon = serialize($arrAddon);
                                        }
                                        
                                        $order_data['order_objects'][$index]['order_items'][$key + $str_start] = array(
                                            'products' => array('product_id'=>$adgiftItems['product_id']),
                                            'goods_id' => $adgiftItems['goods_id'],
                                            'order_id' => $order_data['order_id'],
                                            'item_type'=> $str_service_name,
                                            'bn' => $adgiftItems['bn'],
                                            'name' => $adgiftItems['name'],
                                            'type_id' => $adgiftItems['type_id'],
                                            'cost' => $adgiftItems['price']['cost'],
                                            'quantity' => $adgiftItems['quantity'],
                                            'sendnum' => 0,
                                            'amount' => $this->objMath->number_multiple(array($adgiftItems['price']['buy_price'], $adgiftItems['quantity'])),
                                            'price' => $adgiftItems['price']['buy_price'],
											'g_price'=>$adgiftItems['price']['buy_price'],
                                            'score'=> $this->objMath->number_multiple(array($arr_gift_info['consume_score'], $adgiftItems['quantity'])),
                                            'weight' => $adgiftItems['weight'],
                                            'addon' => $strAddon,
                                        ); 
                                        
                                        // 处理赠品库存冻结                                       
                                        $arr_params = array(
                                            'goods_id' => $adgiftItems['goods_id'],
                                            'product_id' => $adgiftItems['product_id'],
                                            'quantity' => $adgiftItems['quantity'],
                                        );
                                        if ($store_mark == '1')
										{
                                            $is_freez = $str_service_goods_type_obj->freezeGoods($arr_params);
											if (!$is_freez)
											{
												$msg = app::get('b2c')->_('库存冻结失败！');
												return false;
											}
										}                                        
                                    }
                                } 
                                                                
                            }                       
                            
                             // 处理product订单冻结                            
                            if ($arr_service_goods_type_obj['goods'])
                            {
                                $str_service_goods_type_obj = $arr_service_goods_type_obj['goods'];
                                $arr_params = array(
                                    'goods_id' => $arrObjItems['obj_items']['products'][0]['goods_id'],
                                    'product_id' => $arrObjItems['obj_items']['products'][0]['product_id'],
                                    'quantity' => $arrObjItems['quantity'],
                                );
                                if ($store_mark == '1')
								{
                                    $is_freez = $str_service_goods_type_obj->freezeGoods($arr_params);
									if (!$is_freez)
									{
										$msg = app::get('b2c')->_('库存冻结失败！');
										return false;
									}
								}
                            }                            
                            
                            $index++;
                        }
                    }
                    else
                    {
                        if( is_object($arr_service_goods_type_obj[$obj_type]) )
						{
                            if (!$arr_service_goods_type_obj[$obj_type]->gen_order($arrObjInfo, $order_data, $msg))
								return false;
						}
                    }
                }
            }
			
			return true;
        } 
    }
    
    /**
     * 订单保存
     * @param array sdf
     * @param string member indent
     * @param string message
     * @return boolean success or failure
     */
    public function save(&$sdf, &$msg='')
    {
         // 创建订单是和中心的交互
        $order = &app::get('b2c')->model('orders');
        $order_id = $sdf['order_id'];
        $result = $order->save($sdf);//todo order_items表product_id字段未插入 
        
        if (!$result)
        {
            $msg = app::get('b2c')->_("订单生成失败！");
            return false;
        }
        else
        {            
            return true;
        }
    }
    
    public function rpc_caller_request($sdf)
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
            $objMember = app::get('b2c')->model('members');
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