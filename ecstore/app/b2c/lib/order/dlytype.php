<?php

 
class b2c_order_dlytype
{
    /**
     * 选择配送方式的接口
     * @params object 控制器入口
     * @params string 最后一级地区的id
     * @params array 标准购物车数据
     */
    public function select_delivery_method(&$controller, $area_id='', $sdf_cart, $shipping_method='', $tpl='site/cart/checkout_shipping.html', $store_id=0)
    {   //echo "<pre>";print_r($sdf_cart);exit;
        $pay_app_id = $sdf_cart['pay_app_id'] ? $sdf_cart['pay_app_id'] : '';
        $all_dly_types = $this->get_dlytype($controller, $area_id, $pay_app_id, $store_id);
        $controller->pagedata['shipping_method'] = json_decode($shipping_method, true);
        $shipping_id = $controller->pagedata['shipping_method']['shipping_id'];
        $controller->pagedata['is_shipping_match'] = 0;
		/** 阶梯费用只能根据优惠后的金额来处理 - 除去商品优惠和订单优惠后的最终价格 **/
		$objMath = kernel::single('ectools_math');
        $cost_item = $objMath->number_minus(array($sdf_cart['subtotal'], $sdf_cart['discount_amount_prefilter'], $sdf_cart['discount_amount_order']));

        foreach ($all_dly_types as $rows)
        {
            if ($rows['is_threshold'])
            {
                if ($rows['threshold'])
                {
                    $rows['threshold'] = unserialize(stripslashes($rows['threshold']));
                    if (isset($rows['threshold']) && $rows['threshold'])
                    {
                        foreach ($rows['threshold'] as $res)
                        {
                            if ($res['area'][1] > 0)
                            {
                                if ($cost_item >= $res['area'][0] && $cost_item < $res['area'][1])
                                {
                                    $rows['firstprice'] = $res['first_price'];
                                    $rows['continueprice'] = $res['continue_price'];
                                }
                            }
                            else
                            {
                                if ($cost_item >= $res['area'][0])
                                {
                                    $rows['firstprice'] = $res['first_price'];
                                    $rows['continueprice'] = $res['continue_price'];
                                }
                            }
                        }
                    }
                }
            }
			if($sdf_cart['store_free_shipping'][$store_id] === 0){
				$rows['money'] = 0;
			}else{
				$rows['money'] = @utils::cal_fee($rows['dt_expressions'], $sdf_cart['subtotal_weight_store'][$store_id], $sdf_cart['subtotal'], $rows['firstprice'], $rows['continueprice'], $rows['firstprice']);
			}
            $shipping[] = $rows;
            if ($shipping_id == $rows['dt_id'])
            {
                $controller->pagedata['is_shipping_match'] = 1;
            }
        }

        $controller->pagedata['shippings'] = &$shipping;
        $controller->pagedata['store_id'] = $store_id;
		foreach ((array)$obj_dlytype_detail_extends = kernel::servicelist('b2c.dlytype.detail.extends') as $obj)
		{
			if (method_exists($obj, 'extends_shipping_detail'))
			{
				$obj->extends_shipping_detail($shipping);
			}
		}
        $controller->pagedata['shipping_method'] = (isset($_COOKIE['purchase']['shipping']) && $_COOKIE['purchase']['shipping']) ? unserialize($_COOKIE['purchase']['shipping']) : '';
        /** 暂时为商超夜间配送加入的service，可能功能改变后需要删除 **/
        $obj_service = kernel::service('b2c.dlytype.select.extends');
        if ($obj_service)
        {
            if (method_exists($obj_service, 'extends_shipping'))
                $controller->pagedata['is_night_delivery'] = $obj_service->extends_shipping($area_id);
            else
                $controller->pagedata['is_night_delivery'] = 'false';
        }

        /** 结束 **/
        $str_html = $controller->fetch($tpl,$controller->pagedata['app_id']);
		$obj_ajax_view_help = kernel::single('b2c_view_ajax');
		return $obj_ajax_view_help->get_html($str_html, 'b2c_order_dlytype','select_delivery_method');
    }
	/**
     * 获取配送方式
     * @params object 控制器对象
     * @params string area id
	 * author lijun 
	 # data 2012-5-21
     */
	public function get_store_dlytype(&$controller, $area_id, $pay_app_id='', $store_id=0,$total_weight,$total,$store_free_shipping,$sdf_cart){
		$all_dly_types = $this->get_dlytype($controller, $area_id, $pay_app_id, $store_id);
		foreach ($all_dly_types as $rows)
        {
            if ($rows['is_threshold'])
            {
                if ($rows['threshold'])
                {
                    $rows['threshold'] = unserialize(stripslashes($rows['threshold']));
                    if (isset($rows['threshold']) && $rows['threshold'])
                    {
                        foreach ($rows['threshold'] as $res)
                        {
                            if ($res['area'][1] > 0)
                            {
                                if ($cost_item >= $res['area'][0] && $cost_item < $res['area'][1])
                                {
                                    $rows['firstprice'] = $res['first_price'];
                                    $rows['continueprice'] = $res['continue_price'];
                                }
                            }
                            else
                            {
                                if ($cost_item >= $res['area'][0])
                                {
                                    $rows['firstprice'] = $res['first_price'];
                                    $rows['continueprice'] = $res['continue_price'];
                                }
                            }
                        }
                    }
                }
            }
			//echo '<pre>';print_r($store_free_shipping);exit;
			if($store_free_shipping[$store_id]==0||$sdf_cart['is_free_shipping'][$store_id]==1){
				$rows['money'] = 0;
			}else{
				$rows['money'] = @utils::cal_fee($rows['dt_expressions'], $total_weight[$store_id], $total[$store_id], $rows['firstprice'], $rows['continueprice'], $rows['firstprice']);
			}
                
            $shipping[] = $rows;
            if ($shipping_id == $rows['dt_id'])
            {
                $controller->pagedata['is_shipping_match'] = 1;
            }
        }
		return $shipping;
		
	}
    
