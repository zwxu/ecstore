<?php



class b2c_order_reship extends b2c_order_operation
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
     * @return object b2c_order_reship的对象
     */
    public static function getInstance($app, $model)
    {
        if (is_object(self::$instance))
        {
            return self::$instance;
        }

        self::$instance = new b2c_order_reship($app, $model);

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
     * 创建退货单
     * @params array - 订单数据
     * @params obj - 应用对象
     * @params string - 支付单生成的记录
     * @return boolean - 创建成功与否
     */
    public function generate($sdf, &$controller=null, &$msg='')
    {
        $order = $controller->app->model('orders');
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
        $nonGoods_extends = 3;
        $is_process = true;
        $is_start = true;
        foreach ($sdf_order['order_objects'] as $k=>$v)
        {
            if ($v['obj_type'] != 'goods' && $v['obj_type'] != 'gift')
            {
                $arr_goods = array();
                $arr_service_goods_type_obj[$v['obj_type']]->get_order_object($v, $arr_goods);
                $sdf['send'][$arr['item_id']] = floatval($sdf['send'][$arr['item_id']]);
                foreach ($arr_goods as $arr)
                {
                    if (preg_match('/^[1-9]\d*\.\d*|0\.\d*[1-9]\d*$/', $sdf['send'][$arr['item_id']]) && !$arr['floatstore'])
                    {
                        $msg = app::get('b2c')->_('此类型的商品不允许发送浮点库存');
                        return false;
                    }
                }
                $arr_extends_keys = array_keys($sdf['send']);
                foreach ($v['order_items'] as $arr_order_items)
                {
                    if ($arr_order_items['quantity'] > $arr_order_items['sendnum'])
                    {
                        $is_process = true && $is_process;
                        $is_start = false;
                    }
                    else
                    {
                        if (in_array($arr_order_items['item_id'], $arr_extends_keys) && floatval($sdf['send'][$arr_order_items['item_id']]) > 0)
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
                        $obj_extends_store->store_change($v, 'reship', $extends_goods_status);
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
                $sdf['send'][$__arr_order_items['item_id']] = floatval($sdf['send'][$__arr_order_items['item_id']]);
                if (preg_match('/^[1-9]\d*\.\d*|0\.\d*[1-9]\d*$/', $sdf['send'][$__arr_order_items['item_id']]) && !$__arr_order_items['floatstore'])
                {
                    $msg = app::get('b2c')->_('此类型的商品不允许发送浮点库存');
                    return false;
                }
            }
            $order_items = array_merge($order_items,$v['order_items']);
        }

        $oreship = $controller->app->model('reship');
        if (isset($sdf['send']))
        {
            if($sdf['logi_id'])
            {
                $oCorp = &$controller->app->model('dlycorp');
                $aCorp = $oCorp->dump($sdf['logi_id'],'*');
            }

            $reship = array(
                'money' => floatval($sdf['money']) + floatval($sdf['cost_protect']),
                'is_protect' => $sdf['is_protect'],
                'delivery' => $sdf['delivery'],
                'reship_id' => $sdf['reship_id'],
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
            $reship = array(
                'money' => $sdf_order['cost_freight']+$sdf_order['cost_protect'],
                'is_protect' => $sdf['is_protect'],
                'delivery' => $sdf['delivery'],
                'reship_id' => $oreship->gen_id(),
                'logi_id' => '',
                'logi_no' => $sdf['logi_no'],
                'logi_name' => $sdf['logi_name'],
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

        $reship['order_id'] = $sdf['order_id'];
        $reship['member_id'] = $sdf_order['member_id'];
        $reship['t_begin'] = time();
        $reship['op_name'] = $sdf['opname'];
        $v['type'] = 'reship';
        $reship['status'] = 'progress';
        $reship_id = $reship['reship_id'];

        //遍历订单明细
        $aBill = array();
		$nonGoods = 3;
        if (!isset($sdf['send']))
        {//非弹窗确认
           foreach ($order_items as $key=>$dinfo)
           {
                $dinfo['send'] = $dinfo['sendnum'];//须退货=已发送
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
			
			if ($is_all_send_none){
				$msg = app::get('b2c')->_('请输入退货数量！');
				return false;
			}
			
            foreach ($order_items as $key=>$dinfo)
            {
                if (isset($sdf['send'][$dinfo['item_id']]) && floatval($sdf['send'][$dinfo['item_id']]) > 0)
                {//弹窗并且输入了发货数量>=1
                    if (floatval($sdf['send'][$dinfo['item_id']]) > $dinfo['sendnum'])
                    {
                        $msg = app::get('b2c')->_("超过实际需要的退货量！");
                        return false;
                    }
                    elseif (floatval($sdf['send'][$dinfo['item_id']]) == $dinfo['sendnum'])
                    {//足量退货
                        $dinfo['send'] = $this->objMath->number_minus(array($dinfo['sendnum'],0));////须退货=已发送
                    }
                    else
                    {//部分退货
                        $dinfo['send'] = $this->objMath->number_minus(array($sdf['send'][$dinfo['item_id']],0));
                        $nonGoods = 4;
                    }
                }
                else
                {				
                    if (0 < $dinfo['sendnum'] && $dinfo['sendnum'] <= $dinfo['quantity'] && $nonGoods != 4)
                        $nonGoods = 4;
                }
                $order_items[$key] = $dinfo;
            }
        }

        if ($order_items)
        {
            //实体商品
            $iLoop = 0;
            $arr_items = array();
            foreach ($order_items as $dinfo)
            {
                $item = array(
                            'order_item_id' => $dinfo['item_id'],
                            'order_id' => $sdf['order_id'],
                            'reship_id' => $reship['reship_id'],
                            'item_type' => ($dinfo['item_type']=='product') ? 'goods' : $dinfo['item_type'],
                            'product_id' => $dinfo['products']['product_id'],
                            'product_bn' => $dinfo['bn'],
                            'product_name' => $dinfo['name'].$dinfo['addon']['adjname'],
                            'number' => $dinfo['send'] );

                if ($dinfo['send'])
                {
                    $items[] = $dinfo;
                    $arr_items[] = array(
                        'number' => $dinfo['send'],
                        'name' => $dinfo['name'].$dinfo['addon']['adjname'],
                    );
                    $this->toInsertItem($item);
                    $iLoop++;
                }
            }

            if ($iLoop > 0)
            {
                $is_save = $oreship->insert($reship);
                if (!$is_save)
                {
                    $msg = app::get('b2c')->_('退货单生成失败！');
                    return false;
                }

                $up_reship['status'] = 'succ';
                $reship['status'] = 'succ';
                $reship['reship_id'] = $reship_id;
                $is_save = $oreship->update($up_reship,array('reship_id'=>$reship_id));

                if (!$is_save)
                {
                    $msg = app::get('b2c')->_('退货单修改失败！');
                    return false;
                }
            }
        }


        //没有完全退货
        if ($nonGoods != 4 && $nonGoods_extends != 4)
        {
            $aUpdate['ship_status'] = 4;
        }
        else
        {
            $aUpdate['ship_status'] = 3;
        }

        $aUpdate['order_id'] = $sdf['order_id'];
        $aUpdate['ship_status'] = $aUpdate['ship_status'];

        $order->save($aUpdate);

        $order_reship = $controller->app->model('order_delivery');
        $order_reship_data = array('order_id'=>$reship['order_id'],'dly_id'=>$reship_id,'dlytype'=>'reship','items'=>($items));
        $order_reship->save($order_reship_data);

        $aUpdate['total_amount'] = $sdf_order['total_amount'];
        $aUpdate['is_tax'] = $sdf_order['is_tax'];
        $aUpdate['member_id'] = $sdf_order['member_id'];
        $aUpdate['delivery'] = $reship;
        $aUpdate['ship_billno'] = $reship['logi_no'];
        // 取得物流公司的名称
        $obj_dlycorp = $this->app->model('dlycorp');
        $arr_dlycorp = $obj_dlycorp->dump($reship['logi_id'], 'name');
        $aUpdate['ship_corp'] = $arr_dlycorp['name'];
        if ($sdf_order['member_id'])
        {
            $member = $this->app->model('members');
            $arr_member = $member->dump($sdf_order['member_id'], '*', array(':account@pam'=>'*'));
        }
        $aUpdate['email'] = (!$sdf_order['member_id']) ? $sdf_order['consignee']['email'] : $arr_member['contact']['email'];

        //取得发货的具体信息，
        $message_part1 = "";
        $message = "";

        $ship_status = $aUpdate['ship_status'];

        if ($ship_status == '4')
        {   //全部发货
            $message_part1 = app::get('b2c')->_("完全退货");
        }
        else if ($ship_status == '3')
        {    //部分发货
            $message_part1 = app::get('b2c')->_("部分退货");
        }

        $message = "".app::get('b2c')->_('订单')."<!--order_id=".$sdf['order_id']."&reship_id=".$reship['reship_id']."&ship_status=".$ship_status."-->".$message_part1;

        // 更新退款日志结果
        $log_text = "";
        if ($ship_status == '4') {
			$log_text[] = array(
				'txt_key'=>stripslashes('订单<a href="javascript:void(0)" onclick=\'show_delivery_item(this,"%s",%s)\' title="点击查看详细" style="color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;">全部商品</a>退货完成'),
				'data'=>array(
					0=>$reship['reship_id'],
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
			if ($reship['logi_no']) {
				$log_text[] = array(
					'txt_key'=>"物流单号：%s",
					'data'=>array(
						0=>$reship['logi_no'],
					),
				);
				$log_addon['logi_no'] = $reship['logi_no'];
				$log_addon = serialize($log_addon);
			}

            /*$log_text = "".app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$reship['reship_id']."&quot;," . htmlentities(json_encode($arr_items), ENT_QUOTES) . ")
\" href=\"javascript:void(0)\">".app::get('b2c')->_('全部商品')."</a>".app::get('b2c')->_('退货完成')."";*/
        }

        if ($ship_status == '3') {
			$log_text[] = array(
				'txt_key'=>stripslashes('订单<a href="javascript:void(0)" onclick=\'show_delivery_item(this,"%s",%s)\' title="点击查看详细" style="color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;">部分商品</a>已退货'),
				'data'=>array(
					0=>$reship['reship_id'],
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
			if ($reship['logi_no']) {
				$log_text[] = array(
					'txt_key'=>"物流单号：%s",
					'data'=>array(
						0=>$reship['logi_no'],
					),
				);
				$log_addon['logi_no'] = $reship['logi_no'];
				$log_addon = serialize($log_addon);
			}
            /*$log_text = "".app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$reship['reship_id']."&quot;," . htmlentities(json_encode($arr_items), ENT_QUOTES) . ")
\" href=\"javascript:void(0)\">".app::get('b2c')->_('部分商品')."</a>".app::get('b2c')->_('已退货')."";*/
        }
		$log_text = serialize($log_text);
        $objorder_log = $this->app->model('order_log');
        $sdf_order_log = array(
            'rel_id' => $sdf['order_id'],
            'op_id' => $sdf['op_id'],
            'op_name' => $sdf['opname'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'reship',
            'result' => 'SUCCESS',
            'log_text' => $log_text,
			'addon' => $log_addon,
        );
        $log_id = $objorder_log->save($sdf_order_log);

        // 退货单结束后的处理任务
        $obj_after_reship_service = kernel::servicelist('b2c.order.reship.after');
        $is_after_reship_task = true;
        if ($obj_after_reship_service)
        {
            $reship['reship_items']= $items;
            $reship['orders']= $order_reship_data;
            foreach ($obj_after_reship_service as $obj)
            {
                $is_after_reship_task = $obj->generate($reship, $msg);
            }
        }
        return $is_after_reship_task;
    }

    /**
     * 更新订单各个items
     * @params array 标准数据数组
     * @return boolean 更新是否成功
     */
    private function toInsertItem(&$data)
    {
        $order_item = $this->app->model('order_items');
        $o = $this->app->model('reship_items');
        $objMath = kernel::single('ectools_math');

        if ($o->save($data))
        {
            //更新发货量
            $tmp = $order_item->dump($data['order_item_id'],'*');
            $update_data['sendnum'] = $objMath->number_minus(array($tmp['sendnum'], $data['number']));
            $update_data['item_id'] = $tmp['item_id'];

            if ($order_item->save($update_data))
            {
               //return $this->restore_stock($data);
               return true;
            }
            else
            {
                return false;
            }
        }

        return false;
    }
}
