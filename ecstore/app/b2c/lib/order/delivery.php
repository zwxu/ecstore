<?php



class b2c_order_delivery extends b2c_order_operation
{
    // 私有化实例，单件模式使用.
    private static $instance;

    /**
     * 私有构造方法，不能直接实例化，只能通过调用getInstance静态方法被构造
     * @params null
     * @return null
     */
    private function __construct($app, $model)
    {
        // 异常处理
        if (is_null($model) || !$model)
        {
            trigger_error(app::get('b2c')->_("应用对象不能为空！"), E_USER_ERROR);
        }

        $this->app = $app;
        $this->model = $model;
        $this->objMath = kernel::single('ectools_math');
    }

    /**
     * 类静态构造实例的唯一入口
     * @params object app object
     * @params object model object
     * @return object b2c_order_delivery的对象
     */
    public static function getInstance($app, $model)
    {
        if (is_object(self::$instance))
        {
            return self::$instance;
        }

        self::$instance = new b2c_order_delivery($app, $model);

        return self::$instance;
    }

    /**
     * 最终的克隆方法，禁止克隆本类实例，克隆是抛出异常。
     * @params null
     * @return null
     */
    final public function __clone()
    {
        trigger_error(app::get('b2c')->_("此类对象不能被克隆！"), E_USER_ERROR);
    }

