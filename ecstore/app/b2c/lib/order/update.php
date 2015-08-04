<?php

 

class b2c_order_update extends b2c_api_rpc_request
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
     * 管理员手工编辑订单
     * @params string 提交过来的数组
     * @params boolean 附件库存不足时是否需要删除的标记
     * @return string message
     */
    public function generate(&$aData, $delMark=true, &$message='')
    {
        $obj_orders = $this->app->model('orders');
        if ($aData['order_id'] == '')
        {
            $obj_orders->save($aData);
        }
        else
        {
            $orderid = $aData['order_id'];
        }
        
        if (!isset($aData['aItems']) || !$aData['aItems'])
        {
            $message = app::get('b2c')->_('订单明细不能为空！');
            return false;
        }
        
        $mdl_order_items = $this->app->model('order_items');
        $mdl_order_objects = $this->app->model('order_objects');
        $mdl_goods = $this->app->model('goods');
        $mdl_products = $this->app->model('products');
        $addStore = array();
        $is_error = false;
        
        if (isset($aData['aItems']['product_id']) && $aData['aItems']['product_id'] && isset($aData['aItems']['object_id']) && $aData['aItems']['object_id'])
            foreach($aData['aItems']['product_id'] as $key => $productId)
            {
                //得到订单数据，不包含下级的数据
                $aStore = $mdl_products->dump($productId,'*');
                $storage_enable = $this->app->getConf('site.storage.enabled');
                $object_id = $aData['aItems']['object_id'][$key];
                if (!is_null($aStore['store']) && $aStore['store'] !== '')
                {
                    $rows = $mdl_order_items->getList('*',array('order_id'=>$orderid,'product_id'=>$productId,'obj_id'=>$object_id));
                    $aRet = $rows[0];
                    $gStore = $this->objMath->number_plus($this->objMath->number_minus(array(floatval($aStore['store']), floatval($aStore['freez']))), floatval($aRet['nums']));

                    if($gStore < $aData['aNum'][$key] && $storage_enable != 'true'){
                        //return false;
                        $is_error = true;                        
                        $message .= $aRet['name'].'，'.app::get('b2c')->_('商品货号：') . $aRet['bn'] . app::get('b2c')->_('的货品库存不足！') . "\r\n";
                        $aData['aNum'][$key] = $aRet['nums'];
                        
                        continue;
                    }
                    
                    // 需要改变的库存
                    if (isset($addStore[$productId]) && $addStore[$productId])
                        $addStore[$productId] = $this->objMath->number_plus(array($addStore[$productId], $this->objMath->number_minus(array(floatval($aData['aNum'][$key]), floatval($aRet['nums'])))));
                    else 
                        $addStore[$productId] = $this->objMath->number_minus(array(floatval($aData['aNum'][$key]), floatval($aRet['nums'])));
                }
            }
       
        if (isset($aData['ajunctItems']['product_id']) && $aData['ajunctItems']['product_id'] && isset($aData['ajunctItems']['object_id']) && $aData['ajunctItems']['object_id'])
        {
            // 得到商品允许添加的配件数目
            if (isset($aData['ajunctItems']['goods_id']) && $aData['ajunctItems']['goods_id'])
            {
                $arr_goods = $mdl_goods->dump($aData['ajunctItems']['goods_id']);
                $arr_ajunct = unserialize($arr_goods['adjunct']);
                if (is_null($arr_ajunct['max_num']))
                    $max_junct_nums = 99999;
                else
                    $max_junct_nums = $arr_ajunct['max_num'];
            }
            else
            {
                $max_junct_nums = 0;
            }
            foreach($aData['ajunctItems']['product_id'] as $key => $productId)
            {
                //得到订单数据，不包含下级的数据
                $aStore = $mdl_products->dump($productId,'*');
                $storage_enable = $this->app->getConf('site.storage.enabled');
                $object_id = $aData['ajunctItems']['object_id'][$key];
                if (!is_null($aStore['store']) && $aStore['store'] !== '')
                {
                    $rows = $mdl_order_items->getList('*',array('order_id'=>$orderid,'product_id'=>$productId,'obj_id'=>$object_id));
                    $aRet = $rows[0];
                    $gStore = $this->objMath->number_plus($this->objMath->number_minus(array(floatval($aStore['store']), floatval($aStore['freez']))), floatval($aRet['nums']));
                    if($gStore < $aData['ajunctNum'][$key] && $storage_enable != 'true'){
                        //return false;
                        $is_error = true;                        
                        $message .= $aRet['name'] .'，'.app::get('b2c')->_('商品货号：') . $aRet['bn'] .  app::get('b2c')->_('的货品库存不足！') . "\r\n";
                        $aData['ajunctNum'][$key] = $aRet['nums'];
                        
                        continue;
                    }
                    else
                    {
                        if ($max_junct_nums < $aData['ajunctNum'][$key])
                        {
                            $is_error = true;
                            $message .= $aRet['name'] .'，'.app::get('b2c')->_('配件货号：') . $aRet['bn'] .  app::get('b2c')->_('的购买量超过了允许购买的最大值！') . "\r\n";
                            $aData['ajunctNum'][$key] = $aRet['nums'];
                            
                            continue;
                        }
                    }
                    
                    if (isset($addStore[$productId]) && $addStore[$productId])
                        $addStore[$productId] = $this->objMath->number_plus(array($addStore[$productId], $this->objMath->number_minus(array(floatval($aData['ajunctNum'][$key]), floatval($aRet['nums'])))));
                    else 
                        $addStore[$productId] = $this->objMath->number_minus(array(floatval($aData['ajunctNum'][$key]), floatval($aRet['nums'])));
                }
            }
        }
        
        if (isset($aData['aItems']['product_id']) && $aData['aItems']['product_id'] && isset($aData['aItems']['object_id']) && $aData['aItems']['object_id'])
        {
            reset($aData['aItems']['product_id']);
            if ($aData['ajunctItems']['product_id'])
                reset($aData['ajunctItems']['product_id']);
            if ($aData['ajunctItems']['product_id'])
                $aData['aItems']['product_id'] = array_merge($aData['aItems']['product_id'], $aData['ajunctItems']['product_id']);
            if ($aData['ajunctItems']['object_id'])
                $aData['aItems']['object_id'] = array_merge($aData['aItems']['object_id'], $aData['ajunctItems']['object_id']);
            if ($aData['ajunctPrice'])
                $aData['aPrice'] = array_merge($aData['aPrice'], $aData['ajunctPrice']);
            if ($aData['ajunctNum'])
                $aData['aNum'] = array_merge($aData['aNum'], $aData['ajunctNum']);
            
            $itemsFund = 0;
            $item_weight = 0;
            // 所有的goods type 处理的服务的初始化.
            $is_decomposition_types = array();
            $is_cal_itemsfund_types = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                if ($obj_service_goods_type->is_decomposition())
                    $is_decomposition_types[] = $obj_service_goods_type->get_goods_type();
                if (method_exists($obj_service_goods_type, 'is_cal_itemsfund') && $obj_service_goods_type->is_cal_itemsfund()) {
                    $is_cal_itemsfund_types[$obj_service_goods_type->get_goods_type()] = $obj_service_goods_type;
                }
            }
            
			$is_add_item_amount = array();
            $arr_order_object = $mdl_order_objects->getList('obj_id,amount,weight,obj_type', array('order_id'=>$orderid));
            if ($arr_order_object)
            {
                foreach ($arr_order_object as $str_order_object)
                {
                    if(isset($is_cal_itemsfund_types[$str_order_object['obj_type']]) && is_object($is_cal_itemsfund_types[$str_order_object['obj_type']])) {
                        if(in_array($str_order_object['obj_id'], $aData['aItems']['object_id'])) {
                            $is_add_item_amount[$str_order_object['obj_id']] = true;
                            $itemsFund = $this->objMath->number_plus(array($itemsFund, $is_cal_itemsfund_types[$str_order_object['obj_type']]->getItemFund($str_order_object,$aData['aItems'])));
                        }
                    }
                    else if (!in_array($str_order_object['obj_type'], $is_decomposition_types))
                    {
                        if(in_array($str_order_object['obj_id'], $aData['aItems']['object_id'])){
                            $is_add_item_amount[$str_order_object['obj_id']] = true;
                            $itemsFund = $this->objMath->number_plus(array($itemsFund, $str_order_object['amount']));
                            $item_weight = $this->objMath->number_plus(array($item_weight, $str_order_object['weight']));
                        }
                    }
                }
            }
           
            $cost_item = 0;
            $arr_insert_objects = array();
            foreach($aData['aItems']['product_id'] as $key => $productId)
            {
                $aItem = array();
                $aItem['order_id'] = $orderid;
                $aItem['product_id'] = $productId;
				$object_id = $aData['aItems']['object_id'][$key];
				if (!$is_add_item_amount||!$is_add_item_amount[$object_id]){
                $aItem['price'] = $aData['aPrice'][$key];
				}                
                $aItem['quantity'] = $aData['aNum'][$key];
                $aItem['amount'] = $this->objMath->number_multiple(array($aItem['price'], $aItem['quantity']));
                
                $cost_item = $this->objMath->number_plus(array($cost_item, $aItem['amount']));
                
                //todo 库存冻结量,库存是否足够 / 商品配件
                $rows = $mdl_order_items->dump(array('order_id'=>$orderid,'product_id'=>$productId,'obj_id'=>$object_id));
                if(isset($rows['item_id']) && $rows['item_id']){
                    $item_weight = $this->objMath->number_plus(array($item_weight, $this->objMath->number_multiple(array($rows['weight'], $aItem['quantity']))));
                    $aProduct['edit'][] = array(
                        'product_id' => $productId,
                        'object_id' => $object_id,
                    );
                    $aItem['item_id'] = $rows['item_id'];
                    $mdl_order_items->save($aItem);
                }else{
                    
                    $aPdtinfo = $mdl_products->dump($productId, 'goods_id, bn, name, cost, store, weight');
                    $item_weight = $this->objMath->number_plus(array($item_weight, $this->objMath->number_multiple(array($aPdtinfo['weight'], $aItem['quantity']))));
                    $aItem['weight'] = $this->objMath->number_multiple(array($aPdtinfo['weight'], $aItem['quantity']));
                    unset($aPdtinfo['price']);
                    $aGoodsinfo = $mdl_goods->dump($aPdtinfo['goods_id'], 'type_id');
                    
                    $aItem = array_merge($aItem, $aPdtinfo);
                    
                    $aItem['type_id'] = $aGoodsinfo['type']['type_id'];
                    $arr_insert_objects[] = 
                        array(
                            'obj_type'=> 'goods',  //goods,gift,taobao, api...
                            'obj_alias'=> app::get('b2c')->_('商品区块'),
                            'goods_id'=>$aItem['goods_id'],
                            'order_id'=>$aItem['order_id'],
                            'bn'=>$aItem['bn'],
                            'name'=>$aItem['name'],
                            'price'=>$aItem['price'],
                            'quantity'=>1,
                            'amount'=>$aItem['amount'],
                            'weight'=>$aItem['weight'],
                            'score'=>0,//todo 积分
                            'order_items'=>array(
                                array(
                                    'products'=>array('product_id'=>$productId),
                                    'goods_id'=>$aItem['goods_id'],
                                    'order_id'=>$aItem['order_id'],
                                    'item_type'=>'product',
                                    'bn'=>$aItem['bn'],
                                    'name'=>$aItem['name'],
                                    'type_id'=>$aItem['type_id'],
                                    'cost'=>$aItem['cost'],
                                    'quantity'=>$aItem['quantity'],
                                    'sendnum'=>0,
                                    'amount'=>$aItem['amount'],
                                    'price'=>$aItem['price'],
                                    'weight'=>$aItem['weight'],
                                    'addon'=>0,
                                    )
                            )
                        
                        );
                    
                    $aProduct['edit'][] = array(
                        'product_id' => $productId,
                        'object_id' => $object_id,
                    );
                }

                $itemsFund = $this->objMath->number_plus(array($itemsFund, $aItem['amount']));

                $freezTime = $this->app->getConf('system.goods.freez.time');
                $tmpdata = array();
                if($freezTime == 1)
                {
                    $tmpdata['product_id'] = $productId;
                    if(isset($addStore[$productId]))
                    {
                        if(floatval($addStore[$productId])>=0)
                        {
                            // 冻结库存
                            $tmpdata['freez'] = $this->objMath->number_plus(array($row['freez'], floatval($addStore[$productId])));
                            $mdl_goods->freez($aItem['goods_id'], $productId, abs(floatval($addStore[$productId])));
                        }
                        else
                        {
                            $tmpdata['freez'] = $this->objMath->number_plus(array($row['freez'], floatval($addStore[$productId])));
                            $mdl_goods->unfreez($aItem['goods_id'], $productId, abs(floatval($addStore[$productId])));
                        }                    
                    }
                }
            }
          
            if($aData['shipping_id'])
            {
                $dlytype = $this->app->model('dlytype');//配送方式
                $dlytype_info = $dlytype->dump($aData['shipping_id'],'*');
                
                if($aData['is_protect'] == 'true' || $aData['is_protect'] == '1'){//配送设置了保价
                    /*$cost_protect = $this->objMath->number_multiple(array($itemsFund, $dlytype_info['protect_rate']));
                    $cost_protect = $cost_protect>$dlytype_info['minprice']?$cost_protect:$dlytype_info['minprice'];//保价费
                    $aData['cost_protect'] = $cost_protect;*/
                }
                else
                    $aData['cost_protect'] = '0.00';
                
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
                                    if ($itemsFund >= $res['area'][0] && $v < $res['area'][1])
                                    {
                                        $dlytype_info['firstprice'] = $res['first_price'];
                                        $dlytype_info['continueprice'] = $res['continue_price'];
                                    }
                                }
                                else
                                {
                                    if ($itemsFund >= $res['area'][0])
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
                    $arrArea = explode(':', $aData['ship_area']);
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
                                $dlytype_info['firstprice'] = $v['firstprice'];
                                $dlytype_info['continueprice'] = $v['continueprice'];
                                $dlytype_info['dt_expressions'] = $v['dt_expressions'];
                                
                                break;
                            }
                        }
                    }
                }
                
                $aData['cost_freight'] = @utils::cal_fee($dlytype_info['dt_expressions'],$item_weight,$itemsFund,$dlytype_info['firstprice'],$dlytype_info['continueprice']);//配送费
            }
            
            if ($delMark)
            {
                $this->execDelItems($orderid, $aProduct['edit']);
            } 
            else
            {
                //$itemsFund = $this->getCostItems($orderid);
            }
            
            // 存储新增的订单项目
            if (isset($arr_insert_objects) && $arr_insert_objects)
            {
                foreach ($arr_insert_objects as $order_objects)
                {
                    $mdl_order_objects->save($order_objects);
                }
            }
            
            $aDataTmp['cost_item'] = $itemsFund;
            $aDataTmp['shipping']['cost_shipping'] = $aData['cost_freight'];
            $aDataTmp['shipping']['cost_protect'] = $aData['cost_protect'];
            $rate = $obj_orders->dump($orderid, 'cur_rate,discount,pmt_order,pmt_goods');
            $aData['discount'] = $this->objMath->number_minus(array($aData['discount'], $rate['discount']));
            $aDataTmp['total_amount'] = $this->objMath->number_plus(array($itemsFund, $aData['cost_freight'], $aData['cost_protect'], $aData['cost_payment'], $aData['cost_tax'], $rate['discount'], $aData['discount'], -$rate['pmt_order']));
			if ($aDataTmp['total_amount'] < 0)
				$aDataTmp['total_amount'] = 0;
            $aDataTmp['weight'] = $item_weight ? $item_weight : 0;
            $aDataTmp['discount'] = $this->objMath->number_plus(array($rate['discount'], $aData['discount']));
            $aDataTmp['cur_amount'] = $this->objMath->number_multiple(array($aDataTmp['total_amount'], $rate['cur_rate']));

            $aDataTmp['order_id'] = $orderid;
            if ($obj_orders->save($aDataTmp) && !$is_error)
            {            
                // 添加日志.
                $orderLog = $this->app->model("order_log");
				
				$log_text[] = array(
					'txt_key'=>'订单修改成功！',
					'data'=>array(
					),
				);
				$log_text = serialize($log_text);
				
                $sdf_order_log = array(
                    'rel_id' => $orderid,
                    'op_id' => $aData['user_id'],
                    'op_name' => $aData['account']['login_name'],
                    'alttime' => time(),
                    'bill_type' => 'order',
                    'behavior' => 'updates',
                    'result' => 'SUCCESS',
                    'log_text' => $log_text,
                );
                $orderLog->save($sdf_order_log);
                
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            if (isset($aData['aItems']) && $aData['aItems'])
            {
                $obj_order_obj = $this->app->model('order_objects');
                $tmp = $obj_order_obj->getList('amount', array('order_id'=>$aData['order_id']));
                if ($tmp)
                {
                    foreach ($tmp as $obj_items)
                    {
                        $itemsFund = $this->objMath->number_plus(array($itemsFund, $obj_items['amount']));
                    }
                }
                $aDataTmp['cost_item'] = $itemsFund;
                $aDataTmp['shipping']['cost_shipping'] = $aData['cost_freight'];
                $aDataTmp['total_amount'] = $this->objMath->number_minus(array($this->objMath->number_plus(array($itemsFund, $aData['cost_freight'], $aData['cost_protect'], $aData['cost_payment'], $aData['cost_tax'], $aData['discount'])), $aData['pmt_order']));
                $aDataTmp['weight'] = $item_weight ? $item_weight : '0.000';
                $aDataTmp['discount'] = ($aData['discount'] >= 0) ? abs($aData['discount']) : $this->objMath->number_minus(array(0, abs($aData['discount'])));
                $rate = $obj_orders->dump($orderid, 'cur_rate');
                $aDataTmp['cur_amount'] = $this->objMath->number_multiple(array($aDataTmp['total_amount'], $rate['cur_rate']));

                $aDataTmp['order_id'] = $orderid;
                
                if ($obj_orders->save($aDataTmp) && !$is_error)
                {            
                    // 添加日志.
                    $orderLog = $this->app->model("order_log");
					
                    $log_text[] = array(
						'txt_key'=>'订单修改成功！',
						'data'=>array(
						),
					);
					$log_text = serialize($log_text);
					
					$sdf_order_log = array(
						'rel_id' => $orderid,
						'op_id' => $aData['user_id'],
						'op_name' => $aData['account']['login_name'],
						'alttime' => time(),
						'bill_type' => 'order',
						'behavior' => 'updates',
						'result' => 'SUCCESS',
						'log_text' => $log_text,
					);
					
                    $orderLog->save($sdf_order_log);
                    
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * 删除订单产品的某项
     * @params string order id
     * @params item array 
     */
    private function execDelItems($orderid, &$aItems)
    {
        $freezTime = $this->app->getConf('system.goods.freez.time');
        $obj_orders_items = $this->app->model('order_items');
        $obj_orders_objects = $this->app->model('order_objects');
        $obj_goods = $this->app->model('goods');        
        
        $aRets = $obj_orders_items->getList('*', array('order_id' => $orderid));
        $arr_items = array();
        // 所有的goods type 处理的服务的初始化.
        $goods_types = array();
        $arr_is_item_edit = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        $array_no_cascase_delete = array();
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types[] = $obj_service_goods_type->get_goods_type();
            
            if ($obj_service_goods_type->is_item_edit())
                $arr_is_item_edit[] = $obj_service_goods_type->get_goods_type();
            if (method_exists($obj_service_goods_type, 'is_cascade_delete') && !$obj_service_goods_type->is_cascade_delete()) {
                $array_no_cascase_delete[$obj_service_goods_type->get_goods_type()] = 1;
            }
        }
        
        foreach ($aRets as $items)
        {
            $arr_items = array(
                'product_id' => $items['product_id'],
                'object_id' => $items['obj_id'],
            );
            
            $arr_order_object = $obj_orders_objects->getList('obj_type',array('orderid'=>$orderid,'obj_id'=>$items['obj_id']));
            if (!$arr_order_object)
                continue;
  
            if (!in_array($arr_order_object[0]['obj_type'] , $goods_types))
                continue;
            if (array_search($arr_items, $aItems) === false)
            {
                if ($items['item_type'] == 'product')
                    $items['item_type'] = 'goods';
                
                if (in_array($items['item_type'], $arr_is_item_edit))
                {
                    // 解冻库存
                    if ($freezTime == '1')
                    {
                        $productId = $items['product_id'];
                        $nums = $items['nums'];
                        $obj_goods->unfreez($items['goods_id'], $items['product_id'], $nums);
                    }
                    
                    // 在数据表中删除此数据项
                    if ($items['item_type'] == 'goods' && !isset($array_no_cascase_delete[$arr_order_object[0]['obj_type']]))
                    {
                        // 删除主商品并删除订单对象
                        $sqlString = "DELETE FROM sdb_b2c_order_items WHERE order_id = '".$orderid."' AND obj_id = '".$items['obj_id']."'";
                        $obj_orders_items->db->exec($sqlString);
                        $sqlString = "DELETE FROM sdb_b2c_order_objects WHERE order_id = '".$orderid."' AND obj_id = '".$items['obj_id']."'";
                        $obj_orders_items->db->exec($sqlString);
                    }
                    else
                    {
                        // 删除配件
                        $sqlString = "DELETE FROM sdb_b2c_order_items WHERE order_id = '".$orderid."' AND product_id ='".$items['product_id']."' AND obj_id = '".$items['obj_id']."' AND item_type != 'gift'";
                        $obj_orders_items->db->exec($sqlString);

                        //没有item，删除相应的order_object
                        if(!$obj_orders_items->getList('item_id',array('obj_id'=>$items['obj_id'],'order_id'=>$orderid))) {
                            $sqlString = "DELETE FROM sdb_b2c_order_objects WHERE order_id = '".$orderid."' AND obj_id = '".$items['obj_id']."'";
                            $obj_orders_items->db->exec($sqlString);
                        }
                    }
                }
            }
        }
   }
}