    /**
     * 得到相应地区的配送方式
     * @params object 控制器对象
     * @params string area id
     */
    private function get_dlytype(&$controller, $area_id, $pay_app_id='', $store_id=0)
    {
        $objdlytype = $controller->app->model('dlytype');
        $filter = array('dt_status'=>'1');

        if($store_id){
            $filter['store_id'] = $store_id;
        }

        $obj_recsave_checkbox = kernel::servicelist('b2c.checkout_recsave_checkbox');
        $arr_extends_checkout = array();
        if ($obj_recsave_checkbox)
        {
            foreach($obj_recsave_checkbox as $object) 
            {
                if(!is_object($object)) continue;
                
                if( method_exists($object,'get_order') ) 
                    $index = $object->get_order();
                else $index = 10;
                
                while(true) {
                    if( !isset($arr_extends_checkout[$index]) )break;
                    $index++;
                }
                $arr_extends_checkout[$index] = $object;
            }
            ksort($arr_extends_checkout);
        }
        if ($arr_extends_checkout)
        {
            foreach ($arr_extends_checkout as $obj)
            {
                if ( method_exists($obj,'change_filter') )
                    $obj->change_filter($filter,$pay_app_id);
            }
        }            
        $dlytype = $objdlytype->getList('*',$filter,0,-1,'ordernum ASC');

        if ($dlytype && is_array($dlytype))
        {	$areaId = $area_id;
            $setting_0 = $setting_1 = array();
            foreach ($dlytype as $key=>$value)
            {
                if ($value['setting']==1)
                {
                    //统一费用
                    $setting_1[$value['ordernum'].'.'.$value['dt_id']] = $value;
                }
                else
                {
                    if ($value['def_area_fee'] == 'true')
                    {
                        $setting_0[$value['ordernum'].'.'.$value['dt_id']] = $value;
                    }
                    
                    $area_fee_conf = unserialize($value['area_fee_conf']);
                    if ($area_fee_conf && is_array($area_fee_conf))
                    {
                        foreach ($area_fee_conf as $k=>$v)
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
                                    $arrRegion = $objRegions->dump($areaId);
                                    while ($row = $objRegions->getRegionByParentId($arrRegion['p_region_id']))
                                    {
                                        $arrRegion = $row;
                                        $tmp_area_id = $row['region_id'];
                                        if ($tmp_area_id == $strArea)
                                        {
                                            $areaId = $tmp_area_id;
                                            break;
                                        }
                                    }
                                }
                            }
                            
                            if(in_array($areaId,$areas)){//如果地区在其中，优先使用地区设置的配送费用，及公式
                                $value['firstprice'] = $v['firstprice'];
                                $value['continueprice'] = $v['continueprice'];
                                //if($v['dt_useexp']==1){
                                $value['dt_expressions'] = $v['dt_expressions'];
                                //}
                                $setting_0[$value['ordernum'].'.'.$value['dt_id']] = $value;
                                break;
                            }
                        }
                    }
                }
            }
            
            $return = array_merge($setting_1,$setting_0);
            ksort($return);
            return $return;
        }
        
        return array();
    }
    
    public function get_special_delivery(&$controller, $area_id=0, $sdf_cart, $shipping_id=0)
    {
        $objdlytype = $controller->app->model('dlytype');
        $dlytype = $objdlytype->getList('*',array('dt_id' => $shipping_id,'dt_status'=>'1',),0,-1,'ordernum ASC');
        $cost_item = $sdf_cart['subtotal'];
        
        if ($dlytype)
        {
            $rows = $dlytype[0];
            if ($rows['is_threshold'])
            {
                if ($rows['threshold'])
                {
                    $rows['threshold'] = unserialize(stripslashes($rows['threshold']));
                    if (isset($rows['threshold']) && $rows['threshold'])
                    {
                        foreach ($rows['threshold'] as $res)
                        {
                            if ($res['area'][1] > 0)
                            {
                                if ($cost_item >= $res['area'][0] && $cost_item < $res['area'][1])
                                {
                                    $rows['firstprice'] = $res['first_price'];
                                    $rows['continueprice'] = $res['continue_price'];
                                }
                            }
                            else
                            {
                                if ($cost_item >= $res['area'][0])
                                {
                                    $rows['firstprice'] = $res['first_price'];
                                    $rows['continueprice'] = $res['continue_price'];
                                }
                            }
                        }
                    }
                }
            }
            if ($rows['area_fee_conf'])
            {
                $area_fee_conf = unserialize($rows['area_fee_conf']);
                if ($area_fee_conf && is_array($area_fee_conf) && !$rows['setting'])
                {
                    foreach ($area_fee_conf as $k=>$v)
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
                                $tmp = $objRegions->getList('*', array('region_id' => $area_id));
                                $arrRegion = $tmp[0];
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
                        
                        if (in_array($area_id,$areas))
                        {//如果地区在其中，优先使用地区设置的配送费用，及公式
                            $rows['firstprice'] = $v['firstprice'];
                            $rows['continueprice'] = $v['continueprice'];
                            $rows['dt_expressions'] = $v['dt_expressions'];
                            
                            break;
                        }
                    }
                }
            }
            if (isset($sdf_cart['is_free_shipping']) && $sdf_cart['is_free_shipping'])
                $rows['money'] = 0;
            else
                $rows['money'] = @utils::cal_fee($rows['dt_expressions'], $sdf_cart['subtotal_weight'], $cost_item, $rows['firstprice'], $rows['continueprice'], $rows['firstprice']);
            $shipping = $rows;
            
            $controller->pagedata['shipping'] = &$shipping;
			$str_html = $controller->fetch("site/cart/checkout_shipping_info.html");
			$obj_ajax_view_help = kernel::single('b2c_view_ajax');
            return $obj_ajax_view_help->get_html($str_html, 'b2c_order_dlytype','get_special_delivery');
        }
    }
}