    /**
     * 创建发货单
     * @params array - 订单数据
     * @params obj - 应用对象
     * @params string - 支付单生成的记录
     * @return boolean - 创建成功与否
     */
    public function generate($sdf, &$controller=null, &$msg='')
    {
		/**
		 * 发货之前的判断
		 */
		$is_allow_delivery = true;
		$obj_before_delivery = kernel::servicelist('b2c.order.delivery.before');
		if ($obj_before_delivery)
		{
			foreach ($obj_before_delivery as $obj)
			{
				if (method_exists($obj,'generate'))
					$is_allow_delivery = $obj->generate($sdf, $msg);
			}
		}
		if (!$is_allow_delivery) return false;

        $manual = true;
        // 得到delivery的一些信息
        $sdf['delivery_id'] = $this->model->gen_id();
        $this->model->op_id = $controller->user->user_id;
        $this->model->op_name = $controller->user->user_data['name'];

        // 处理返货单据信息，得到订单的发送量。
        $order = $controller->app->model('orders');
        $odelivery = $controller->app->model('delivery');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $sdf_order = $order->dump($sdf['order_id'],'*',$subsdf);
        $order_items = array();

        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

        $goods_item_type = '';
        $obj_order_item = $this->app->model('order_items');
        $is_process = true;
        $is_start = true;
        foreach($sdf_order['order_objects'] as $k=>$v)
        {
            if ($v['obj_type'] != 'goods' && $v['obj_type'] != 'gift')
            {
                $arr_goods = array();
                $arr_service_goods_type_obj[$v['obj_type']]->get_order_object($v, $arr_goods);
                foreach ($arr_goods as $arr)
                {
                    $sdf['extend_send'][$arr['item_id']] = floatval($sdf['extend_send'][$arr['item_id']]);
                    if (preg_match('/^[1-9]\d*\.\d*|0\.\d*[1-9]\d*$/', $sdf['extend_send'][$arr['item_id']]) && !$arr['floatstore'])
                    {
                        $msg = app::get('b2c')->_('此类型的商品不允许发送浮点库存');
                        return false;
                    }
                }
                $arr_extends_keys = array_keys($sdf['extend_send']);
                foreach ($v['order_items'] as $arr_order_items)
                {
                    if ($arr_order_items['quantity'] == $arr_order_items['sendnum'])
                    {
                        $is_process = true && $is_process;
                        $is_start = false;
                    }
                    else
                    {
                        if (in_array($arr_order_items['item_id'], $arr_extends_keys) && floatval($sdf['extend_send'][$arr_order_items['item_id']]) > 0)
                        {
                            $is_process = true && $is_process;
                            $is_start = false;
                        }
                        else
                            $is_process = false;
                    }
                }
                if ($is_start)
                    $extends_goods_status = 'delivery_start';
                else
                {
                    if ($is_process)
                        $extends_goods_status = 'delivery_finish';
                    else
                        $extends_goods_status = 'delivery_process';
                }

                foreach( kernel::servicelist('b2c.order_store_extends') as $object ) {
                    if( $object->get_goods_type()!=$v['obj_type'] ) continue;
                    $obj_extends_store = $object;
                    if ($obj_extends_store)
                    {
                        $obj_extends_store->store_change($v, 'delivery', $extends_goods_status);
                    }
                }
            }

            foreach ($v['order_items'] as &$__arr_order_items)
            {
                $arr_goods = array();
                if ($__arr_order_items['item_type'] == 'product')
                    $goods_item_type = 'goods';
                else
                    $goods_item_type = $__arr_order_items['item_type'];
                if (!isset($__arr_order_items['products']) || !$__arr_order_items['products'])
                {
                    $arr_tmp = $obj_order_item->getList('*', array('item_id'=>$__arr_order_items['item_id']));
                    if ($arr_tmp)
                    {
                        $__arr_order_items['products']['product_id'] = $arr_tmp[0]['product_id'];
                        $__arr_order_items['products']['goods_id'] = $arr_tmp[0]['goods_id'];
                    }
                }
                $arr_service_goods_type_obj[$goods_item_type]->get_order_object($__arr_order_items['products'], $arr_goods);
                $__arr_order_items['floatstore'] = $arr_goods['type']['floatstore'];
                if ($sdf['send'] && array_key_exists($__arr_order_items['item_id'], $sdf['send']))
                {
                    $sdf['send'][$__arr_order_items['item_id']] = floatval($sdf['send'][$__arr_order_items['item_id']]);
                    if (preg_match('/^[1-9]\d*\.\d*|0\.\d*[1-9]\d*$/', $sdf['send'][$__arr_order_items['item_id']]) && !$__arr_order_items['floatstore'])
                    {
                        $msg = app::get('b2c')->_('此类型的商品不允许发送浮点库存');
                        return false;
                    }
                }
                else
                {
                    if ($sdf['gift_send'] && array_key_exists($__arr_order_items['item_id'], $sdf['gift_send']))
                    {
                        $sdf['gift_send'][$__arr_order_items['item_id']] = floatval($sdf['gift_send'][$__arr_order_items['item_id']]);
                        if (preg_match('/^[1-9]\d*\.\d*|0\.\d*[1-9]\d*$/', $sdf['gift_send'][$__arr_order_items['item_id']]) && !$__arr_order_items['floatstore'])
                        {
                            $msg = app::get('b2c')->_('此类型的商品不允许发送浮点库存');
                            return false;
                        }
                    }
                }
            }

            $order_items = array_merge($order_items,$v['order_items']);
        }

        $this->objMath = kernel::single('ectools_math');

        if (isset($sdf['send']) || isset($sdf['gift_send']) ||  isset($sdf['extend_send']))
        {
            if ($sdf['logi_id'])
            {
                $oCorp = &$controller->app->model('dlycorp');
                $aCorp = $oCorp->dump($sdf['logi_id'],'*');
            }

            $delivery = array(
                'money' => $this->objMath->number_plus(array($sdf['money'], $sdf['cost_protect'])),
                'is_protect' => $sdf['is_protect'],
                'delivery' => $sdf['delivery'],
                'delivery_id' => $sdf['delivery_id'],
                'logi_id' => $sdf['logi_id'],
                'logi_no' => $sdf['logi_no'],
                'logi_name' => $aCorp['name'],
                'ship_name' => $sdf['ship_name'],
                'ship_area' => $sdf['ship_area'],
                'ship_addr' => $sdf['ship_addr'],
                'ship_zip' => $sdf['ship_zip'],
                'ship_tel' => $sdf['ship_tel'],
                'ship_mobile' => $sdf['ship_mobile'],
                'ship_email' => $sdf['ship_email'],
                'memo' => $sdf['memo']
            );
        }
        else
        {
            $delivery = array(
                'money' => $this->objMath->number_plus(array($sdf_order['cost_freight'], $sdf_order['cost_protect'])),
                'is_protect' => $sdf_order['shipping']['is_protect'],
                'delivery' => $sdf_order['shipping']['method'],
                'delivery_id' => $odelivery->gen_id(),
                'logi_id' => '',
                'logi_no' => $sdf['logi_no'],
                'logi_name' => $sdf['logi_name'],
                'ship_name' => $sdf_order['consignee']['name'],
                'ship_area' => $sdf_order['consignee']['area'],
                'ship_addr' => $sdf_order['consignee']['addr'],
                'ship_zip' => $sdf_order['consignee']['zip'],
                'ship_tel' => $sdf_order['consignee']['telephone'],
                'ship_mobile' => $sdf_order['consignee']['mobile'],
                'ship_email' => $sdf_order['consignee']['email']
            );
        }

        /**
        *    @function:    订单明细赋值,读取订单详细表sdb_order_items的addon字段
        *    @params:
        *        @$dinfo['addon']:        订单序列化字段，存放订单物品等资料
        *        @$delivery['op_name']:    订单操作人员
        *        @$aUpdate['ship_status']:订单发货状态 1为发货状态
        */
        $delivery['order_id'] = $sdf['order_id'];
        $delivery['member_id'] = $sdf_order['member_id'];
        $delivery['t_begin'] = time();
        $delivery['op_name'] = $sdf['opname'];
        $delivery['type'] = 'delivery';
        $delivery['status'] = 'progress';
        $delivery_id = $delivery['delivery_id'];

        //遍历订单明细
        $aBill = array();
        $nonGoods = 0;    //是否完全发货商品标识
        if (!isset($sdf['send']) && !isset($sdf['gift_send']) && !isset($sdf['extend_send']) )
        {//非弹窗确认
           foreach($order_items as $key=>$dinfo)
           {
                if ($dinfo['item_type'] != 'gift')
                    $dinfo['send'] = $dinfo['quantity']-$dinfo['sendnum'];//须发送=未发送
                else
                    $dinfo['gift_send'] = $dinfo['quantity']-$dinfo['sendnum'];//须发送=未发送
                $order_items[$key] = $dinfo;
           }
        }
        else
        {//弹窗确认
			$is_all_send_none = true;
			if ($is_all_send_none && isset($sdf['send']) && $sdf['send']){
				foreach ((array)$sdf['send'] as $arr_send){
					if ($arr_send > 0){
						$is_all_send_none = false;
						break;
					}
				}
			}
			
			if ($is_all_send_none && isset($sdf['gift_send']) && $sdf['gift_send']){
				foreach ((array)$sdf['gift_send'] as $arr_send){
					if ($arr_send > 0){
						$is_all_send_none = false;
						break;
					}
				}
			}
			
			if ($is_all_send_none && isset($sdf['extend_send']) && $sdf['extend_send']){
				foreach ((array)$sdf['extend_send'] as $arr_send){
					if ($arr_send > 0){
						$is_all_send_none = false;
						break;
					}
				}
			}
			
			if ($is_all_send_none){
				$msg = app::get('b2c')->_('请输入发货数量！');
				return false;
			}
            foreach ($order_items as $key=>$dinfo)
            {
                if ($dinfo['item_type'] == 'product' || $dinfo['item_type'] == 'adjunct')
                {
                    if (isset($sdf['send'][$dinfo['item_id']]) && floatval($sdf['send'][$dinfo['item_id']]) > 0)
                    {//弹窗并且输入了发货数量>=1
                        if (floatval($sdf['send'][$dinfo['item_id']]) > $this->objMath->number_minus(array($dinfo['quantity'], $dinfo['sendnum'])))
                        {
                            $msg = app::get('b2c')->_("发货数量超过需要发货量！");
                            return false;
                        }
                        elseif (floatval($sdf['send'][$dinfo['item_id']]) == $this->objMath->number_minus(array($dinfo['quantity'], $dinfo['sendnum'])))
                        {//足量发送
                            $dinfo['send'] = $this->objMath->number_minus(array($dinfo['quantity'], $dinfo['sendnum']));//须发送=未发送
                        }
                        else
                        {//部分发送
                            $dinfo['send'] = $this->objMath->number_minus(array(floatval($sdf['send'][$dinfo['item_id']]),0));
                            $nonGoods = 1;
                        }
                    }
                    else
                    {
                        if (!is_null($sdf['send'][$dinfo['item_id']]) && $dinfo['sendnum'] != $dinfo['quantity'])
                        {
                            $dinfo['send'] = 0;
                            $nonGoods = 1;
                        }
                    }

                     if (isset($sdf['extend_send'][$dinfo['item_id']]) && floatval($sdf['extend_send'][$dinfo['item_id']]) > 0)
                    {
                        //弹窗并且输入了发货数量>=1
                        if (floatval($sdf['extend_send'][$dinfo['item_id']]) > $this->objMath->number_minus(array($dinfo['quantity'], $dinfo['sendnum'])))
                        {
                            $msg = app::get('b2c')->_("发货数量超过需要发货量！");
                            return false;
                        }
                        elseif (floatval($sdf['extend_send'][$dinfo['item_id']]) == $this->objMath->number_minus(array($dinfo['quantity'], $dinfo['sendnum'])))
                        {
                            //足量发送
                            $dinfo['extend_send'] = $this->objMath->number_minus(array($dinfo['quantity'], $dinfo['sendnum']));//须发送=未发送
                        }
                        else
                        {
                            //部分发送
                            $dinfo['extend_send'] = $this->objMath->number_minus(array(floatval($sdf['extend_send'][$dinfo['item_id']]),0));
                            $nonGoods = 1;
                        }
                    }
                    else
                    {
                        if (!is_null($sdf['extend_send'][$dinfo['item_id']]) && $dinfo['sendnum'] != $dinfo['quantity'])
                        {
                            $dinfo['extend_send'] = 0;
                            $nonGoods = 1;
                        }
                    }
                }
                else
                {
                    // 赠品发送过程...
                    if (isset($sdf['gift_send'][$dinfo['item_id']]) && floatval($sdf['gift_send'][$dinfo['item_id']]) > 0)
                    {
                        //弹窗并且输入了发货数量>=1
                        if (floatval($sdf['gift_send'][$dinfo['item_id']]) > $this->objMath->number_minus(array($dinfo['quantity'], $dinfo['sendnum'])))
                        {
                            $msg = app::get('b2c')->_("发货数量超过需要发货量！");
                            return false;
                        }
                        elseif (floatval($sdf['gift_send'][$dinfo['item_id']]) == $this->objMath->number_minus(array($dinfo['quantity'], $dinfo['sendnum'])))
                        {
                            //足量发送
                            $dinfo['gift_send'] = $this->objMath->number_minus(array($dinfo['quantity'], $dinfo['sendnum']));//须发送=未发送
                        }
                        else
                        {
                            //部分发送
                            $dinfo['gift_send'] = $this->objMath->number_minus(array(floatval($sdf['gift_send'][$dinfo['item_id']]),0));
                            $nonGoods = 1;
                        }
                    }
                    else
                    {
                        if (!is_null($sdf['gift_send'][$dinfo['item_id']]) && $dinfo['sendnum'] != $dinfo['quantity'])
                        {
                            $dinfo['gift_send'] = 0;
                            $nonGoods = 1;
                        }
                    }

                     // 其他发送过程
                    if (isset($sdf['extend_send'][$dinfo['item_id']]) && floatval($sdf['extend_send'][$dinfo['item_id']]) > 0)
                    {
                        //弹窗并且输入了发货数量>=1
                        if (floatval($sdf['extend_send'][$dinfo['item_id']]) > $this->objMath->number_minus(array($dinfo['quantity'], $dinfo['sendnum'])))
                        {
                            $msg = app::get('b2c')->_("发货数量超过需要发货量！");
                            return false;
                        }
                        elseif (floatval($sdf['extend_send'][$dinfo['item_id']]) == $this->objMath->number_minus(array($dinfo['quantity'], $dinfo['sendnum'])))
                        {
                            //足量发送
                            $dinfo['extend_send'] = $this->objMath->number_minus(array($dinfo['quantity'], $dinfo['sendnum']));//须发送=未发送
                        }
                        else
                        {
                            //部分发送
                            $dinfo['extend_send'] = $this->objMath->number_minus(array(floatval($sdf['extend_send'][$dinfo['item_id']]),0));
                            $nonGoods = 1;
                        }
                    }
                    else
                    {
                        if (!is_null($sdf['extend_send'][$dinfo['item_id']]) && $dinfo['sendnum'] != $dinfo['quantity'])
                        {
                            $dinfo['extend_send'] = 0;
                            $nonGoods = 1;
                        }
                    }
                }
                $order_items[$key] = $dinfo;
            }
        }

        if ($order_items)
        {
            //实体商品
            $arr_items = array();
            if ($manual || (!$manual && $this->app->getConf('system.auto_delivery_physical') != 'no'))
            {
                if (!$manual)
                {
                    $delivery['status'] = ($this->app->getConf('system.auto_delivery_physical')=='yes' ? 'progress' : 'ready');
                }
                $iLoop = 0;
                foreach ($order_items as $dinfo)
                {
                    if($dinfo['send'])
                    {
                        $item = array(
                                'order_item_id' => $dinfo['item_id'],
                                'order_id' => $sdf['order_id'],
                                'delivery_id' => $delivery['delivery_id'],
                                'item_type' => ($dinfo['item_type'] == 'product') ? 'goods' : $dinfo['item_type'],
                                'goods_id' => $dinfo['goods_id'],
                                'product_id' => $dinfo['products']['product_id'],
                                'product_bn' => $dinfo['bn'],
                                'product_name' => $dinfo['name'].$dinfo['addon']['adjname'],
                                'number' => $dinfo['send'],
                            );

                        $items[] = $dinfo;
                        $arr_items[] = array(
                            'number' => $dinfo['send'],
                            'name' => $dinfo['name'],
                        );
                        $this->toInsertItem($item);
                        $iLoop++;
                    }

                    if ($dinfo['gift_send'])
                    {
                        $item = array(
                                'order_item_id' => $dinfo['item_id'],
                                'order_id' => $sdf['order_id'],
                                'delivery_id' => $delivery['delivery_id'],
                                'item_type' => ($dinfo['item_type'] == 'product') ? 'goods' : $dinfo['item_type'],
                                'goods_id' => $dinfo['goods_id'],
                                'product_id' => $dinfo['products']['product_id'],
                                'product_bn' => $dinfo['bn'],
                                'product_name' => $dinfo['name'],
                                'number' => $dinfo['gift_send'],
                            );

                        $items[] = $dinfo;
                        $arr_items[] = array(
                            'number' => $dinfo['gift_send'],
                            'name' => $dinfo['name'],
                        );
                        $this->toInsertItem($item);
                        $iLoop++;
                    }

                    if ($dinfo['extend_send'])
                    {
                        $item = array(
                                'order_item_id' => $dinfo['item_id'],
                                'order_id' => $sdf['order_id'],
                                'delivery_id' => $delivery['delivery_id'],
                                'item_type' => ($dinfo['item_type'] == 'product') ? 'goods' : $dinfo['item_type'],
                                'goods_id' => $dinfo['goods_id'],
                                'product_id' => $dinfo['products']['product_id'],
                                'product_bn' => $dinfo['bn'],
                                'product_name' => $dinfo['name'],
                                'number' => $dinfo['extend_send'],
                            );

                        $items[] = $dinfo;
                        $arr_items[] = array(
                            'number' => $dinfo['extend_send'],
                            'name' => $dinfo['name'].$dinfo['addon']['adjname'],
                        );
                        $this->toInsertItem($item);
                        $iLoop++;
                    }
                }
            }

            if($iLoop > 0)
            {
                $delivery_id = $delivery['delivery_id'];
                $is_save = $odelivery->insert($delivery);
                if (!$is_save)
                {
                    $msg = app::get('b2c')->_('发货单生成失败！');
                    return false;
                }

                $arr_delivery['status'] = 'succ';
                $delivery['status'] = 'succ';
                $delivery['delivery_id'] = $delivery_id;
                $is_save = $odelivery->update($arr_delivery,array('delivery_id'=>$delivery_id));
                if (!$is_save)
                {
                    $msg = app::get('b2c')->_('发货单修改失败！');
                    return false;
                }
            }

            $order_delivery = $controller->app->model('order_delivery');
            $order_delivery_data = array('order_id'=>$delivery['order_id'],'dly_id'=>$delivery_id,'dlytype'=>'delivery','items'=>($items));
            $order_delivery->save($order_delivery_data);
        }

        //没有完全发货
        if ($nonGoods)
            $aUpdate['ship_status'] = '2';
        else
            $aUpdate['ship_status'] = '1';

        $aUpdate['order_id'] = $sdf['order_id'];
        $aUpdate['ship_status'] = $aUpdate['ship_status'];

		$tmp_order = $order->getList('ship_status', array('order_id'=>$sdf['order_id']));
		if ($aUpdate['ship_status'] == '1' && $tmp_order[0]['ship_status'] != '3' && $tmp_order[0]['ship_status'] != '4')
		{
			$order->addSellLog($sdf);
		}
        $order->save($aUpdate);

        $aUpdate['total_amount'] = $sdf_order['total_amount'];
        $aUpdate['is_tax'] = $sdf_order['is_tax'];
        $aUpdate['member_id'] = $sdf_order['member_id'];
        $aUpdate['delivery'] = $delivery;
        $aUpdate['ship_billno'] = $delivery['logi_no'];
        $aUpdate['ship_corp'] = $delivery['logi_name'];
        // 配送方式名称
        $obj_dlytype = $this->app->model('dlytype');
        $arr_dlytype = $obj_dlytype->dump($delivery['delivery']['delivery'], 'dt_name');
        $aUpdate['delivery']['delivery'] = $arr_dlytype['dt_name'];
        if ($sdf_order['member_id'])
        {
            $member = $this->app->model('members');
            $arr_member = $member->dump($sdf_order['member_id'], '*', array(':account@pam'=>'*'));
        }
        $aUpdate['email'] = (!$sdf_order['member_id']) ? $sdf_order['consignee']['email'] : $arr_member['contact']['email'];

        app::get('b2c')->model('orders')->fireEvent('shipping', $aUpdate, $sdf_order['member_id']);

        $is_change_point = true;
        // 订单积分结算埋点
        $policy_stage = $this->app->getConf("site.consume_point.stage");
        if ($sdf_order['pay_status'] == '1' && $aUpdate['ship_status'] == '1' && $policy_stage == '2')
            $stage = '1';
        else
            $stage = '0';

        // 扣除积分，使用积分
        $obj_reducte_point = kernel::service('b2c_member_point_reducte');
        if ($stage)
            $is_change_point = $obj_reducte_point->change_point($sdf_order['member_id'], 0 - intval($sdf_order['score_u']), $msg, 'order_pay_use', 1, $stage, $sdf['order_id'], $controller->user->user_id, 'delivery');

        if (!$is_change_point)
        {
            $msg = app::get('b2c')->_('积分扣除超过会员已有积分');
            return false;
        }
        // 订单积分结算埋点
        $policy_stage = $this->app->getConf("site.get_policy.stage");
        if ($sdf_order['pay_status'] == '1' && $aUpdate['ship_status'] == '1' && $policy_stage == '2')
            $stage = '1';
        else
            $stage = '0';

        // 获得积分
        $obj_add_point = kernel::service('b2c_member_point_add');
        if ($stage)
            $obj_add_point->change_point($sdf_order['member_id'], intval($sdf_order['score_g']), $msg, 'order_pay_get', 2, $stage, $sdf['order_id'], $controller->user->user_id, 'delivery');

        //取得发货的具体信息，
        $message_part1 = "";
        $message = "";

        $ship_status = $aUpdate['ship_status'];

        if ($ship_status == '1')
        {   //全部发货
            $message_part1 = app::get('b2c')->_("发货完成");
        }
        else if ($ship_status == '2')
        {    //部分发货
            $message_part1 = app::get('b2c')->_("已发货");
        }

        $message = "".app::get('b2c')->_('订单')."<!--order_id=".$sdf['order_id']."&delivery_id=".$delivery['delivery_id']."&ship_status=".$ship_status."-->".$message_part1;

        $log_text = "";
        if ($ship_status == '1') {
			$log_text[] = array(
				'txt_key'=>stripslashes('订单<a href="javascript:void(0)" onclick=\'show_delivery_item(this,"%s",%s)\' title="点击查看详细" style="color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;">全部商品</a>发货完成'),
				'data'=>array(
					0=>$delivery['delivery_id'],
					1=>htmlentities(json_encode($arr_items), ENT_QUOTES),
				),
			);
			if ($aCorp) {
				$log_text[] = array(
					'txt_key'=>'，物流公司：<a class="lnk" target="_blank" title="%s" href="%s">%s</a>（可点击进入物流公司网站跟踪配送）',
					'data'=>array(
						0=>$aCorp['name'],
						1=>$aCorp['request_url'],
						2=>$aCorp['name'],
					),
				);
			}
			if ($delivery['logi_no']) {
				$log_text[] = array(
					'txt_key'=>"物流单号：%s",
					'data'=>array(
						0=>$delivery['logi_no']
					),
				);
				$log_addon['logi_no'] = $delivery['logi_no'];
				$log_addon = serialize($log_addon);
			}
            /*$log_text = "".app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$delivery['delivery_id']."&quot;," . htmlentities(json_encode($arr_items), ENT_QUOTES) . ")
\" href=\"javascript:void(0)\">".app::get('b2c')->_('全部商品')."</a>".app::get('b2c')->_('发货完成')."，" . (($aCorp) ? "".app::get('b2c')->_('物流公司：')."<a href=\"" . $aCorp['request_url'] . "\" title=\"" . $aCorp['name'] . "\" target=\"_blank\" class=\"lnk\">" . $aCorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . (($delivery['logi_no']) ? "".app::get('b2c')->_('物流单号：')."" . $delivery['logi_no'] : "");*/
        }

        if ($ship_status == '2') {
			$log_text['items'] = array(
				'txt_key'=>stripslashes('订单<a href="javascript:void(0)" onclick=\'show_delivery_item(this,"%s",%s)\' title="点击查看详细" style="color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;">部分商品</a>已发货'),
				'data'=>array(
					0=>$delivery['delivery_id'],
					1=>htmlentities(json_encode($arr_items), ENT_QUOTES),
				),
			);
			if ($aCorp) {
				$log_text['dlycorp'] = array(
					'txt_key'=>'，物流公司：<a class="lnk" target="_blank" title="%s" href="%s">%s</a>（可点击进入物流公司网站跟踪配送）',
					'data'=>array(
						0=>$aCorp['name'],
						1=>$aCorp['request_url'],
						2=>$aCorp['name'],
					),
				);
			}
			if ($delivery['logi_no']) {
				$log_text['logi_no'] = array(
					'txt_key'=>"物流单号：%s",
					'data'=>array(
						0=>$delivery['logi_no'],
					),
				);
				$log_addon['logi_no'] = $delivery['logi_no'];
				$log_addon = serialize($log_addon);
			}
            /*$log_text = "".app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$delivery['delivery_id']."&quot;," . htmlentities(json_encode($arr_items), ENT_QUOTES) . ")
\" href=\"javascript:void(0)\">".app::get('b2c')->_('部分商品')."</a>".app::get('b2c')->_('已发货')."，" . (($aCorp) ? "".app::get('b2c')->_('物流公司：')."<a href=\"" . $aCorp['request_url'] . "\" title=\"" . $aCorp['name'] . "\" target=\"_blank\" class=\"lnk\">" . $aCorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . (($delivery['logi_no']) ? "".app::get('b2c')->_('物流单号：')."" . $delivery['logi_no'] : "");*/
        }
		$log_text = serialize($log_text);

        // 更新发货日志结果
        $objorder_log = $this->app->model('order_log');
        $sdf_order_log = array(
            'rel_id' => $sdf['order_id'],
            'op_id' => $sdf['opid'],
            'op_name' => $sdf['opname'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'delivery',
            'result' => 'SUCCESS',
            'log_text' => $log_text,
			'addon' => $log_addon,
        );
        $log_id = $objorder_log->save($sdf_order_log);

        // 发货单结束后的处理任务
        $obj_after_delivery_service = kernel::servicelist('b2c.order.delivery.after');
        if ($obj_after_delivery_service)
        {
            $delivery['delivery_items']= $items;
            $delivery['orders']= $order_delivery_data;
            foreach ($obj_after_delivery_service as $obj)
            {
				if (method_exists($obj,'generate'))
					$obj->generate($delivery);
            }
        }

        return true;
    }

    /**
     * 修改各个item的相关信息
     * @params array 修改的data
     * @return boolean 成功与否的
     */
    private function toInsertItem(&$data)
    {
        // 三个模型实体对象
        $order_item = $this->app->model('order_items');
        $o = $this->app->model('delivery_items');
        $arr_data = $data;

        if ($o->save($arr_data))
        {
            //更新发货量
            $is_update_store = false;
            $tmp = $order_item->dump($data['order_item_id'],'*');
            $update_data['sendnum'] = $this->objMath->number_plus(array($tmp['sendnum'], $data['number']));

            if ($tmp['quantity'] < $update_data['sendnum'])
                $is_update_store = false;
            else
                $is_update_store = true;

            $update_data['item_id'] = $tmp['item_id'];

            if ($is_update_store && $order_item->save($update_data))
            {
                return $this->minus_stock($data);
            }
            else
            {
                return false;
            }

        }
        return false;
    }
}
